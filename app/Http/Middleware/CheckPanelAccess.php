<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        $user = Auth::user();
        $currentPanel = Filament::getCurrentPanel();

        if (!$user) {
            return $next($request);
        }

        // Cek panel admin
        if ($currentPanel && $currentPanel->getId() === 'admin') {
            if (!$user->hasRole('super_admin')) {
                Auth::logout();
                session()->flash('error', 'You do not have access to the Admin Panel. Please login through the App Panel.');

                return redirect()->route('filament.app.auth.login')
                    ->with('refresh', true);
                // ->with('error', 'You do not have access to the Admin Panel. Please login through the App Panel.');
            }
        }

        // Cek panel app
        if ($currentPanel && $currentPanel->getId() === 'app') {
            if (!$user->hasAnyRole(['employee', 'manager'])) {
                Auth::logout();
                session()->flash('error', 'You do not have access to the App Panel. Please login through the Admin Panel.');

                return redirect()->route('filament.admin.auth.login')
                    ->with('refresh', true);
                // ->with('error', 'You do not have access to the App Panel. Please login through the Admin Panel.');
            }
        }

        return $next($request);
    }
}
