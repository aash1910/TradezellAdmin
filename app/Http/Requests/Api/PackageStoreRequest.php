<?php

namespace App\Http\Requests\Api;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PackageStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        return $user instanceof User && $user->hasRole('sender');
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'You are not authorized to create packages. Only senders can create packages.',
        ], 403));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pickup_name' => 'required|string',
            'pickup_mobile' => 'required|string',
            'pickup_address' => 'required|string',
            'pickup_details' => 'nullable|string',
            'weight' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0.01',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|date_format:H:i',
            'drop_name' => 'required|string',
            'drop_mobile' => 'required|string',
            'drop_address' => 'required|string',
            'drop_details' => 'nullable|string',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'drop_lat' => 'nullable|numeric',
            'drop_lng' => 'nullable|numeric',
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
            'pickup_date.after_or_equal' => 'The pickup date must be today or a future date',
            'pickup_time.date_format' => 'The pickup time must be in 24-hour format (HH:mm)',
        ];
    }
} 