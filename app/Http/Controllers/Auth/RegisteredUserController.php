<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $schools = config('schools', []);

        return view('auth.register', compact('schools'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $schools = config('schools', []);
        $schoolNames = array_keys($schools);
        $selectedMajors = $schools[$request->school] ?? [];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'school' => ['required', 'string', Rule::in($schoolNames)],
            'major' => ['required', 'string', Rule::in($selectedMajors)],
            'grade' => ['required', 'string', Rule::in(['X', 'XI', 'XII', 'S1'])],
            'parallel' => ['required', 'string', Rule::in(['A', 'B', 'C', 'D'])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school' => $request->school,
            'major' => $request->major,
            'grade' => $request->grade,
            'parallel' => $request->parallel,
        ]);

        // 'role' bukan mass-assignable (lihat App\Models\User), jadi
        // diset secara eksplisit di sini. Setiap akun yang mendaftar
        // sendiri lewat form ini selalu berupa 'student'.
        $user->forceFill([
            'role' => 'student',
            'email_verified_at' => now(),
        ])->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
