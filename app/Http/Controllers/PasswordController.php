<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('account.password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => $request->validated('password'),
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        return to_route('account.show')->with('status', 'Senha alterada. Use a nova senha no próximo acesso.');
    }
}
