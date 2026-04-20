<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppSettingsUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class AppSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'user' => $request->user(),
            'salon' => $request->user()->salon,
        ]);
    }

    public function update(AppSettingsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->fill([
            'theme_mode' => $validated['theme_mode'],
        ])->save();

        if ($user->salon) {
            $user->salon->fill([
                'name' => $validated['company_name'] ?? null,
                'address' => $validated['company_address'] ?? null,
                'phone' => $validated['company_phone'] ?? null,
                'email' => $validated['company_email'] ?? null,
                'opening_time' => $validated['opening_time'],
                'closing_time' => $validated['closing_time'],
                'saturday_opening_time' => $validated['saturday_opening_time'] ?? null,
                'saturday_closing_time' => $validated['saturday_closing_time'] ?? null,
                'sunday_opening_time' => $validated['sunday_opening_time'] ?? null,
                'sunday_closing_time' => $validated['sunday_closing_time'] ?? null,
                'saturday_closed' => (bool) ($validated['saturday_closed'] ?? false),
                'sunday_closed' => (bool) ($validated['sunday_closed'] ?? false),
            ])->save();
        }

        return Redirect::route('settings.edit')->with('status', 'settings-updated');
    }
}
