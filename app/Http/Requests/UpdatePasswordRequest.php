<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Informe sua senha atual.',
            'current_password.current_password' => 'A senha atual não confere.',
            'password.required' => 'Informe a nova senha.',
            'password.min' => 'A nova senha precisa ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.different' => 'Escolha uma senha diferente da atual.',
        ];
    }
}
