<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DispatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'mc_number' => ['required', 'numeric'],
            'owner_name' => ['required', 'string', 'max:255'],
            'load_number' => ['required', 'string', 'max:255'],
            'pick_location' => ['required', 'string', 'max:255'],
            'delivery_location' => ['required', 'string', 'max:255'],
            // 'load_date' => ['required', 'date'],
            'pick_date' => ['required', 'date'],
            'delivery_date' => ['required', 'date'],
            'driver_name' => ['required', 'string', 'max:100'],
            'truck_number' => ['required', 'string', 'max:50'],
            'trailer_number' => ['nullable', 'string', 'max:50'],
            'driver_number' => ['required', 'string', 'regex:/^\(\d{3}\) \d{3}-\d{4}$/'],
            'total_miles' => ['required', 'integer'],
            'rate' => 'required|numeric|between:0,9999999999.99',
            'percentage' => ['nullable','numeric','between:0,99.99'],
            'receivable' => 'nullable|numeric|between:0,9999999999.99',
            'broker_company_name' => ['required', 'string', 'max:255'],
            'broker_mc' => ['required', 'numeric'],
            'broker_number' => ['required','string'],
            'broker_email' => ['required', 'string', 'email'],
            'broker_rep_name' => ['required', 'string'],
            'bol_pod' => 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf',
            'additional_doc' => 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf',
        ];

        $user = Auth::user();
        if ($user->can('add-previous-load-date')) {
            $rules['load_date'] = ['required', 'date'];
        } else {
            $rules['load_date'] = ['required', 'date', 'after_or_equal:today'];
        }

        // Check if it's an Update request
        if ($this->has('Update')) {
            // If it is, remove the 'image' validation rule
            $rules['rate_confirmation'] = 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf';
        } else {
            // If it's a post request, include the 'image' validation rule
            $rules['rate_confirmation'] = 'required|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        return $rules;
    }
}
