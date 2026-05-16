<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id'     => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:trade,sell,both',
            'status'      => 'required|in:active,paused,sold,traded',
            'description' => 'nullable|string|max:2000',
            'category'    => 'nullable|string|max:100',
            'condition'   => 'nullable|in:new,like_new,good,fair,poor',
            'price'       => 'nullable|numeric|min:0',
            'currency'    => 'nullable|string|max:10',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ];
    }
}
