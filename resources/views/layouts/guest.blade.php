<!DOCTYPE html>

<html translate="no"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme="dark"
>

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="description"
        content="Sign in or create your SpeakVerse account."
    >

    <title>
        SpeakVerse
    </title>


    {{-- ========================================================= --}}
    {{-- THEME INITIALIZER --}}
    {{-- ========================================================= --}}

    <script>
        (function () {

            try {

                const savedTheme =
                    localStorage.getItem('speakverse-theme');

                if (
                    savedTheme === 'dark' ||
                    savedTheme === 'light'
                ) {

                    document.documentElement.setAttribute(
                        'data-theme',
                        savedTheme
                    );

                    return;
                }

                const prefersDark =
                    window.matchMedia(
                        '(prefers-color-scheme: dark)'
                    ).matches;

                document.documentElement.setAttribute(
                    'data-theme',
                    prefersDark ? 'dark' : 'light'
                );

            } catch (error) {

                document.documentElement.setAttribute(
                    'data-theme',
                    'dark'
                );

            }

        })();
    </script>


    {{-- ========================================================= --}}
    {{-- FONT --}}
    {{-- ========================================================= --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- ========================================================= --}}
    {{-- VITE --}}
    {{-- ========================================================= --}}

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])


    <style>

        /* =========================================================
           GLOBAL
        ========================================================= */

        :root {
            --sv-cyan: #06b6d4;
            --sv-cyan-bright: #22d3ee;
            --sv-blue: #2563eb;
            --sv-red: #ef4444;
            --sv-green: #10b981;
        }


        /* =========================================================
           DARK MODE
        ========================================================= */

        :root[data-theme="dark"] {
            color-scheme: dark;

            --sv-bg: #06101e;

            --sv-navbar:
                rgba(5, 12, 23, 0.88);

            --sv-surface: #0c192b;

            --sv-input: #0a1626;

            --sv-input-hover: #0c192b;

            --sv-border:
                rgba(148, 163, 184, 0.16);

            --sv-border-soft:
                rgba(148, 163, 184, 0.09);

            --sv-heading: #f8fafc;

            --sv-text: #cbd5e1;

            --sv-muted: #8292a8;

            --sv-placeholder: #53657c;

            --sv-divider:
                rgba(148, 163, 184, 0.13);

            --sv-google-bg:
                rgba(255, 255, 255, 0.025);

            --sv-google-hover:
                rgba(255, 255, 255, 0.055);

            --sv-error-bg:
                rgba(239, 68, 68, 0.08);

            --sv-error-border:
                rgba(239, 68, 68, 0.20);

            --sv-success-bg:
                rgba(16, 185, 129, 0.08);

            --sv-success-border:
                rgba(16, 185, 129, 0.20);

            --sv-shadow:
                0 30px 80px rgba(0, 0, 0, 0.32);

            --sv-page-bg:
                radial-gradient(
                    circle at 10% 15%,
                    rgba(34, 211, 238, 0.08),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 25%,
                    rgba(37, 99, 235, 0.08),
                    transparent 28%
                ),
                #06101e;
        }


        /* =========================================================
           LIGHT MODE
        ========================================================= */

        :root[data-theme="light"] {
            color-scheme: light;

            --sv-bg: #f8fafc;

            --sv-navbar:
                rgba(255, 255, 255, 0.90);

            --sv-surface: #ffffff;

            --sv-input: #f8fafc;

            --sv-input-hover: #ffffff;

            --sv-border:
                rgba(15, 23, 42, 0.12);

            --sv-border-soft:
                rgba(15, 23, 42, 0.07);

            --sv-heading: #0f172a;

            --sv-text: #475569;

            --sv-muted: #64748b;

            --sv-placeholder: #94a3b8;

            --sv-divider:
                rgba(15, 23, 42, 0.10);

            --sv-google-bg: #ffffff;

            --sv-google-hover: #f8fafc;

            --sv-error-bg:
                rgba(239, 68, 68, 0.055);

            --sv-error-border:
                rgba(239, 68, 68, 0.18);

            --sv-success-bg:
                rgba(16, 185, 129, 0.055);

            --sv-success-border:
                rgba(16, 185, 129, 0.18);

            --sv-shadow:
                0 25px 70px rgba(15, 23, 42, 0.10);

            --sv-page-bg:
                radial-gradient(
                    circle at 10% 15%,
                    rgba(6, 182, 212, 0.08),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 25%,
                    rgba(37, 99, 235, 0.06),
                    transparent 30%
                ),
                #f8fafc;
        }


        /* =========================================================
           RESET
        ========================================================= */

        * {
            box-sizing: border-box;
        }


        html {
            min-height: 100%;

            background:
                var(--sv-bg);
        }


        body {
            min-width: 320px;
            min-height: 100vh;

            margin: 0;

            font-family:
                "Outfit",
                sans-serif;

            color:
                var(--sv-text);

            background:
                var(--sv-page-bg);

            -webkit-font-smoothing:
                antialiased;
        }


        a {
            color: inherit;
            text-decoration: none;
        }


        button,
        input {
            font: inherit;
        }


        /* =========================================================
           PAGE
        ========================================================= */

        .sv-auth-page {
            min-height: 100vh;

            display: flex;
            flex-direction: column;
        }


        /* =========================================================
           NAVBAR
        ========================================================= */

        .sv-auth-navbar {
            height: 74px;

            flex-shrink: 0;

            border-bottom:
                1px solid
                var(--sv-border-soft);

            background:
                var(--sv-navbar);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);
        }


        .sv-auth-navbar-inner {
            width:
                min(
                    calc(100% - 48px),
                    1240px
                );

            height: 100%;

            margin-inline: auto;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;
        }


        /* =========================================================
           BRAND
        ========================================================= */

        .sv-auth-brand {
            display: inline-flex;
            align-items: center;

            gap: 11px;
        }


        .sv-auth-logo {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            border-radius: 13px;

            color: white;

            font-size: 20px;
            font-weight: 800;

            background:
                linear-gradient(
                    135deg,
                    #22d3ee,
                    #2563eb
                );

            box-shadow:
                0 10px 30px
                rgba(37, 99, 235, 0.17);
        }


        .sv-auth-brand-name {
            color:
                var(--sv-heading);

            font-size: 22px;
            font-weight: 700;

            letter-spacing:
                -0.6px;
        }


        .sv-auth-brand-name span {
            color:
                var(--sv-cyan);
        }


        /* =========================================================
           NAV ACTIONS
        ========================================================= */

        .sv-auth-nav-actions {
            display: flex;
            align-items: center;

            gap: 10px;
        }


        .sv-back-home {
            min-height: 42px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            padding:
                0 15px;

            border:
                1px solid transparent;

            border-radius: 11px;

            color:
                var(--sv-muted);

            font-size: 13px;
            font-weight: 600;

            transition:
                color 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease;
        }


        .sv-back-home:hover {
            color:
                var(--sv-heading);

            border-color:
                var(--sv-border);

            background:
                var(--sv-google-bg);
        }


        .sv-back-home svg {
            width: 17px;
            height: 17px;
        }


        /* =========================================================
           THEME BUTTON
        ========================================================= */

        .sv-theme-button {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            padding: 0;

            border:
                1px solid
                var(--sv-border);

            border-radius: 11px;

            color:
                var(--sv-muted);

            background:
                var(--sv-google-bg);

            cursor: pointer;

            transition:
                transform 0.2s ease,
                color 0.2s ease,
                border-color 0.2s ease,
                background 0.2s ease;
        }


        .sv-theme-button:hover {
            transform:
                translateY(-1px);

            color:
                var(--sv-heading);

            border-color:
                rgba(6, 182, 212, 0.35);

            background:
                var(--sv-google-hover);
        }


        .sv-theme-button svg {
            width: 19px;
            height: 19px;
        }


        .sv-icon-sun,
        .sv-icon-moon {
            display: none;
        }


        :root[data-theme="dark"]
        .sv-icon-sun {
            display: block;
        }


        :root[data-theme="light"]
        .sv-icon-moon {
            display: block;
        }


        /* =========================================================
           MAIN
        ========================================================= */

        .sv-auth-main {
            flex: 1;

            display: flex;
            align-items: center;
            justify-content: center;

            padding:
                52px 20px;
        }


        .sv-auth-wrapper {
            width: 100%;

            max-width: 500px;
        }


        /* =========================================================
           CARD
        ========================================================= */

        .sv-auth-card {
            width: 100%;

            padding:
                38px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 24px;

            background:
                var(--sv-surface);

            box-shadow:
                var(--sv-shadow);
        }


        /* =========================================================
           HEADINGS
        ========================================================= */

        .sv-auth-heading {
            margin-bottom: 29px;

            text-align: center;
        }


        .sv-auth-eyebrow {
            display: inline-flex;
            align-items: center;

            gap: 7px;

            margin-bottom: 13px;

            color:
                var(--sv-cyan);

            font-size: 10px;
            font-weight: 700;

            letter-spacing:
                1.25px;

            text-transform:
                uppercase;
        }


        .sv-auth-eyebrow-dot {
            width: 6px;
            height: 6px;

            border-radius:
                50%;

            background:
                var(--sv-cyan-bright);
        }


        .sv-auth-title {
            margin: 0;

            color:
                var(--sv-heading);

            font-size: 31px;
            font-weight: 700;

            line-height: 1.2;

            letter-spacing:
                -1px;
        }


        .sv-auth-description {
            max-width: 390px;

            margin:
                9px auto 0;

            color:
                var(--sv-muted);

            font-size: 14px;
            line-height: 1.55;
        }


        /* =========================================================
           GOOGLE BUTTON
        ========================================================= */

        .sv-google-button {
            width: 100%;

            min-height: 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 11px;

            padding:
                0 18px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 13px;

            color:
                var(--sv-heading);

            background:
                var(--sv-google-bg);

            font-size: 14px;
            font-weight: 600;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }


        .sv-google-button:hover {
            transform:
                translateY(-1px);

            border-color:
                rgba(6, 182, 212, 0.30);

            background:
                var(--sv-google-hover);

            box-shadow:
                0 8px 25px
                rgba(15, 23, 42, 0.06);
        }


        .sv-google-button svg {
            width: 20px;
            height: 20px;

            flex-shrink: 0;
        }


        /* =========================================================
           DIVIDER
        ========================================================= */

        .sv-divider {
            display: flex;
            align-items: center;

            gap: 14px;

            margin:
                24px 0;
        }


        .sv-divider::before,
        .sv-divider::after {
            content: "";

            height: 1px;

            flex: 1;

            background:
                var(--sv-divider);
        }


        .sv-divider span {
            color:
                var(--sv-muted);

            font-size: 11px;
            font-weight: 500;

            text-transform:
                uppercase;

            letter-spacing:
                1px;
        }


        /* =========================================================
           FORM
        ========================================================= */

        .sv-auth-form {
            display: grid;

            gap: 18px;
        }


        .sv-field {
            display: grid;

            gap: 8px;
        }


        .sv-label {
            color:
                var(--sv-text);

            font-size: 13px;
            font-weight: 600;
        }


        .sv-input-wrapper {
            position: relative;
        }


        .sv-input {
            width: 100%;

            min-height: 50px;

            padding:
                0 15px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 13px;

            outline: none;

            color:
                var(--sv-heading);

            background:
                var(--sv-input);

            font-size: 14px;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .sv-input::placeholder {
            color:
                var(--sv-placeholder);
        }


        .sv-input:hover {
            background:
                var(--sv-input-hover);
        }


        .sv-input:focus {
            border-color:
                rgba(6, 182, 212, 0.70);

            box-shadow:
                0 0 0 4px
                rgba(6, 182, 212, 0.10);

            background:
                var(--sv-input-hover);
        }


        .sv-input.sv-password-input {
            padding-right: 52px;
        }


        /* =========================================================
           PASSWORD TOGGLE
        ========================================================= */

        .sv-password-toggle {
            position: absolute;

            top: 50%;
            right: 9px;

            width: 36px;
            height: 36px;

            display: grid;
            place-items: center;

            transform:
                translateY(-50%);

            padding: 0;

            border: 0;
            border-radius: 9px;

            color:
                var(--sv-muted);

            background:
                transparent;

            cursor: pointer;
        }


        .sv-password-toggle:hover {
            color:
                var(--sv-heading);

            background:
                var(--sv-google-hover);
        }


        .sv-password-toggle svg {
            width: 18px;
            height: 18px;
        }


        /* =========================================================
           ERROR
        ========================================================= */

        .sv-field-error {
            margin: 0;

            color: #ef4444;

            font-size: 12px;
            line-height: 1.45;
        }


        .sv-alert {
            margin-bottom: 20px;

            padding:
                12px 14px;

            border-radius: 12px;

            font-size: 12px;
            line-height: 1.5;
        }


        .sv-alert-error {
            border:
                1px solid
                var(--sv-error-border);

            color:
                #ef4444;

            background:
                var(--sv-error-bg);
        }


        .sv-alert-success {
            border:
                1px solid
                var(--sv-success-border);

            color:
                var(--sv-green);

            background:
                var(--sv-success-bg);
        }


        /* =========================================================
           FORM EXTRAS
        ========================================================= */

        .sv-form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;
        }


        .sv-checkbox-label {
            display: inline-flex;
            align-items: center;

            gap: 9px;

            color:
                var(--sv-muted);

            font-size: 12px;

            cursor: pointer;
        }


        .sv-checkbox {
            width: 17px;
            height: 17px;

            accent-color:
                var(--sv-cyan);
        }


        .sv-link {
            color:
                var(--sv-cyan);

            font-size: 12px;
            font-weight: 600;
        }


        .sv-link:hover {
            text-decoration:
                underline;
        }


        /* =========================================================
           PRIMARY BUTTON
        ========================================================= */

        .sv-submit-button {
            width: 100%;

            min-height: 50px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border: 0;

            border-radius: 13px;

            color: #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            box-shadow:
                0 13px 30px
                rgba(37, 99, 235, 0.18);

            font-size: 14px;
            font-weight: 700;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .sv-submit-button:hover {
            transform:
                translateY(-1px);

            box-shadow:
                0 17px 38px
                rgba(37, 99, 235, 0.25);
        }


        /* =========================================================
           AUTH SWITCH
        ========================================================= */

        .sv-auth-switch {
            margin:
                24px 0 0;

            text-align: center;

            color:
                var(--sv-muted);

            font-size: 13px;
        }


        .sv-auth-switch a {
            color:
                var(--sv-cyan);

            font-weight: 600;
        }


        .sv-auth-switch a:hover {
            text-decoration:
                underline;
        }


        /* =========================================================
           FOOTER NOTE
        ========================================================= */

        .sv-auth-footer {
            margin-top: 18px;

            text-align: center;

            color:
                var(--sv-muted);

            font-size: 11px;
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 640px) {

            .sv-auth-navbar {
                height: 68px;
            }


            .sv-auth-navbar-inner {
                width:
                    calc(100% - 30px);
            }


            .sv-auth-logo {
                width: 39px;
                height: 39px;

                border-radius: 12px;

                font-size: 18px;
            }


            .sv-auth-brand-name {
                font-size: 19px;
            }


            .sv-back-home span {
                display: none;
            }


            .sv-back-home {
                width: 42px;

                padding: 0;

                border-color:
                    var(--sv-border);
            }


            .sv-auth-main {
                align-items:
                    flex-start;

                padding:
                    32px 15px;
            }


            .sv-auth-card {
                padding:
                    28px 21px;

                border-radius:
                    20px;
            }


            .sv-auth-title {
                font-size: 27px;
            }


            .sv-form-row {
                align-items:
                    flex-start;

                flex-direction:
                    column;
            }

        }

    </style>

    <meta name="google" content="notranslate">
</head>


<body>

    <div class="sv-auth-page">


        {{-- ========================================================= --}}
        {{-- NAVBAR --}}
        {{-- ========================================================= --}}

        <header class="sv-auth-navbar">

            <div class="sv-auth-navbar-inner">


                {{-- BRAND --}}
                <a
                    href="{{ route('home') }}"
                    class="sv-auth-brand"
                >

                    <span class="sv-auth-logo">
                        S
                    </span>

                    <span class="sv-auth-brand-name">
                        Speak<span>Verse</span>
                    </span>

                </a>


                {{-- ACTIONS --}}
                <div class="sv-auth-nav-actions">


                    {{-- BACK HOME --}}
                    <a
                        href="{{ route('home') }}"
                        class="sv-back-home"
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
                                d="M15 18l-6-6 6-6"
                            />
                        </svg>

                        <span>
                            Back to Home
                        </span>

                    </a>


                    {{-- THEME --}}
                    <button
                        id="themeToggle"
                        type="button"
                        class="sv-theme-button"
                        aria-label="Change appearance"
                    >

                        {{-- SUN --}}
                        <svg
                            class="sv-icon-sun"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="4"
                            />

                            <path
                                stroke-linecap="round"
                                d="
                                    M12 2v2
                                    M12 20v2
                                    M4.93 4.93l1.42 1.42
                                    M17.66 17.66l1.41 1.41
                                    M2 12h2
                                    M20 12h2
                                    M4.93 19.07l1.42-1.42
                                    M17.66 6.34l1.41-1.41
                                "
                            />
                        </svg>


                        {{-- MOON --}}
                        <svg
                            class="sv-icon-moon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="
                                    M21 12.79
                                    A9 9 0 1 1
                                    11.21 3
                                    7 7 0 0 0
                                    21 12.79Z
                                "
                            />
                        </svg>

                    </button>

                </div>

            </div>

        </header>


        {{-- ========================================================= --}}
        {{-- CONTENT --}}
        {{-- ========================================================= --}}

        <main class="sv-auth-main">

            <div class="sv-auth-wrapper">

                <div class="sv-auth-card">

                    {{ $slot }}

                </div>


                <p class="sv-auth-footer">
                    SpeakVerse · Interactive English Learning Platform
                </p>

            </div>

        </main>

    </div>


    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {


                /* =================================================
                   THEME
                ================================================= */

                const themeToggle =
                    document.getElementById(
                        'themeToggle'
                    );


                function getTheme() {

                    return document.documentElement
                        .getAttribute('data-theme') === 'light'
                            ? 'light'
                            : 'dark';

                }


                function setTheme(theme) {

                    document.documentElement
                        .setAttribute(
                            'data-theme',
                            theme
                        );


                    try {

                        localStorage.setItem(
                            'speakverse-theme',
                            theme
                        );

                    } catch (error) {
                        //
                    }


                    if (themeToggle) {

                        themeToggle.setAttribute(
                            'title',
                            theme === 'dark'
                                ? 'Light mode'
                                : 'Dark mode'
                        );

                    }

                }


                setTheme(
                    getTheme()
                );


                if (themeToggle) {

                    themeToggle.addEventListener(
                        'click',
                        function () {

                            setTheme(
                                getTheme() === 'dark'
                                    ? 'light'
                                    : 'dark'
                            );

                        }
                    );

                }


                /* =================================================
                   PASSWORD VISIBILITY
                ================================================= */

                const passwordButtons =
                    document.querySelectorAll(
                        '[data-password-toggle]'
                    );


                passwordButtons.forEach(
                    function (button) {

                        button.addEventListener(
                            'click',
                            function () {

                                const targetId =
                                    button.getAttribute(
                                        'data-password-toggle'
                                    );


                                const input =
                                    document.getElementById(
                                        targetId
                                    );


                                if (!input) {
                                    return;
                                }


                                input.type =
                                    input.type === 'password'
                                        ? 'text'
                                        : 'password';


                                button.setAttribute(
                                    'aria-label',
                                    input.type === 'password'
                                        ? 'Show password'
                                        : 'Hide password'
                                );

                            }
                        );

                    }
                );

            }
        );

    </script>

</body>

</html>