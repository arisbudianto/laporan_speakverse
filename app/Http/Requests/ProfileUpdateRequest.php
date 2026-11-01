<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schools = config('schools', []);
        $schoolNames = array_keys($schools);
        $majors = $schools[$this->input('school')] ?? [];
        $role = $this->user()?->role;
        $isStaff = in_array($role, ['admin', 'teacher'], true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'school' => [$role === 'admin' ? 'nullable' : 'required', 'string', Rule::in($schoolNames)],
            'major' => [$isStaff ? 'nullable' : 'required', 'string', Rule::in($majors)],
            'grade' => [$isStaff ? 'nullable' : 'required', 'string', Rule::in(['X', 'XI', 'XII', 'S1'])],
            'parallel' => [$isStaff ? 'nullable' : 'required', 'string', Rule::in(['A', 'B', 'C', 'D'])],
        ];
    }
}
