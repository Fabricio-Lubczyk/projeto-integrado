<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateGameRequest extends StoreGameRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['title'] = ['required', 'string', 'max:150', Rule::unique('games', 'title')->ignore($this->route('game'))];

        return $rules;
    }
}
