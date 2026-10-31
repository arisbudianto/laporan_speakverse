<x-guest-layout>


    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="sv-auth-heading">

        <div class="sv-auth-eyebrow">

            <span class="sv-auth-eyebrow-dot"></span>

            Get Started

        </div>


        <h1 class="sv-auth-title">
            Create your account
        </h1>


        <p class="sv-auth-description">
            Start your English learning journey with SpeakVerse.
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- GOOGLE ERROR --}}
    {{-- ========================================================= --}}

    @error('google')

        <div class="sv-alert sv-alert-error">
            {{ $message }}
        </div>

    @enderror


    {{-- ========================================================= --}}
    {{-- GOOGLE --}}
    {{-- ========================================================= --}}

    <a
        href="{{ route('google.redirect') }}"
        class="sv-google-button"
    >

        <svg
            viewBox="0 0 24 24"
            aria-hidden="true"
        >

            <path
                fill="#4285F4"
                d="
                    M21.6 12.23
                    c0-.71-.06-1.4-.18-2.06
                    H12v3.9h5.38
                    a4.6 4.6 0 0 1-1.99 3.02
                    v2.53h3.22
                    c1.88-1.73 2.99-4.28 2.99-7.39
                "
            />

            <path
                fill="#34A853"
                d="
                    M12 22
                    c2.7 0 4.96-.9 6.61-2.38
                    l-3.22-2.53
                    c-.9.6-2.04.96-3.39.96
                    -2.6 0-4.8-1.75-5.59-4.11
                    H3.08v2.6
                    A10 10 0 0 0 12 22
                "
            />

            <path
                fill="#FBBC05"
                d="
                    M6.41 13.94
                    A6.02 6.02 0 0 1 6.1 12
                    c0-.67.11-1.32.31-1.94
                    v-2.6H3.08
                    A10 10 0 0 0 2 12
                    c0 1.61.39 3.13 1.08 4.54
                    l3.33-2.6
                "
            />

            <path
                fill="#EA4335"
                d="
                    M12 5.95
                    c1.47 0 2.79.51 3.83 1.5
                    l2.87-2.87
                    C16.95 2.95 14.7 2 12 2
                    a10 10 0 0 0-8.92 5.46
                    l3.33 2.6
                    C7.2 7.7 9.4 5.95 12 5.95
                "
            />

        </svg>


        Continue with Google

    </a>


    {{-- ========================================================= --}}
    {{-- DIVIDER --}}
    {{-- ========================================================= --}}

    <div class="sv-divider">
        <span>
            or
        </span>
    </div>


    {{-- ========================================================= --}}
    {{-- REGISTER FORM --}}
    {{-- ========================================================= --}}

    <form
        method="POST"
        action="{{ route('register') }}"
        class="sv-auth-form"
    >

        @csrf


        {{-- NAME --}}
        <div class="sv-field">

            <label
                for="name"
                class="sv-label"
            >
                Full Name
            </label>


            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="sv-input"
                placeholder="Enter your full name"
            >


            @error('name')

                <p class="sv-field-error">
                    {{ $message }}
                </p>

            @enderror

        </div>


        {{-- EMAIL --}}
        <div class="sv-field">

            <label
                for="email"
                class="sv-label"
            >
                Email Address
            </label>


            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="sv-input"
                placeholder="you@example.com"
            >


            @error('email')

                <p class="sv-field-error">
                    {{ $message }}
                </p>

            @enderror

        </div>


        {{-- SCHOOL --}}
        <div class="sv-field">
            <label for="school" class="sv-label">Sekolah</label>
            <select
                id="school"
                name="school"
                required
                class="sv-input"
            >
                <option value="">Pilih sekolah</option>
                @foreach (($schools ?? []) as $schoolName => $majors)
                    <option value="{{ $schoolName }}" @selected(old('school') === $schoolName)>
                        {{ $schoolName }}
                    </option>
                @endforeach
            </select>
            @error('school')
                <p class="sv-field-error">{{ $message }}</p>
            @enderror
        </div>


        {{-- MAJOR --}}
        <div class="sv-field">
            <label for="major" class="sv-label">Jurusan</label>
            <select
                id="major"
                name="major"
                required
                class="sv-input"
                @disabled(! old('school'))
            >
                <option value="">Pilih sekolah terlebih dahulu</option>
            </select>
            @error('major')
                <p class="sv-field-error">{{ $message }}</p>
            @enderror
        </div>


        {{-- GRADE --}}
        <div class="sv-field">
            <label for="grade" class="sv-label">Kelas</label>
            <select
                id="grade"
                name="grade"
                required
                class="sv-input"
            >
                <option value="">Pilih kelas</option>
                @foreach (['X', 'XI', 'XII', 'S1'] as $grade)
                    <option value="{{ $grade }}" @selected(old('grade') === $grade)>
                        {{ $grade }}
                    </option>
                @endforeach
            </select>
            @error('grade')
                <p class="sv-field-error">{{ $message }}</p>
            @enderror
        </div>


        {{-- PARALLEL --}}
        <div class="sv-field">
            <label for="parallel" class="sv-label">Paralel</label>
            <select
                id="parallel"
                name="parallel"
                required
                class="sv-input"
            >
                <option value="">Pilih paralel</option>
                @foreach (['A', 'B', 'C', 'D'] as $parallel)
                    <option value="{{ $parallel }}" @selected(old('parallel') === $parallel)>
                        {{ $parallel }}
                    </option>
                @endforeach
            </select>
            @error('parallel')
                <p class="sv-field-error">{{ $message }}</p>
            @enderror
        </div>


        {{-- PASSWORD --}}
        <div class="sv-field">

            <label
                for="password"
                class="sv-label"
            >
                Password
            </label>


            <div class="sv-input-wrapper">

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="sv-input sv-password-input"
                    placeholder="Create your password"
                >


                <button
                    type="button"
                    class="sv-password-toggle"
                    data-password-toggle="password"
                    aria-label="Show password"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="
                                M2.5 12
                                s3.5-6 9.5-6
                                9.5 6 9.5 6
                                -3.5 6-9.5 6
                                -9.5-6-9.5-6Z
                            "
                        />

                        <circle
                            cx="12"
                            cy="12"
                            r="2.5"
                        />
                    </svg>

                </button>

            </div>


            @error('password')

                <p class="sv-field-error">
                    {{ $message }}
                </p>

            @enderror

        </div>


        {{-- CONFIRM PASSWORD --}}
        <div class="sv-field">

            <label
                for="password_confirmation"
                class="sv-label"
            >
                Confirm Password
            </label>


            <div class="sv-input-wrapper">

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="sv-input sv-password-input"
                    placeholder="Confirm your password"
                >


                <button
                    type="button"
                    class="sv-password-toggle"
                    data-password-toggle="password_confirmation"
                    aria-label="Show password"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="
                                M2.5 12
                                s3.5-6 9.5-6
                                9.5 6 9.5 6
                                -3.5 6-9.5 6
                                -9.5-6-9.5-6Z
                            "
                        />

                        <circle
                            cx="12"
                            cy="12"
                            r="2.5"
                        />
                    </svg>

                </button>

            </div>

        </div>


        {{-- REGISTER --}}
        <button
            type="submit"
            class="sv-submit-button"
        >
            Create Account
        </button>

    </form>


    {{-- ========================================================= --}}
    {{-- LOGIN --}}
    {{-- ========================================================= --}}

    <p class="sv-auth-switch">

        Already have an account?

        <a href="{{ route('login') }}">
            Log In
        </a>

    </p>

    <script>
        (function () {
            const schools = @json($schools ?? []);
            const schoolSelect = document.getElementById('school');
            const majorSelect = document.getElementById('major');
            const oldMajor = @json(old('major'));

            function fillMajors(schoolName, selectedMajor) {
                const majors = schools[schoolName] || [];
                majorSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = majors.length
                    ? 'Pilih jurusan'
                    : 'Pilih sekolah terlebih dahulu';
                majorSelect.appendChild(placeholder);

                majors.forEach(function (major) {
                    const option = document.createElement('option');
                    option.value = major;
                    option.textContent = major;
                    if (selectedMajor && selectedMajor === major) {
                        option.selected = true;
                    }
                    majorSelect.appendChild(option);
                });

                majorSelect.disabled = majors.length === 0;
            }

            schoolSelect.addEventListener('change', function () {
                fillMajors(schoolSelect.value, '');
            });

            if (schoolSelect.value) {
                fillMajors(schoolSelect.value, oldMajor);
            }
        })();
    </script>

</x-guest-layout>