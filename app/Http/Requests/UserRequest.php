<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'regex:/^\+7\d{10}$/',
                Rule::unique('users')->ignore($this->route('user')) // Тут ignore исключаем текущего юзера и проверки номера, если редактируем учетку
            ],
            'birth_date' => ['required', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'favorite_wine_id' => ['nullable', 'exists:wines,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'Пользователь с таким номером телефона уже существует',
            'phone.regex' => 'Телефон должен быть в формате +7XXXXXXXXXX',
            'birth_date.required' => 'Дата рождения обязательна',
            'name.required' => 'Имя обязательно'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Логируем входящие данные
        Log::info('Входящие данные формы', $this->all());
    }
}
