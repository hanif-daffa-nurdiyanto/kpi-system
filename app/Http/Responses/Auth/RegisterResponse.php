<?php

namespace App\Http\Responses\Auth;

use Filament\Pages\Auth\Login;
use Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\RegistrationResponse;

class RegisterResponse extends RegistrationResponse
{
  public function toResponse($request): RedirectResponse|Redirector
  {
    Auth::Logout();
    // Here, you can define which resource and which page you want to redirect to
    return redirect()->route('filament.app.auth.login');
  }
}