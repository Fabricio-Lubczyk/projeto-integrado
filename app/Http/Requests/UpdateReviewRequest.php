<?php

namespace App\Http\Requests;

class UpdateReviewRequest extends StoreReviewRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->route('review')->user_id === $this->user()->id;
    }
}
