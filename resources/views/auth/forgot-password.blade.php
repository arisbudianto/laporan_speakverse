<x-guest-layout>

    <!-- TITLE -->
    <div class="mb-8">

        <h2 class="text-4xl font-bold text-white mb-3">
            Reset Password
        </h2>

        <p class="text-slate-400 leading-relaxed">
            Forgot your password? Enter your email address and we’ll send you
            a password reset link to create a new password.
        </p>

    </div>

    <!-- SESSION STATUS -->
    <x-auth-session-status class="mb-6 text-sm text-green-400" :status="session('status')" />

    <!-- FORM -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">

        @csrf

        <!-- EMAIL -->
        <div>

            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">

                Email Address

            </label>

            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                placeholder="Enter your email"
                class="w-full rounded-2xl border border-slate-700 bg-slate-900/60 px-5 py-4 text-white placeholder:text-slate-500 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 transition-all duration-300 outline-none" />

            @error('email')
                <p class="mt-2 text-sm text-red-400">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- BUTTON -->
        <button type="submit"
            class="w-full py-4 rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold text-lg hover:scale-[1.01] hover:shadow-xl hover:shadow-cyan-500/20 transition-all duration-300">

            Send Reset Link

        </button>

        <!-- BACK TO LOGIN -->
        <div class="text-center pt-2">

            <a href="{{ route('login') }}" class="text-sm text-cyan-400 hover:text-cyan-300 transition">

                ← Back to Login

            </a>

        </div>

    </form>

</x-guest-layout>
