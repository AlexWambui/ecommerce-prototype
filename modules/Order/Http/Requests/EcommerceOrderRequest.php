<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EcommerceOrderRequest extends FormRequest
{
    /**
     * Public storefront — no auth required. Guest checkout is allowed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for an ecommerce order submission.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Customer
            'customer_name' => ['required', 'string', 'max:200'],
            'customer_phone' => ['required', 'phone:INTERNATIONAL'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],

            // Shipping
            'delivery_method' => ['required', 'in:delivery,pickup'],
            'delivery_location_id' => [
                'required_if:delivery_method,delivery',
                'nullable',
                'integer',
                Rule::exists('delivery_locations', 'id')->where('is_active', true),
            ],
            'delivery_area_id' => [
                'required_if:delivery_method,delivery',
                'nullable',
                'integer',
                Rule::exists('delivery_areas', 'id')
                    ->where('is_active', true)
                    ->where('delivery_location_id', $this->input('delivery_location_id')),
            ],
            'delivery_address' => ['required_if:delivery_method,delivery', 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Payments (optional at creation)
            'payments' => ['sometimes', 'array'],
            'payments.*.method' => ['required_with:payments', 'string', 'max:50'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom messages for specific failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_email.email' => 'Please provide a valid email address.',
            'customer_phone.phone' => 'Please provide a valid phone number.',
            'customer_phone.required' => 'Please enter your phone number',
            // 'customer_phone.regex' => 'Please provide a valid phone number (7–15 digits, optionally starting with +).',
        ];
    }

    /**
     * Normalize input before validation runs.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from text fields.
        $this->merge([
            'customer_name' => is_string($this->customer_name) ? trim($this->customer_name) : $this->customer_name,
            'customer_email' => is_string($this->customer_email) ? trim($this->customer_email) : $this->customer_email,
            'customer_phone' => is_string($this->customer_phone)
            ? $this->normalizePhone($this->customer_phone)
            : $this->customer_phone,
            // 'customer_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9]{7,15}$/'],
        ]);
    }

    /**
     * Strip formatting characters so the phone validator sees a clean number.
     * Keeps a leading "+" if present, removes everything else non-numeric.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = trim($phone);

        // Keep the leading "+" if present; strip spaces, parens, dashes, dots.
        return preg_replace('/(?!^\+)[^\d]/', '', $phone);
    }
}
