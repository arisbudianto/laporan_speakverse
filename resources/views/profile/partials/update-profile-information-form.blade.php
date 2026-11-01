<section>

    <!-- HEADER -->
    <header class="mb-8">

        <h2 class="text-2xl font-black">
            Profile Information
        </h2>

        <p class="mt-2 text-slate-500 dark:text-slate-400">
            Update your account profile information and email address.
        </p>

    </header>

    <!-- VERIFY -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- FORM -->
    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">

        @csrf
        @method('patch')

        <!-- NAME -->
        <div>

            <label class="block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                Full Name
            </label>

            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                autofocus autocomplete="name"
                class="w-full
                px-5 py-4 rounded-2xl
                bg-slate-50 dark:bg-white/[0.03]
                border border-slate-200 dark:border-white/10
                focus:outline-none
                focus:ring-2 focus:ring-cyan-500
                transition-all duration-200">

            @error('name')
                <p class="mt-2 text-sm text-red-400">
                    {{ $message }}
                </p>
            @enderror

        </div>

        <!-- EMAIL -->
        <div>

            <label class="block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                Email Address
            </label>

            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                autocomplete="username"
                class="w-full
                px-5 py-4 rounded-2xl
                bg-slate-50 dark:bg-white/[0.03]
                border border-slate-200 dark:border-white/10
                focus:outline-none
                focus:ring-2 focus:ring-cyan-500
                transition-all duration-200">

            @error('email')
                <p class="mt-2 text-sm text-red-400">
                    {{ $message }}
                </p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())

                <div class="mt-4 p-4 rounded-2xl bg-yellow-500/10 border border-yellow-500/20">

                    <p class="text-sm text-yellow-500 font-medium">
                        Your email address is unverified.
                    </p>

                    <button form="send-verification"
                        class="mt-3 text-sm font-bold text-cyan-400 hover:text-cyan-300 transition">

                        Click here to re-send verification email.

                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 text-sm text-green-400 font-medium">
                            Verification link has been sent successfully.
                        </p>
                    @endif

                </div>

            @endif

        </div>

        @if (($user->role ?? null) === 'teacher')
            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 text-sm text-cyan-800">
                Kelas dan jurusan yang diampu diatur di menu Admin → Guru.
                Satu guru dapat mengampu beberapa jurusan dan kelas berbeda.
            </div>
            @if ($user->relationLoaded('teachingAssignments') || method_exists($user, 'teachingAssignments'))
                @php $ampu = $user->teachingAssignments ?? collect(); @endphp
                @if ($ampu->count())
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="mb-2 text-sm font-bold">Kelas yang diampu</p>
                        <ul class="space-y-1 text-sm text-slate-600">
                            @foreach ($ampu as $row)
                                <li>{{ $row->school }} — {{ $row->major }} — {{ $row->grade }} {{ $row->parallel }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        @endif

        @include('partials.school-major-grade-fields', [
            'schools' => $schools ?? config('schools', []),
            'selectedSchool' => old('school', $user->school),
            'selectedMajor' => old('major', $user->major),
            'selectedGrade' => old('grade', $user->grade),
            'selectedParallel' => old('parallel', $user->parallel),
            'required' => true,
            'showMajor' => ($user->role ?? null) !== 'teacher',
            'showClassFields' => ! in_array($user->role ?? null, ['teacher', 'admin'], true),
            'labelClass' => 'block mb-3 text-sm font-bold text-slate-700 dark:text-slate-300',
            'inputClass' => 'w-full px-5 py-4 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all duration-200',
        ])

        <!-- BUTTON -->
        <div class="flex items-center gap-4 pt-2">

            <button type="submit"
                class="px-8 py-4 rounded-2xl
                bg-gradient-to-r from-cyan-500 to-blue-600
                text-white font-bold
                shadow-lg shadow-cyan-500/20
                hover:scale-[1.02]
                transition-all duration-200">

                Save Changes

            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-green-400">

                    Profile updated successfully.

                </p>
            @endif

        </div>

    </form>

</section>
