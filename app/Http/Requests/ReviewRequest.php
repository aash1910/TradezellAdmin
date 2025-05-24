<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => [
                'required',
                'exists:orders,id',
                function($attribute, $value, $fail) {
                    $reviewCount = \App\Models\Review::where('order_id', $value)->count();
                    if ($reviewCount >= 2) {
                        $fail('This order already has the maximum number of reviews (2).');
                    }
                },
            ],
            'reviewer_id' => 'required|exists:users,id|different:reviewee_id',
            'reviewee_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:255',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'reviewer_id.different' => 'The reviewer cannot be the same as the person being reviewed.',
        ];
    }
}
