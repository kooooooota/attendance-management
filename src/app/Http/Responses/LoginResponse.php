<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if (auth()->user()->is_admin) {
            return redirect()->route('admins.attendances.index');
        }

        return redirect()->intended(url('/login'));
    }
}