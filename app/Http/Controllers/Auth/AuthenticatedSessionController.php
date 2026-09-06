<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->homeRoute($request->user()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function homeRoute(?User $user): string
    {
        if ($user?->hasRole(Role::SuperAdmin)) {
            return route('super-admin.ketua.index', absolute: false);
        }

        if ($user?->hasRole(Role::Santri) || $user?->hasRole(Role::OrangTua)) {
            return route('portal.home', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
