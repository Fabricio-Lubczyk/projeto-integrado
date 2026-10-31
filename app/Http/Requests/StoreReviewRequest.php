<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Selecione uma nota de 1 a 5.',
            'rating.integer' => 'A nota deve ser um número inteiro.',
            'rating.between' => 'A nota deve estar entre 1 e 5.',
            'comment.required' => 'Escreva um comentário para avaliar o jogo.',
            'comment.string' => 'O comentário informado é inválido.',
            'comment.max' => 'O comentário deve ter no máximo 2.000 caracteres.',
        ];
    }
}
