<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        // Clean MC Number: strip optional "MC" or "MC-" prefix and whitespace
        if ($this->has('mc_number') && $this->mc_number !== null) {
            $cleanedMc = preg_replace('/^MC[- ]?/i', '', trim($this->mc_number));
            $mergeData['mc_number'] = $cleanedMc;
        }

        // Clean DOT: trim whitespace
        if ($this->has('dot') && $this->dot !== null) {
            $cleanedDot = trim($this->dot);
            $mergeData['dot'] = $cleanedDot !== '' ? $cleanedDot : null;
        }

        // Format Contact Number to (XXX) XXX-XXXX if standard digits are entered
        if ($this->has('number') && $this->number !== null) {
            $digits = preg_replace('/[^\d]/', '', $this->number);
            if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
                $digits = substr($digits, 1);
            }
            if (strlen($digits) === 10) {
                $mergeData['number'] = sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
            }
        }

        // Strip dollar signs and whitespace from RPM
        if ($this->has('rpm') && $this->rpm !== null) {
            $mergeData['rpm'] = ltrim(trim($this->rpm), '$ ');
        }

        // Strip commas and whitespace from maximum weight
        if ($this->has('maximum_weight') && $this->maximum_weight !== null) {
            $mergeData['maximum_weight'] = str_replace([',', ' '], '', $this->maximum_weight);
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $isAdmin = Auth::user() && Auth::user()->hasRole('Admin');

        return [
            'agent_id' => [$isAdmin ? 'required' : 'nullable', 'exists:users,id'],
            'assign_to' => ['nullable', 'integer', 'exists:users,id'],
            'mc_number' => ['required', 'numeric', 'unique:carriers,mc_number'],
            'dot' => ['nullable', 'numeric'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'number' => ['required', 'string', 'regex:/^\(\d{3}\) \d{3}-\d{4}$/'],
            'company_name' => ['required', 'string', 'max:255'],
            'truck_type' => ['required', 'integer'],
            'truck_size' => ['required', 'integer'],
            'maximum_weight' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', 'integer'],
            'percent_flat' => ['required', 'string', 'in:percentage,flat_rate'],
            'charge_type' => ['required', 'string', 'max:255'],
            'rpm' => ['required', 'numeric', 'between:0,9999999999.99'],
            'street_address' => ['required', 'string', 'max:255'],
            'city_name' => ['required', 'string', 'max:255'],
            'state_name' => ['required', 'integer'],
            'zip_code' => ['required', 'string', 'max:20'],
            'comment' => ['nullable', 'string'],

            // Documents are optional on create, but if uploaded, must be valid files and allowed mime types
            'mc_letter' => ['nullable', 'file', 'mimes:png,jpg,jpeg,doc,docx,pdf', 'max:20480'],
            'w_form' => ['nullable', 'file', 'mimes:png,jpg,jpeg,doc,docx,pdf', 'max:20480'],
            'coi' => ['nullable', 'file', 'mimes:png,jpg,jpeg,doc,docx,pdf', 'max:20480'],
            'noa' => ['nullable', 'file', 'mimes:png,jpg,jpeg,doc,docx,pdf', 'max:20480'],
            'void_cheque' => ['nullable', 'file', 'mimes:png,jpg,jpeg,doc,docx,pdf', 'max:20480'],
            'extra_document' => ['nullable', 'file', 'mimes:png,jpg,jpeg,doc,docx,pdf', 'max:20480'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'agent_id' => 'sales agent',
            'mc_number' => 'MC number',
            'dot' => 'DOT number',
            'name' => 'carrier contact name',
            'email' => 'carrier email',
            'number' => 'contact number',
            'company_name' => 'company name',
            'truck_type' => 'truck type',
            'truck_size' => 'truck size',
            'maximum_weight' => 'maximum weight',
            'payment_type' => 'payment type',
            'percent_flat' => 'percentage / flat rate',
            'charge_type' => 'charge rate / value',
            'rpm' => 'expected RPM',
            'street_address' => 'street address',
            'city_name' => 'city',
            'state_name' => 'state',
            'zip_code' => 'zip code',
            'mc_letter' => 'MC authority letter',
            'w_form' => 'W-9 form',
            'coi' => 'certificate of insurance (COI)',
            'noa' => 'notice of assignment (NOA)',
            'void_cheque' => 'void cheque',
            'extra_document' => 'extra document',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'number.regex' => 'The contact number must be in (XXX) XXX-XXXX format (e.g. (888) 123-4567).',
            'mc_number.unique' => 'This MC number is already registered.',
            'agent_id.required' => 'Please select a sales agent.',
        ];
    }
}
