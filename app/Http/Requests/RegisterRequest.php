<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
            'terms' => [
                'required',
                'accepted',
            ],
            'preferred_language' => [
                'nullable',
                'string',
                'max:10',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Имя пользователя обязательно для заполнения.',
            'username.min' => 'Имя пользователя должно содержать минимум :min символов.',
            'username.max' => 'Имя пользователя не должно превышать :max символов.',
            'username.regex' => 'Имя пользователя может содержать только буквы, цифры и подчеркивания.',
            'username.unique' => 'Это имя пользователя уже занято.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.email' => 'Пожалуйста, введите корректный email адрес.',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'password.required' => 'Пароль обязателен для заполнения.',
            'password.min' => 'Пароль должен содержать минимум :min символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'terms.required' => 'Необходимо принять пользовательское соглашение.',
            'terms.accepted' => 'Необходимо принять пользовательское соглашение.',
        ];
    }
}
