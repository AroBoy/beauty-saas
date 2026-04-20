<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalonUserRequest;
use App\Http\Requests\UpdateSalonUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalonUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->where('salon_id', $request->user()->salon_id)
            ->orderByRaw("case when role in ('admin', 'owner') then 0 else 1 end")
            ->orderBy('name')
            ->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'userModel' => new User(['role' => 'operator']),
            'isEdit' => false,
        ]);
    }

    public function store(StoreSalonUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::query()->create([
            'salon_id' => $request->user()->salon_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'operator',
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'user-created');
    }

    public function edit(Request $request, User $user): View
    {
        $user = $this->resolveOperator($request, $user);

        return view('users.form', [
            'userModel' => $user,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateSalonUserRequest $request, User $user): RedirectResponse
    {
        $user = $this->resolveOperator($request, $user);
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()
            ->route('users.index')
            ->with('status', 'user-updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $user = $this->resolveOperator($request, $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'user-deleted');
    }

    private function resolveOperator(Request $request, User $user): User
    {
        abort_unless($user->salon_id === $request->user()->salon_id, 404);
        abort_unless($user->isOperator(), 404);

        return $user;
    }
}
