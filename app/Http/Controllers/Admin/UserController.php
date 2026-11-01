<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display all registered users.
     */
    public function index()
    {
        $users = User::query()
            ->latest('created_at')
            ->paginate(10);

        return view(
            'admin.users.index',
            compact('users')
        );
    }

    /**
     * Display the create user page.
     */
    public function create()
    {
        return view('admin.users.create', [
            'schools' => config('schools', []),
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'nip' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'school' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys(config('schools', []))),
                ],

                'major' => [
                    'nullable',
                    'string',
                    Rule::in((config('schools', [])[$request->school] ?? [])),
                ],

                'grade' => [
                    'nullable',
                    'string',
                    Rule::in(['X', 'XI', 'XII', 'S1']),
                ],

                'parallel' => [
                    'nullable',
                    'string',
                    Rule::in(['A', 'B', 'C', 'D']),
                ],

                'role' => [
                    'required',
                    Rule::in(['admin', 'user', 'student', 'teacher']),
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'name.required' =>
                    'The name field is required.',

                'name.string' =>
                    'The name must be valid text.',

                'name.max' =>
                    'The name may not be longer than 255 characters.',

                'email.required' =>
                    'The email field is required.',

                'email.email' =>
                    'Please enter a valid email address.',

                'email.max' =>
                    'The email may not be longer than 255 characters.',

                'email.unique' =>
                    'This email address is already registered.',

                'role.required' =>
                    'Please select a user role.',

                'role.in' =>
                    'The selected role is invalid.',

                'password.required' =>
                    'The password field is required.',

                'password.string' =>
                    'The password must be valid text.',

                'password.min' =>
                    'The password must be at least 8 characters.',

                'password.confirmed' =>
                    'The password confirmation does not match.',
            ]
        );

        // 'role' bukan mass-assignable secara default (lihat
        // App\Models\User). forceFill() dipakai di sini karena route
        // ini sudah dibatasi middleware 'admin', jadi hanya admin
        // yang benar-benar login yang bisa mengubah role user lain.
        (new User())
            ->forceFill($validated)
            ->save();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User added successfully.'
            );
    }

    /**
     * Display the edit user page.
     */
    public function edit(User $user)
    {
        return view(
            'admin.users.edit',
            [
                'user' => $user,
                'schools' => config('schools', []),
            ]
        );
    }

    /**
     * Update an existing user.
     */
    public function update(
        Request $request,
        User $user
    ) {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique(
                        'users',
                        'email'
                    )->ignore($user->id),
                ],

                'nip' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'school' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys(config('schools', []))),
                ],

                'major' => [
                    'nullable',
                    'string',
                    Rule::in((config('schools', [])[$request->school] ?? [])),
                ],

                'grade' => [
                    'nullable',
                    'string',
                    Rule::in(['X', 'XI', 'XII', 'S1']),
                ],

                'parallel' => [
                    'nullable',
                    'string',
                    Rule::in(['A', 'B', 'C', 'D']),
                ],

                'role' => [
                    'required',
                    Rule::in(['admin', 'user', 'student', 'teacher']),
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'name.required' =>
                    'The name field is required.',

                'name.string' =>
                    'The name must be valid text.',

                'name.max' =>
                    'The name may not be longer than 255 characters.',

                'email.required' =>
                    'The email field is required.',

                'email.email' =>
                    'Please enter a valid email address.',

                'email.max' =>
                    'The email may not be longer than 255 characters.',

                'email.unique' =>
                    'This email address is already registered.',

                'role.required' =>
                    'Please select a user role.',

                'role.in' =>
                    'The selected role is invalid.',

                'password.string' =>
                    'The password must be valid text.',

                'password.min' =>
                    'The password must be at least 8 characters.',

                'password.confirmed' =>
                    'The password confirmation does not match.',
            ]
        );

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Sama seperti store(): forceFill() dipakai karena route ini
        // hanya bisa diakses admin, dan form ini memang dimaksudkan
        // untuk bisa mengubah role user lain.
        $user->forceFill($validated)->save();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User updated successfully.'
            );
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with(
                    'error',
                    'You cannot delete the administrator account that is currently signed in.'
                );
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User deleted successfully.'
            );
    }
}
