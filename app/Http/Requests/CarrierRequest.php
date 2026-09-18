<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarrierRequest extends FormRequest
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
            'mc_number' => ['required', 'numeric', 'unique:carriers'],
            'dot' => ['required', 'numeric'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'number' => ['required', 'string', 'regex:/^\(\d{3}\) \d{3}-\d{4}$/'],
            'company_name' => ['required', 'string'],
            'truck_type' => ['required', 'integer'],
            'truck_size' => ['required', 'integer'],
            'maximum_weight' => ['required', 'integer'],
            'payment_type' => ['required', 'integer'],
            'charge_type' => ['required', 'string', 'max:255'],
            'street_address' => ['required', 'string'],
            'city_name' => ['required', 'string'],
            'state_name' => ['required', 'integer'],
            'zip_code' => ['required', 'numeric'],
            'rpm' => ['required','numeric','between:0,9999999999.99'],
            'mc_letter' => 'required|mimes:png,jpg,jpeg,doc,docx,pdf',
            'w_form' => 'required|mimes:png,jpg,jpeg,doc,docx,pdf',
            'coi' => 'required|mimes:png,jpg,jpeg,doc,docx,pdf',
            'noa' => 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf',
            'void_cheque' => 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf',
            'extra_document' => 'nullable|mimes:png,jpg,jpeg,doc,docx,pdf',
        ];

        if ($this->filled('mc_letter')) {
            $rules['mc_letter'] = 'required|file|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        if ($this->filled('w_form')) {
            $rules['w_form'] = 'required|file|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        if ($this->filled('coi')) {
            $rules['coi'] = 'required|file|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        if ($this->filled('noa')) {
            $rules['noa'] = 'required|file|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        if ($this->filled('void_cheque')) {
            $rules['void_cheque'] = 'required|file|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        if ($this->filled('extra_document')) {
            $rules['extra_document'] = 'required|file|mimes:png,jpg,jpeg,doc,docx,pdf';
        }

        return $rules;
    }
}
