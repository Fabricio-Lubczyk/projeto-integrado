<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['title', 'developer', 'description'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150', 'unique:games,title'],
            'developer' => ['required', 'string', 'max:120'],
            'released_on' => ['nullable', 'date_format:Y-m-d'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'título', 'developer' => 'desenvolvedora', 'released_on' => 'data de lançamento', 'description' => 'descrição'];
    }

    public function messages(): array
    {
        return [
            'required' => 'Preencha o campo :attribute.',
            'string' => 'Informe um texto válido para :attribute.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'title.unique' => 'Este jogo já está cadastrado.',
            'released_on.date_format' => 'Informe uma data de lançamento válida.',
        ];
    }
}
