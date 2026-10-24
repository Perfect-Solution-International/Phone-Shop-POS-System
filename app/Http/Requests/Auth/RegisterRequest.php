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
        return [
            'shop_name' => ['required', 'string', 'min:2', 'max:100'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s\-()]{7,25}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'business_reg_number' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()],
            'password_confirmation' => ['required', 'string'],
            'terms' => ['accepted'],
        ];
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
            'terms.accepted' => 'You must accept the Terms of Service and Privacy Policy.',
        ];
    }
}

