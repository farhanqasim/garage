<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get current user's theme settings (for JSON/API).
     */
    public function index()
    {
        $user = Auth::user();
        $settings = $user->theme_settings ?? [];
        $defaults = [
            'theme' => 'light',
            'sidebar' => 'light',
            'color' => 'primary',
            'layout' => 'default',
            'topbar' => 'white',
            'width' => 'fluid',
            'rtl' => false,
            'sidebar_bg' => null,
            'topbar_bg' => null,
        ];
        return response()->json(array_merge($defaults, $settings));
    }

    /**
     * Save theme settings to database.
     * Reset: when reset=true, usi user ki saari theme settings DB mein default/null save ho jati hain.
     * Normal update: bheje gaye keys (null bhi) save hoti hain taake sidebar_bg/topbar_bg null ho saken.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $valid = [
            'theme', 'sidebar', 'color', 'layout', 'topbar', 'width',
            'rtl', 'sidebar_bg', 'topbar_bg',
        ];
        $defaults = [
            'theme' => 'light',
            'sidebar' => 'light',
            'color' => 'primary',
            'layout' => 'default',
            'topbar' => 'white',
            'width' => 'fluid',
            'rtl' => false,
            'sidebar_bg' => null,
            'topbar_bg' => null,
        ];

        if ($request->boolean('reset')) {
            $user->theme_settings = $defaults;
            $user->save();
            return response()->json(['success' => true, 'theme_settings' => $user->theme_settings]);
        }

        $input = $request->only($valid);
        if (array_key_exists('rtl', $input)) {
            $input['rtl'] = filter_var($input['rtl'], FILTER_VALIDATE_BOOLEAN);
        }
        $current = $user->theme_settings ?? [];
        // Jo keys request mein bheji gayi hain (null bhi) unhi se update; baaki current se
        $merged = $current;
        foreach ($valid as $key) {
            if (array_key_exists($key, $input)) {
                $merged[$key] = $input[$key];
            }
        }
        $user->theme_settings = $merged;
        $user->save();
        return response()->json(['success' => true, 'theme_settings' => $user->theme_settings]);
    }
}
