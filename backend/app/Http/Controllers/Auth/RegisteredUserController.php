<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:32'],
            'company_email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'sms_sender' => ['nullable', 'string', 'max:11'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $salon = Salon::create([
                'name' => $request->string('company_name')->toString(),
                'address' => $request->string('company_address')->toString() ?: null,
                'phone' => $request->string('company_phone')->toString() ?: null,
                'email' => $request->string('company_email')->toString() ?: $request->string('email')->toString(),
                'default_visit_length_min' => 60,
                'sms_sender' => $request->string('sms_sender')->toString() ?: null,
                'sms_reminder_hours' => 24,
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
            ]);

            return User::create([
                'salon_id' => $salon->id,
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => Hash::make($request->string('password')->toString()),
                'role' => 'admin',
            ]);
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->put('salon_id', $user->salon_id);

        return redirect(route('dashboard', absolute: false));
    }
}
