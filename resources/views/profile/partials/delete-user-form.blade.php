<section class="space-y-6">

    <!-- HEADER -->
    <header>

        <h2 class="text-2xl font-black text-red-400">
            Delete Account
        </h2>

        <p class="mt-3 text-slate-500 dark:text-slate-400">
            Permanently delete your account and all associated data.
            This action cannot be undone.
        </p>

    </header>

    <!-- BUTTON -->
    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-8 py-4 rounded-2xl
        bg-red-500/10
        text-red-400
        font-bold
        hover:bg-red-500/20
        transition-all duration-200">

        Delete Account

    </button>

    <!-- MODAL -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>

        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 bg-white dark:bg-[#081120]">

            @csrf
            @method('delete')

            <h2 class="text-2xl font-black text-red-400">
                Confirm Account Deletion
            </h2>

            <p class="mt-3 text-slate-500 dark:text-slate-400 leading-relaxed">
                Once your account is deleted, all resources and data will be permanently removed.
                Please enter your password to confirm.
            </p>

            <!-- PASSWORD -->
            <div class="mt-6">

                <label class="block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                    Password
                </label>

                <input id="password" name="password" type="password" placeholder="Enter your password"
                    class="w-full
                    px-5 py-4 rounded-2xl
                    bg-slate-50 dark:bg-white/[0.03]
                    border border-slate-200 dark:border-white/10
                    focus:outline-none
                    focus:ring-2 focus:ring-red-500">

                @error('password', 'userDeletion')
                    <p class="mt-2 text-sm text-red-400">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <!-- BUTTON -->
            <div class="mt-8 flex justify-end gap-3">

                <button type="button" x-on:click="$dispatch('close')"
                    class="px-6 py-3 rounded-2xl
                    bg-slate-100 dark:bg-white/5
                    hover:bg-slate-200 dark:hover:bg-white/10
                    transition-all duration-200
                    font-semibold">

                    Cancel

                </button>

                <button type="submit"
                    class="px-6 py-3 rounded-2xl
                    bg-red-500
                    text-white font-bold
                    hover:bg-red-600
                    transition-all duration-200">

                    Delete Permanently

                </button>

            </div>

        </form>

    </x-modal>

</section>
