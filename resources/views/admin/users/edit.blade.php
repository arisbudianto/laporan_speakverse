@extends('layouts.admin')

@section('content')
    <div class="max-w-3xl mx-auto">

        <div class="mb-8">

            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-2
                text-sm font-semibold
                text-slate-500 dark:text-slate-400
                hover:text-cyan-500 transition">

                ← Back to Users

            </a>

            <h1 class="mt-4 text-3xl lg:text-4xl font-black">
                Edit User
            </h1>

            <p class="mt-2 text-slate-500 dark:text-slate-400">
                Update account information for {{ $user->name }}
            </p>

        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST"
            class="space-y-6 rounded-[32px]
            bg-white dark:bg-white/[0.03]
            border border-slate-200 dark:border-white/10
            p-6 lg:p-8 shadow-sm">

            @csrf
            @method('PUT')

            <div>

                <label for="name" class="block mb-2 font-bold">
                    Full Name
                </label>

                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                    autofocus
                    class="w-full rounded-2xl
                    border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-white/5
                    px-5 py-4
                    focus:border-cyan-500 focus:ring-cyan-500">

                @error('name')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div>

                <label for="email" class="block mb-2 font-bold">
                    Email Address
                </label>

                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full rounded-2xl
                    border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-white/5
                    px-5 py-4
                    focus:border-cyan-500 focus:ring-cyan-500">

                @error('email')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <div>
                <label for="nip" class="block mb-2 font-bold">NIP</label>
                <input id="nip" type="text" name="nip" value="{{ old('nip', $user->nip) }}"
                    class="w-full rounded-2xl border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 px-5 py-4 focus:border-cyan-500 focus:ring-cyan-500">
                @error('nip')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

@include('partials.school-major-grade-fields', [
                'schools' => $schools ?? config('schools', []),
                'selectedSchool' => old('school', $user->school),
                'selectedMajor' => old('major', $user->major),
                'selectedGrade' => old('grade', $user->grade),
                'selectedParallel' => old('parallel', $user->parallel),
                'required' => false,
            ])

            <div>

                <label for="role" class="block mb-2 font-bold">
                    Role
                </label>

                <select id="role" name="role" required
                    class="w-full rounded-2xl
                    border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-[#081120]
                    px-5 py-4
                    focus:border-cyan-500 focus:ring-cyan-500">

                    <option value="student" @selected(in_array(old('role', $user->role), ['student', 'user'], true))>
                        Student
                    </option>

                    <option value="admin" @selected(old('role', $user->role) === 'admin')>
                        Administrator
                    </option>

                    <option value="teacher" @selected(old('role', $user->role) === 'teacher')>
                        Teacher
                    </option>

                </select>

                @error('role')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div>

                <label for="password" class="block mb-2 font-bold">
                    New Password
                </label>

                <input id="password" type="password" name="password"
                    class="w-full rounded-2xl
                    border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-white/5
                    px-5 py-4
                    focus:border-cyan-500 focus:ring-cyan-500">

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Leave blank when you do not want to change the password.
                </p>

                @error('password')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div>

                <label for="password_confirmation" class="block mb-2 font-bold">
                    Confirm New Password
                </label>

                <input id="password_confirmation" type="password" name="password_confirmation"
                    class="w-full rounded-2xl
                    border-slate-200 dark:border-white/10
                    bg-slate-50 dark:bg-white/5
                    px-5 py-4
                    focus:border-cyan-500 focus:ring-cyan-500">

            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">

                <a href="{{ route('admin.users.index') }}"
                    class="px-6 py-4 rounded-2xl
                    bg-slate-100 dark:bg-white/5
                    font-bold text-center
                    hover:bg-slate-200 dark:hover:bg-white/10
                    transition">

                    Cancel

                </a>

                <button type="submit"
                    class="px-6 py-4 rounded-2xl
                    bg-gradient-to-r from-cyan-500 to-blue-600
                    text-white font-bold
                    shadow-lg shadow-cyan-500/20
                    hover:scale-[1.02]
                    transition">

                    Update User

                </button>

            </div>

        </form>

    </div>
@endsection
