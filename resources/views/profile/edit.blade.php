<x-app-layout>

    <!-- HEADER -->
    <div class="mb-8">

        <h1 class="text-4xl font-black leading-tight">
            Profile Settings
        </h1>

        <p class="mt-3 text-slate-500 dark:text-slate-400 text-lg">
            Manage your account settings and security preferences
        </p>

    </div>

    <!-- PROFILE INFO -->
    <div
        class="rounded-[32px]
        bg-white dark:bg-white/[0.03]
        border border-slate-200 dark:border-white/10
        shadow-sm
        p-6 lg:p-8 mb-8">

        <div class="flex items-center gap-5 mb-8">

            <div
                class="w-20 h-20 rounded-3xl
                bg-gradient-to-br from-cyan-400 to-blue-600
                flex items-center justify-center
                text-white text-3xl font-black
                shadow-lg shadow-cyan-500/20">

                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

            </div>

            <div>

                <h2 class="text-2xl font-black">
                    {{ Auth::user()->name }}
                </h2>

                <p class="mt-2 text-slate-500 dark:text-slate-400">
                    {{ Auth::user()->email }}
                </p>

                <div
                    class="mt-3 inline-flex items-center
                    px-4 py-2 rounded-2xl
                    bg-cyan-500/10
                    text-cyan-400
                    text-sm font-bold">

                    {{ ucfirst(Auth::user()->role ?? 'Student') }}

                </div>

            </div>

        </div>

        @include('profile.partials.update-profile-information-form')

    </div>

    <!-- UPDATE PASSWORD -->
    <div
        class="rounded-[32px]
        bg-white dark:bg-white/[0.03]
        border border-slate-200 dark:border-white/10
        shadow-sm
        p-6 lg:p-8 mb-8">

        @include('profile.partials.update-password-form')

    </div>

    <!-- DELETE ACCOUNT -->
    <div
        class="rounded-[32px]
        bg-white dark:bg-white/[0.03]
        border border-red-500/20
        shadow-sm
        p-6 lg:p-8">

        @include('profile.partials.delete-user-form')

    </div>

</x-app-layout>
