<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Informe seu e-mail.',
            'email.string' => 'Informe um e-mail válido.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'Use até 255 caracteres no e-mail.',
            'password.required' => 'Informe sua senha.',
            'password.string' => 'Informe uma senha válida.',
            'remember.boolean' => 'Verifique a opção de manter a conta conectada.',
        ];
    }

    public function authenticate(): void
    {
        $key = Str::transliterate($this->input('email').'|'.$this->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Muitas tentativas. Aguarde {$seconds} segundos e tente novamente.",
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        RateLimiter::clear($key);
    }
}
