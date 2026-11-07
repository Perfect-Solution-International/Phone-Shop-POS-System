<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentMethod = $this->input('payment_method', 'card');
        $isCard = $paymentMethod !== 'bank_transfer';

        $rules = [
            'shop_name' => ['required', 'string', 'min:2', 'max:100'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s\-()]{7,25}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'business_reg_number' => ['nullable', 'string', 'max:100'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['nullable', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'in:card,bank_transfer'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'password_confirmation' => ['required', 'string'],
            'terms' => ['accepted'],
        ];

        if ($isCard) {
            $rules['cardholder_name'] = ['required', 'string', 'min:2', 'max:100'];
            $rules['card_number'] = ['required', 'string', 'min:12', 'max:25'];
            $rules['card_expiry'] = ['required', 'string', 'max:7', 'regex:/^(0[1-9]|1[0-2])\/?([0-9]{2})$/'];
            $rules['card_cvv'] = ['required', 'string', 'min:3', 'max:4'];
        } else {
            $rules['reference_number'] = ['required', 'string', 'max:100'];
            $rules['receipt'] = ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'shop_name.required' => 'Please provide a name for your shop workspace.',
            'shop_name.min' => 'Shop name must be at least 2 characters.',
            'shop_name.max' => 'Shop name cannot exceed 100 characters.',
            'name.required' => 'Please enter the shop owner’s full name.',
            'phone.required' => 'Please enter a contact mobile number.',
            'phone.regex' => 'Please provide a valid mobile number (e.g. +94 77 123 4567).',
            'email.required' => 'Please provide an email address.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'An account with this email address already exists.',
            'password.required' => 'A secure password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'plan_id.required' => 'Please select a subscription plan for your shop.',
            'plan_id.exists' => 'The selected subscription plan is invalid.',
            'cardholder_name.required' => 'Please enter the name on your card.',
            'card_number.required' => 'Please enter your credit or debit card number to activate the 7-day free trial.',
            'card_expiry.required' => 'Please enter your card expiry date (MM/YY).',
            'card_expiry.regex' => 'Card expiry must be in MM/YY format (e.g. 12/28).',
            'card_cvv.required' => 'Please enter the CVV / CVC code on the back of your card.',
            'reference_number.required' => 'Please enter your bank transfer / deposit slip reference number.',
            'receipt.mimes' => 'The payment receipt must be a JPG, PNG, or PDF file.',
            'receipt.max' => 'The payment receipt file size cannot exceed 5MB.',
            'terms.accepted' => 'You must accept the Terms of Service and Privacy Policy.',
        ];
    }
}

