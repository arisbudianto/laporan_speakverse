<section>

    <!-- HEADER -->
    <header class="mb-8">

        <h2 class="text-2xl font-black">
            Update Password
        </h2>

        <p class="mt-2 text-slate-500 dark:text-slate-400">
            Use a strong password to keep your account secure.
        </p>

    </header>

    <!-- FORM -->
    <form method="post" action="{{ route('password.update') }}" class="space-y-6">

        @csrf
        @method('put')

        <!-- CURRENT PASSWORD -->
        <div>

            <label class="block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                Current Password
            </label>

            <input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password"
                class="w-full
                px-5 py-4 rounded-2xl
                bg-slate-50 dark:bg-white/[0.03]
                border border-slate-200 dark:border-white/10
                focus:outline-none
                focus:ring-2 focus:ring-cyan-500">

            @error('current_password', 'updatePassword')
                <p class="mt-2 text-sm text-red-400">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- NEW PASSWORD -->
        <div>

            <label class="block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                New Password
            </label>

            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                class="w-full
                px-5 py-4 rounded-2xl
                bg-slate-50 dark:bg-white/[0.03]
                border border-slate-200 dark:border-white/10
                focus:outline-none
                focus:ring-2 focus:ring-cyan-500">

            @error('password', 'updatePassword')
                <p class="mt-2 text-sm text-red-400">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- CONFIRM -->
        <div>

            <label class="block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                Confirm Password
            </label>

            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password"
                class="w-full
                px-5 py-4 rounded-2xl
                bg-slate-50 dark:bg-white/[0.03]
                border border-slate-200 dark:border-white/10
                focus:outline-none
                focus:ring-2 focus:ring-cyan-500">

        </div>

        <!-- BUTTON -->
        <div class="flex items-center gap-4 pt-2">

            <button type="submit"
                class="px-8 py-4 rounded-2xl
                bg-gradient-to-r from-cyan-500 to-blue-600
                text-white font-bold
                shadow-lg shadow-cyan-500/20
                hover:scale-[1.02]
                transition-all duration-200">

                Update Password

            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-green-400">

                    Password updated successfully.

                </p>
            @endif

        </div>

    </form>

</section>
