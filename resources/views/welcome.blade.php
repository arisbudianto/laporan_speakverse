<!DOCTYPE html>
<html
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
        content="SpeakVerse is an interactive English learning platform for listening, reading, writing, and speaking."
    >

    <title>SpeakVerse — Learn English Smarter</title>


    {{-- ========================================================= --}}
    {{-- THEME INITIALIZER --}}
    {{-- ========================================================= --}}
    {{-- Dijalankan sebelum halaman dirender untuk mencegah flash --}}
    {{-- warna putih / gelap ketika halaman dibuka.              --}}
    {{-- ========================================================= --}}

    <script>
        (function () {
            try {
                const savedTheme = localStorage.getItem('speakverse-theme');

                if (savedTheme === 'light' || savedTheme === 'dark') {
                    document.documentElement.setAttribute(
                        'data-theme',
                        savedTheme
                    );

                    return;
                }

                const prefersDark = window.matchMedia(
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
           THEME VARIABLES
        ========================================================= */

        :root {
            --sv-container: 1240px;

            --sv-cyan: #06b6d4;
            --sv-cyan-bright: #22d3ee;
            --sv-blue: #2563eb;

            --sv-green: #10b981;
            --sv-violet: #8b5cf6;
            --sv-rose: #f43f5e;
        }


        /* =========================================================
           DARK MODE
        ========================================================= */

        :root[data-theme="dark"] {
            color-scheme: dark;

            --sv-bg: #06101e;
            --sv-bg-secondary: #081421;
            --sv-navbar: rgba(5, 12, 23, 0.92);

            --sv-surface: #0c192b;
            --sv-surface-secondary: #0f1d30;
            --sv-surface-subtle: rgba(255, 255, 255, 0.025);

            --sv-border: rgba(148, 163, 184, 0.14);
            --sv-border-soft: rgba(148, 163, 184, 0.08);

            --sv-heading: #f8fafc;
            --sv-text: #cbd5e1;
            --sv-muted: #8292a8;
            --sv-muted-dark: #63758d;

            --sv-button-secondary-bg: rgba(255, 255, 255, 0.035);
            --sv-button-secondary-hover: rgba(255, 255, 255, 0.065);

            --sv-progress-bg: #17243a;

            --sv-shadow:
                0 30px 70px rgba(0, 0, 0, 0.28);

            --sv-card-shadow:
                0 15px 40px rgba(0, 0, 0, 0.12);

            --sv-body-background:
                radial-gradient(
                    circle at 0% 10%,
                    rgba(34, 211, 238, 0.07),
                    transparent 27%
                ),
                radial-gradient(
                    circle at 100% 20%,
                    rgba(37, 99, 235, 0.06),
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
            --sv-bg-secondary: #f1f5f9;
            --sv-navbar: rgba(255, 255, 255, 0.92);

            --sv-surface: #ffffff;
            --sv-surface-secondary: #f8fafc;
            --sv-surface-subtle: rgba(15, 23, 42, 0.025);

            --sv-border: rgba(15, 23, 42, 0.11);
            --sv-border-soft: rgba(15, 23, 42, 0.07);

            --sv-heading: #0f172a;
            --sv-text: #475569;
            --sv-muted: #64748b;
            --sv-muted-dark: #94a3b8;

            --sv-button-secondary-bg: #ffffff;
            --sv-button-secondary-hover: #f1f5f9;

            --sv-progress-bg: #e2e8f0;

            --sv-shadow:
                0 28px 65px rgba(15, 23, 42, 0.10);

            --sv-card-shadow:
                0 12px 35px rgba(15, 23, 42, 0.05);

            --sv-body-background:
                radial-gradient(
                    circle at 0% 10%,
                    rgba(6, 182, 212, 0.08),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 100% 20%,
                    rgba(37, 99, 235, 0.06),
                    transparent 30%
                ),
                #f8fafc;
        }


        /* =========================================================
           BASE
        ========================================================= */

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            margin: 0;

            min-width: 320px;

            font-family: "Outfit", sans-serif;

            color: var(--sv-text);

            background: var(--sv-body-background);

            -webkit-font-smoothing: antialiased;

            transition:
                background-color 0.25s ease,
                color 0.25s ease;
        }


        body.sv-menu-open {
            overflow: hidden;
        }


        a {
            color: inherit;
            text-decoration: none;
        }


        button {
            font: inherit;
        }


        .sv-page {
            min-height: 100vh;
        }


        .sv-container {
            width: min(
                calc(100% - 48px),
                var(--sv-container)
            );

            margin-inline: auto;
        }


        /* =========================================================
           NAVBAR
        ========================================================= */

        .sv-navbar {
            position: sticky;

            top: 0;

            z-index: 100;

            height: 74px;

            border-bottom:
                1px solid var(--sv-border-soft);

            background: var(--sv-navbar);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            transition:
                background 0.25s ease,
                border-color 0.25s ease;
        }


        .sv-navbar-inner {
            height: 100%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 30px;
        }


        /* =========================================================
           BRAND
        ========================================================= */

        .sv-brand {
            display: inline-flex;
            align-items: center;

            gap: 11px;

            flex-shrink: 0;
        }


        .sv-brand-logo {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            border-radius: 13px;

            color: #ffffff;

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
                rgba(37, 99, 235, 0.18);
        }


        .sv-brand-name {
            color: var(--sv-heading);

            font-size: 22px;
            font-weight: 700;

            letter-spacing: -0.6px;

            transition:
                color 0.25s ease;
        }


        .sv-brand-name span {
            color: var(--sv-cyan);
        }


        /* =========================================================
           DESKTOP NAVIGATION
        ========================================================= */

        .sv-nav-menu {
            display: flex;
            align-items: center;

            gap: 36px;

            margin-left: auto;
            margin-right: auto;
        }


        .sv-nav-menu a {
            color: var(--sv-muted);

            font-size: 14px;
            font-weight: 500;

            transition:
                color 0.2s ease;
        }


        .sv-nav-menu a:hover {
            color: var(--sv-heading);
        }


        .sv-navbar-actions {
            display: flex;
            align-items: center;

            gap: 8px;

            flex-shrink: 0;
        }


        /* =========================================================
           LOGIN LINK
        ========================================================= */

        .sv-login-link {
            min-height: 42px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding:
                0 17px;

            border:
                1px solid transparent;

            border-radius: 11px;

            color: var(--sv-text);

            font-size: 14px;
            font-weight: 600;

            transition:
                color 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease;
        }


        .sv-login-link:hover {
            color: var(--sv-heading);

            border-color: var(--sv-border);

            background:
                var(--sv-surface-subtle);
        }


        /* =========================================================
           DASHBOARD LINK
        ========================================================= */

        .sv-dashboard-link {
            min-height: 42px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding:
                0 18px;

            border-radius: 11px;

            color: #ffffff;

            font-size: 14px;
            font-weight: 600;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            box-shadow:
                0 10px 28px
                rgba(37, 99, 235, 0.17);
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
                1px solid var(--sv-border);

            border-radius: 11px;

            color: var(--sv-muted);

            background:
                var(--sv-surface-subtle);

            cursor: pointer;

            transition:
                color 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
        }


        .sv-theme-button:hover {
            color: var(--sv-heading);

            border-color:
                rgba(6, 182, 212, 0.35);

            background:
                var(--sv-surface-secondary);

            transform:
                translateY(-1px);
        }


        .sv-theme-button svg {
            width: 19px;
            height: 19px;
        }


        .sv-theme-button .sv-icon-sun {
            display: none;
        }


        :root[data-theme="dark"]
        .sv-theme-button .sv-icon-sun {
            display: block;
        }


        :root[data-theme="dark"]
        .sv-theme-button .sv-icon-moon {
            display: none;
        }


        :root[data-theme="light"]
        .sv-theme-button .sv-icon-sun {
            display: none;
        }


        :root[data-theme="light"]
        .sv-theme-button .sv-icon-moon {
            display: block;
        }


        /* =========================================================
           MOBILE MENU BUTTON
        ========================================================= */

        .sv-mobile-button {
            width: 42px;
            height: 42px;

            display: none;
            place-items: center;

            padding: 0;

            border:
                1px solid var(--sv-border);

            border-radius: 11px;

            color: var(--sv-heading);

            background:
                var(--sv-surface-subtle);

            cursor: pointer;
        }


        .sv-mobile-button svg {
            width: 20px;
            height: 20px;
        }


        .sv-mobile-menu {
            display: none;
        }


        /* =========================================================
           HERO
        ========================================================= */

        .sv-hero {
            padding:
                70px 0
                74px;
        }


        .sv-hero-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 0.95fr)
                minmax(500px, 1.05fr);

            align-items: center;

            gap: 70px;
        }


        .sv-hero-content {
            min-width: 0;
        }


        .sv-hero-badge {
            width: fit-content;

            display: inline-flex;
            align-items: center;

            gap: 8px;

            margin-bottom: 23px;

            padding:
                8px 13px;

            border:
                1px solid
                rgba(6, 182, 212, 0.22);

            border-radius: 999px;

            color: var(--sv-cyan);

            background:
                rgba(6, 182, 212, 0.07);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: 1.25px;

            text-transform: uppercase;
        }


        .sv-hero-badge-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: var(--sv-cyan-bright);

            box-shadow:
                0 0 12px
                rgba(34, 211, 238, 0.65);
        }


        .sv-hero-title {
            margin: 0;

            max-width: 680px;

            color: var(--sv-heading);

            font-size:
                clamp(
                    50px,
                    5vw,
                    72px
                );

            line-height: 1.03;

            letter-spacing: -3px;

            font-weight: 800;

            transition:
                color 0.25s ease;
        }


        .sv-hero-title span {
            display: block;

            margin-top: 5px;

            background:
                linear-gradient(
                    100deg,
                    #22d3ee,
                    #06b6d4 45%,
                    #3b82f6
                );

            background-clip: text;
            -webkit-background-clip: text;

            color: transparent;
            -webkit-text-fill-color: transparent;
        }


        .sv-hero-description {
            max-width: 610px;

            margin:
                25px 0 0;

            color: var(--sv-muted);

            font-size: 17px;
            line-height: 1.7;
        }


        /* =========================================================
           PRIMARY CTA
        ========================================================= */

        .sv-hero-action {
            margin-top: 32px;
        }


        .sv-primary-button {
            min-height: 50px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 10px;

            padding:
                0 25px;

            border-radius: 13px;

            color: #ffffff;

            font-size: 15px;
            font-weight: 600;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            box-shadow:
                0 14px 35px
                rgba(37, 99, 235, 0.20);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .sv-primary-button:hover {
            transform:
                translateY(-2px);

            box-shadow:
                0 18px 42px
                rgba(37, 99, 235, 0.27);
        }


        .sv-primary-button svg {
            width: 18px;
            height: 18px;
        }


        /* =========================================================
           HERO BENEFITS
        ========================================================= */

        .sv-hero-notes {
            display: flex;
            align-items: center;

            flex-wrap: wrap;

            gap:
                9px 22px;

            margin-top: 26px;
        }


        .sv-hero-note {
            display: flex;
            align-items: center;

            gap: 7px;

            color: var(--sv-muted);

            font-size: 12px;
            font-weight: 500;
        }


        .sv-note-check {
            width: 18px;
            height: 18px;

            display: grid;
            place-items: center;

            border-radius: 50%;

            color: var(--sv-cyan);

            background:
                rgba(6, 182, 212, 0.09);

            font-size: 10px;
        }


        /* =========================================================
           APPLICATION PREVIEW
        ========================================================= */

        .sv-preview-area {
            position: relative;

            min-width: 0;
        }


        .sv-preview-area::before {
            content: "";

            position: absolute;

            inset:
                15% 10%;

            border-radius: 50%;

            background:
                rgba(37, 99, 235, 0.12);

            filter: blur(80px);

            pointer-events: none;
        }


        :root[data-theme="light"]
        .sv-preview-area::before {
            background:
                rgba(6, 182, 212, 0.10);
        }


        .sv-preview-card {
            position: relative;

            overflow: hidden;

            border:
                1px solid var(--sv-border);

            border-radius: 24px;

            background:
                var(--sv-surface);

            box-shadow:
                var(--sv-shadow);

            transition:
                background 0.25s ease,
                border-color 0.25s ease,
                box-shadow 0.25s ease;
        }


        .sv-preview-top {
            height: 61px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding:
                0 20px;

            border-bottom:
                1px solid var(--sv-border-soft);
        }


        .sv-preview-brand {
            display: flex;
            align-items: center;

            gap: 10px;
        }


        .sv-preview-logo {
            width: 34px;
            height: 34px;

            display: grid;
            place-items: center;

            border-radius: 10px;

            color: #ffffff;

            font-size: 12px;
            font-weight: 800;

            background:
                linear-gradient(
                    135deg,
                    #22d3ee,
                    #2563eb
                );
        }


        .sv-preview-brand strong {
            display: block;

            color: var(--sv-heading);

            font-size: 12px;
        }


        .sv-preview-brand small {
            display: block;

            margin-top: 2px;

            color: var(--sv-muted-dark);

            font-size: 9px;
        }


        .sv-student-pill {
            padding:
                5px 10px;

            border:
                1px solid var(--sv-border);

            border-radius: 999px;

            color: var(--sv-muted);

            background:
                var(--sv-surface-subtle);

            font-size: 9px;
        }


        .sv-preview-body {
            padding: 20px;
        }


        /* =========================================================
           MISSION PREVIEW
        ========================================================= */

        .sv-mission {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 20px;
        }


        .sv-mission-label {
            margin: 0;

            color: var(--sv-cyan);

            font-size: 9px;
            font-weight: 700;

            letter-spacing: 1.2px;

            text-transform: uppercase;
        }


        .sv-mission-title {
            margin:
                6px 0 0;

            color: var(--sv-heading);

            font-size: 17px;
            font-weight: 700;
        }


        .sv-mission-text {
            margin:
                4px 0 0;

            color: var(--sv-muted);

            font-size: 10px;
        }


        .sv-status {
            flex-shrink: 0;

            padding:
                6px 9px;

            border:
                1px solid
                rgba(16, 185, 129, 0.13);

            border-radius: 9px;

            color: #10b981;

            background:
                rgba(16, 185, 129, 0.08);

            font-size: 9px;
            font-weight: 600;
        }


        /* =========================================================
           PROGRESS PREVIEW
        ========================================================= */

        .sv-progress {
            margin-top: 18px;

            padding: 15px;

            border:
                1px solid var(--sv-border-soft);

            border-radius: 14px;

            background:
                var(--sv-surface-subtle);
        }


        .sv-progress-info {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 9px;

            color: var(--sv-text);

            font-size: 10px;
        }


        .sv-progress-info strong {
            color: var(--sv-cyan);
        }


        .sv-progress-track {
            height: 6px;

            overflow: hidden;

            border-radius: 999px;

            background:
                var(--sv-progress-bg);
        }


        .sv-progress-value {
            width: 75%;
            height: 100%;

            border-radius: inherit;

            background:
                linear-gradient(
                    90deg,
                    #22d3ee,
                    #3b82f6
                );
        }


        /* =========================================================
           PREVIEW SKILLS
        ========================================================= */

        .sv-preview-skills {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 9px;

            margin-top: 12px;
        }


        .sv-preview-skill {
            padding: 12px;

            border:
                1px solid var(--sv-border-soft);

            border-radius: 13px;

            background:
                var(--sv-surface-subtle);
        }


        .sv-skill-icon {
            width: 31px;
            height: 31px;

            display: grid;
            place-items: center;

            border-radius: 9px;
        }


        .sv-skill-icon svg {
            width: 15px;
            height: 15px;
        }


        .sv-skill-icon.cyan {
            color: #06b6d4;

            background:
                rgba(6, 182, 212, 0.10);
        }


        .sv-skill-icon.blue {
            color: #3b82f6;

            background:
                rgba(59, 130, 246, 0.10);
        }


        .sv-skill-icon.violet {
            color: #8b5cf6;

            background:
                rgba(139, 92, 246, 0.10);
        }


        .sv-skill-icon.rose {
            color: #f43f5e;

            background:
                rgba(244, 63, 94, 0.10);
        }


        .sv-preview-skill-name {
            margin:
                8px 0 0;

            color: var(--sv-text);

            font-size: 9px;
            font-weight: 600;
        }


        .sv-preview-skill-score {
            margin:
                2px 0 0;

            color: var(--sv-muted);

            font-size: 9px;
        }


        /* =========================================================
           AI PREVIEW
        ========================================================= */

        .sv-ai-row {
            display: flex;
            align-items: center;

            gap: 11px;

            margin-top: 12px;

            padding: 12px;

            border:
                1px solid
                rgba(6, 182, 212, 0.12);

            border-radius: 13px;

            background:
                rgba(6, 182, 212, 0.035);
        }


        .sv-ai-icon {
            width: 34px;
            height: 34px;

            display: grid;
            place-items: center;

            flex-shrink: 0;

            border-radius: 10px;

            color: var(--sv-cyan);

            background:
                rgba(6, 182, 212, 0.10);
        }


        .sv-ai-icon svg {
            width: 16px;
            height: 16px;
        }


        .sv-ai-row strong {
            display: block;

            color: var(--sv-heading);

            font-size: 10px;
        }


        .sv-ai-row span {
            display: block;

            margin-top: 2px;

            color: var(--sv-muted);

            font-size: 9px;
        }


        /* =========================================================
           COMMON SECTION
        ========================================================= */

        .sv-section {
            padding:
                60px 0;
        }


        .sv-section-border {
            border-top:
                1px solid var(--sv-border-soft);

            border-bottom:
                1px solid var(--sv-border-soft);

            background:
                var(--sv-surface-subtle);
        }


        .sv-section-heading {
            max-width: 620px;

            margin:
                0 auto 34px;

            text-align: center;
        }


        .sv-section-label {
            margin: 0;

            color: var(--sv-cyan);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: 1.5px;

            text-transform: uppercase;
        }


        .sv-section-title {
            margin:
                9px 0 0;

            color: var(--sv-heading);

            font-size:
                clamp(
                    27px,
                    3vw,
                    36px
                );

            line-height: 1.15;

            letter-spacing: -1.3px;

            font-weight: 700;
        }


        .sv-section-description {
            margin:
                11px auto 0;

            color: var(--sv-muted);

            font-size: 13px;
            line-height: 1.6;
        }


        /* =========================================================
           FEATURES
        ========================================================= */

        .sv-features {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 14px;
        }


        .sv-feature-card {
            padding: 20px;

            border:
                1px solid var(--sv-border);

            border-radius: 17px;

            background:
                var(--sv-surface);

            box-shadow:
                var(--sv-card-shadow);

            transition:
                transform 0.2s ease,
                border-color 0.2s ease,
                background 0.25s ease,
                box-shadow 0.25s ease;
        }


        .sv-feature-card:hover {
            transform:
                translateY(-3px);

            border-color:
                rgba(6, 182, 212, 0.35);
        }


        .sv-feature-icon {
            width: 40px;
            height: 40px;

            display: grid;
            place-items: center;

            border-radius: 12px;
        }


        .sv-feature-icon svg {
            width: 19px;
            height: 19px;
        }


        .sv-feature-icon.cyan {
            color: #06b6d4;

            background:
                rgba(6, 182, 212, 0.10);
        }


        .sv-feature-icon.blue {
            color: #3b82f6;

            background:
                rgba(59, 130, 246, 0.10);
        }


        .sv-feature-icon.violet {
            color: #8b5cf6;

            background:
                rgba(139, 92, 246, 0.10);
        }


        .sv-feature-icon.rose {
            color: #f43f5e;

            background:
                rgba(244, 63, 94, 0.10);
        }


        .sv-feature-card h3 {
            margin:
                14px 0 0;

            color: var(--sv-heading);

            font-size: 16px;
            font-weight: 600;
        }


        .sv-feature-card p {
            margin:
                7px 0 0;

            color: var(--sv-muted);

            font-size: 12px;
            line-height: 1.6;
        }


        /* =========================================================
           HOW IT WORKS
        ========================================================= */

        .sv-steps {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            overflow: hidden;

            border:
                1px solid var(--sv-border);

            border-radius: 18px;

            background:
                var(--sv-surface);

            box-shadow:
                var(--sv-card-shadow);

            transition:
                background 0.25s ease,
                border-color 0.25s ease;
        }


        .sv-step {
            padding:
                23px 25px;
        }


        .sv-step + .sv-step {
            border-left:
                1px solid var(--sv-border);
        }


        .sv-step-header {
            display: flex;
            align-items: center;

            gap: 12px;
        }


        .sv-step-number {
            width: 36px;
            height: 36px;

            display: grid;
            place-items: center;

            flex-shrink: 0;

            border-radius: 10px;

            color: #06101e;

            font-size: 10px;
            font-weight: 800;
        }


        .sv-step-number.one {
            background: #67e8f9;
        }


        .sv-step-number.two {
            background: #93c5fd;
        }


        .sv-step-number.three {
            background: #c4b5fd;
        }


        .sv-step h3 {
            margin: 0;

            color: var(--sv-heading);

            font-size: 15px;
            font-weight: 600;
        }


        .sv-step p {
            margin:
                10px 0 0;

            color: var(--sv-muted);

            font-size: 12px;
            line-height: 1.6;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .sv-footer {
            border-top:
                1px solid var(--sv-border-soft);

            background:
                var(--sv-surface-subtle);
        }


        .sv-footer-inner {
            min-height: 72px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;
        }


        .sv-footer .sv-brand-logo {
            width: 32px;
            height: 32px;

            border-radius: 9px;

            font-size: 14px;
        }


        .sv-footer .sv-brand-name {
            font-size: 16px;
        }


        .sv-footer-copy {
            color: var(--sv-muted);

            font-size: 11px;
        }


        /* =========================================================
           TABLET
        ========================================================= */

        @media (max-width: 1050px) {

            .sv-hero-layout {
                grid-template-columns:
                    minmax(0, 0.9fr)
                    minmax(430px, 1.1fr);

                gap: 42px;
            }


            .sv-features {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        /* =========================================================
           SMALL TABLET
        ========================================================= */

        @media (max-width: 850px) {

            .sv-nav-menu {
                display: none;
            }


            .sv-hero-layout {
                grid-template-columns: 1fr;

                gap: 45px;
            }


            .sv-hero-content {
                max-width: 720px;

                margin-inline: auto;

                text-align: center;
            }


            .sv-hero-badge {
                margin-left: auto;
                margin-right: auto;
            }


            .sv-hero-description {
                margin-left: auto;
                margin-right: auto;
            }


            .sv-hero-action {
                display: flex;
                justify-content: center;
            }


            .sv-hero-notes {
                justify-content: center;
            }


            .sv-preview-area {
                width: min(
                    100%,
                    680px
                );

                margin-inline: auto;
            }

        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 680px) {

            .sv-container {
                width: min(
                    calc(100% - 30px),
                    var(--sv-container)
                );
            }


            .sv-navbar {
                height: 68px;
            }


            .sv-nav-menu {
                display: none;
            }


            .sv-login-link,
            .sv-dashboard-link {
                display: none;
            }


            .sv-mobile-button {
                display: grid;
            }


            .sv-navbar-actions {
                margin-left: auto;
            }


            .sv-mobile-menu {
                position: fixed;

                top: 68px;
                left: 0;
                right: 0;

                z-index: 99;

                display: none;

                padding:
                    14px 0 18px;

                border-bottom:
                    1px solid var(--sv-border);

                background:
                    var(--sv-navbar);

                box-shadow:
                    0 20px 50px
                    rgba(0, 0, 0, 0.18);

                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
            }


            .sv-mobile-menu.open {
                display: block;
            }


            .sv-mobile-menu-content {
                display: grid;

                gap: 5px;
            }


            .sv-mobile-link {
                padding:
                    11px 13px;

                border-radius: 10px;

                color: var(--sv-text);

                font-size: 13px;
                font-weight: 500;
            }


            .sv-mobile-link:hover {
                color: var(--sv-heading);

                background:
                    var(--sv-surface-subtle);
            }


            .sv-mobile-divider {
                height: 1px;

                margin:
                    5px 0;

                background:
                    var(--sv-border);
            }


            .sv-mobile-auth {
                min-height: 44px;

                display: flex;
                align-items: center;
                justify-content: center;

                border:
                    1px solid var(--sv-border);

                border-radius: 11px;

                color: var(--sv-heading);

                background:
                    var(--sv-surface);

                font-size: 13px;
                font-weight: 600;
            }


            .sv-hero {
                padding:
                    50px 0
                    54px;
            }


            .sv-hero-title {
                font-size:
                    clamp(
                        41px,
                        12vw,
                        55px
                    );

                letter-spacing: -2px;
            }


            .sv-hero-description {
                font-size: 15px;
            }


            .sv-primary-button {
                width: 100%;
            }


            .sv-hero-notes {
                width: fit-content;

                display: grid;

                margin-left: auto;
                margin-right: auto;

                text-align: left;
            }


            .sv-preview-skills {
                grid-template-columns:
                    repeat(2, 1fr);
            }


            .sv-section {
                padding:
                    48px 0;
            }


            .sv-features {
                grid-template-columns: 1fr;
            }


            .sv-steps {
                grid-template-columns: 1fr;
            }


            .sv-step + .sv-step {
                border-left: 0;

                border-top:
                    1px solid var(--sv-border);
            }


            .sv-footer-inner {
                min-height: auto;

                padding:
                    22px 0;

                flex-direction: column;
                align-items: flex-start;
            }

        }


        /* =========================================================
           SMALL MOBILE
        ========================================================= */

        @media (max-width: 430px) {

            .sv-brand-name {
                font-size: 19px;
            }


            .sv-brand-logo {
                width: 39px;
                height: 39px;
            }


            .sv-mission {
                flex-direction: column;
            }


            .sv-preview-body {
                padding: 16px;
            }

        }

        /* =========================================================
           LANDING PAGE VIDEO
        ========================================================= */

        .sv-video-section {
            position: relative;
            padding: 20px 0 90px;
            overflow: hidden;
        }

        .sv-video-section::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 300px;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 50%;
            background: rgba(6, 182, 212, 0.10);
            filter: blur(90px);
            pointer-events: none;
        }

        .sv-video-heading {
            position: relative;
            max-width: 700px;
            margin: 0 auto 30px;
            text-align: center;
        }

        .sv-video-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            margin-bottom: 12px;
            padding: 7px 13px;

            border: 1px solid rgba(6, 182, 212, 0.22);
            border-radius: 999px;

            color: var(--sv-cyan);

            background: rgba(6, 182, 212, 0.07);

            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .sv-video-label-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: var(--sv-cyan-bright);

            box-shadow:
                0 0 12px rgba(34, 211, 238, 0.65);
        }

        .sv-video-title {
            margin: 0;

            color: var(--sv-heading);

            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.1;
            letter-spacing: -1.8px;
            font-weight: 800;
        }

        .sv-video-title span {
            background:
                linear-gradient(
                    100deg,
                    #22d3ee,
                    #06b6d4 45%,
                    #3b82f6
                );

            background-clip: text;
            -webkit-background-clip: text;

            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        .sv-video-description {
            max-width: 600px;

            margin: 14px auto 0;

            color: var(--sv-muted);

            font-size: 15px;
            line-height: 1.7;
        }

        .sv-video-wrapper {
            position: relative;

            width: min(
                calc(100% - 48px),
                1050px
            );

            margin: 0 auto;

            padding: 8px;

            border:
                1px solid
                var(--sv-border);

            border-radius: 30px;

            background:
                linear-gradient(
                    135deg,
                    rgba(34, 211, 238, 0.10),
                    rgba(37, 99, 235, 0.08)
                );

            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.16);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                border-color 0.25s ease;
        }

        .sv-video-wrapper:hover {
            transform: translateY(-3px);

            border-color:
                rgba(6, 182, 212, 0.30);

            box-shadow:
                0 35px 90px rgba(37, 99, 235, 0.16);
        }

        .sv-video-frame {
            position: relative;

            overflow: hidden;

            aspect-ratio: 16 / 9;

            border-radius: 23px;

            background:
                #020817;
        }

        .sv-video-frame video {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: cover;
        }

        .sv-video-caption {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            padding: 16px 18px 8px;
        }

        .sv-video-caption-text {
            color: var(--sv-muted);

            font-size: 12px;
            line-height: 1.5;
        }

        .sv-video-caption-text strong {
            display: block;

            margin-bottom: 2px;

            color: var(--sv-heading);

            font-size: 13px;
        }

        .sv-video-play-badge {
            flex-shrink: 0;

            width: 36px;
            height: 36px;

            display: grid;
            place-items: center;

            border-radius: 50%;

            color: #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #2563eb
                );

            box-shadow:
                0 8px 22px rgba(37, 99, 235, 0.25);
        }

        .sv-video-play-badge svg {
            width: 16px;
            height: 16px;
        }

        /* LIGHT MODE */
        :root[data-theme="light"] .sv-video-wrapper {
            background:
                linear-gradient(
                    135deg,
                    rgba(6, 182, 212, 0.08),
                    rgba(37, 99, 235, 0.06)
                );

            box-shadow:
                0 25px 65px rgba(15, 23, 42, 0.10);
        }

        /* MOBILE */
        @media (max-width: 680px) {

            .sv-video-section {
                padding:
                    10px 0
                    60px;
            }

            .sv-video-heading {
                margin-bottom: 22px;
            }

            .sv-video-title {
                font-size: 30px;
                letter-spacing: -1px;
            }

            .sv-video-description {
                font-size: 14px;
                padding: 0 10px;
            }

            .sv-video-wrapper {
                width: calc(100% - 32px);
                padding: 5px;
                border-radius: 21px;
            }

            .sv-video-frame {
                border-radius: 17px;
            }

            .sv-video-caption {
                padding:
                    12px 10px 5px;
            }

            .sv-video-caption-text {
                font-size: 11px;
            }
        }

    </style>

</head>


<body>

    <div class="sv-page">


        {{-- ========================================================= --}}
        {{-- NAVBAR --}}
        {{-- ========================================================= --}}

        <header class="sv-navbar">

            <div class="sv-container sv-navbar-inner">


                {{-- BRAND --}}
                <a
                    href="{{ route('home') }}"
                    class="sv-brand"
                >

                    <span class="sv-brand-logo">
                        S
                    </span>

                    <span class="sv-brand-name">
                        Speak<span>Verse</span>
                    </span>

                </a>


                {{-- DESKTOP NAVIGATION --}}
                <nav class="sv-nav-menu">

                    <a href="#features">
                        Features
                    </a>

                    <a href="#how-it-works">
                        How It Works
                    </a>

                </nav>


                {{-- NAVBAR ACTION --}}
                <div class="sv-navbar-actions">


                    {{-- THEME --}}
                    <button
                        id="themeToggle"
                        type="button"
                        class="sv-theme-button"
                        aria-label="Change appearance"
                        title="Change appearance"
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


                    {{-- AUTH --}}
                    @auth

                        <a
                            href="{{ auth()->user()->isAdmin()
                                ? route('admin.dashboard')
                                : route('dashboard') }}"
                            class="sv-dashboard-link"
                        >
                            Dashboard
                        </a>

                    @else

                        <a
                            href="{{ route('login') }}"
                            class="sv-login-link"
                        >
                            Log In
                        </a>

                    @endauth


                    {{-- MOBILE MENU BUTTON --}}
                    <button
                        id="mobileMenuButton"
                        type="button"
                        class="sv-mobile-button"
                        aria-label="Open menu"
                        aria-expanded="false"
                    >

                        <svg
                            id="menuOpenIcon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                d="
                                    M4 7h16
                                    M4 12h16
                                    M4 17h16
                                "
                            />
                        </svg>


                        <svg
                            id="menuCloseIcon"
                            style="display: none;"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                d="
                                    M6 6l12 12
                                    M18 6 6 18
                                "
                            />
                        </svg>

                    </button>

                </div>

            </div>

        </header>


        {{-- ========================================================= --}}
        {{-- MOBILE MENU --}}
        {{-- ========================================================= --}}

        <div
            id="mobileMenu"
            class="sv-mobile-menu"
        >

            <div
                class="sv-container
                       sv-mobile-menu-content"
            >

                <a
                    href="#features"
                    class="sv-mobile-link
                           sv-mobile-nav-link"
                >
                    Features
                </a>

                <a
                    href="#how-it-works"
                    class="sv-mobile-link
                           sv-mobile-nav-link"
                >
                    How It Works
                </a>


                <div class="sv-mobile-divider"></div>


                @auth

                    <a
                        href="{{ auth()->user()->isAdmin()
                            ? route('admin.dashboard')
                            : route('dashboard') }}"
                        class="sv-mobile-auth"
                    >
                        Dashboard
                    </a>

                @else

                    <a
                        href="{{ route('login') }}"
                        class="sv-mobile-auth"
                    >
                        Log In
                    </a>

                @endauth

            </div>

        </div>


        <main>


            {{-- ===================================================== --}}
            {{-- HERO --}}
            {{-- ===================================================== --}}

            <section class="sv-hero">

                <div
                    class="sv-container
                           sv-hero-layout"
                >


                    {{-- LEFT --}}
                    <div class="sv-hero-content">

                        <div class="sv-hero-badge">

                            <span
                                class="sv-hero-badge-dot"
                            ></span>

                            Interactive English Learning

                        </div>


                        <h1 class="sv-hero-title">

                            Learn English.

                            <span>
                                Speak Confidently.
                            </span>

                        </h1>


                        <p class="sv-hero-description">

                            Improve your listening, reading, writing,
                            and speaking through interactive lessons,
                            assessments, AI-powered feedback, and
                            measurable learning progress.

                        </p>


                        {{-- ONLY ONE MAIN CTA --}}
                        <div class="sv-hero-action">

                            @auth

                                <a
                                    href="{{ auth()->user()->isAdmin()
                                        ? route('admin.dashboard')
                                        : route('dashboard') }}"
                                    class="sv-primary-button"
                                >

                                    Continue Learning

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
                                                M5 12h14
                                                m-6-6 6 6-6 6
                                            "
                                        />
                                    </svg>

                                </a>

                            @else

                                <a
                                    href="{{ route('register') }}"
                                    class="sv-primary-button"
                                >

                                    Start Learning

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
                                                M5 12h14
                                                m-6-6 6 6-6 6
                                            "
                                        />
                                    </svg>

                                </a>

                            @endauth

                        </div>


                        {{-- BENEFITS --}}
                        <div class="sv-hero-notes">

                            <span class="sv-hero-note">

                                <span class="sv-note-check">
                                    ✓
                                </span>

                                Four Core Skills

                            </span>


                            <span class="sv-hero-note">

                                <span class="sv-note-check">
                                    ✓
                                </span>

                                AI Feedback

                            </span>


                            <span class="sv-hero-note">

                                <span class="sv-note-check">
                                    ✓
                                </span>

                                Progress Tracking

                            </span>

                        </div>

                    </div>


                    {{-- ================================================= --}}
                    {{-- APPLICATION PREVIEW --}}
                    {{-- ================================================= --}}

                    <div class="sv-preview-area">

                        <div class="sv-preview-card">


                            {{-- HEADER --}}
                            <div class="sv-preview-top">

                                <div class="sv-preview-brand">

                                    <span class="sv-preview-logo">
                                        S
                                    </span>

                                    <div>

                                        <strong>
                                            SpeakVerse
                                        </strong>

                                        <small>
                                            Learning Progress
                                        </small>

                                    </div>

                                </div>


                                <span class="sv-student-pill">
                                    Student
                                </span>

                            </div>


                            {{-- CONTENT --}}
                            <div class="sv-preview-body">


                                {{-- MISSION --}}
                                <div class="sv-mission">

                                    <div>

                                        <p class="sv-mission-label">
                                            Current Mission
                                        </p>

                                        <h3 class="sv-mission-title">
                                            Unit 2 — English Skills
                                        </h3>

                                        <p class="sv-mission-text">
                                            Keep going. You're making progress.
                                        </p>

                                    </div>


                                    <span class="sv-status">
                                        In Progress
                                    </span>

                                </div>


                                {{-- PROGRESS --}}
                                <div class="sv-progress">

                                    <div class="sv-progress-info">

                                        <span>
                                            Overall Progress
                                        </span>

                                        <strong>
                                            75%
                                        </strong>

                                    </div>


                                    <div class="sv-progress-track">

                                        <div
                                            class="sv-progress-value"
                                        ></div>

                                    </div>

                                </div>


                                {{-- SKILLS --}}
                                <div class="sv-preview-skills">


                                    {{-- LISTENING --}}
                                    <div class="sv-preview-skill">

                                        <div
                                            class="sv-skill-icon cyan"
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
                                                        M5 12V9
                                                        a7 7 0 0 1 14 0v3
                                                    "
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="
                                                        M5 12H3v5
                                                        a2 2 0 0 0 2 2h2v-7H5
                                                        Z
                                                        m14 0h2v5
                                                        a2 2 0 0 1-2 2h-2v-7h2
                                                        Z
                                                    "
                                                />
                                            </svg>

                                        </div>

                                        <p
                                            class="sv-preview-skill-name"
                                        >
                                            Listening
                                        </p>

                                        <p
                                            class="sv-preview-skill-score"
                                        >
                                            80%
                                        </p>

                                    </div>


                                    {{-- READING --}}
                                    <div class="sv-preview-skill">

                                        <div
                                            class="sv-skill-icon blue"
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
                                                        M4 5.5
                                                        A2.5 2.5 0 0 1 6.5 3
                                                        H11v16H6.5
                                                        A2.5 2.5 0 0 0 4 21.5
                                                        v-16
                                                        Z
                                                        m16 0
                                                        A2.5 2.5 0 0 0 17.5 3
                                                        H13v16h4.5
                                                        A2.5 2.5 0 0 1 20 21.5
                                                        v-16
                                                        Z
                                                    "
                                                />
                                            </svg>

                                        </div>

                                        <p
                                            class="sv-preview-skill-name"
                                        >
                                            Reading
                                        </p>

                                        <p
                                            class="sv-preview-skill-score"
                                        >
                                            75%
                                        </p>

                                    </div>


                                    {{-- WRITING --}}
                                    <div class="sv-preview-skill">

                                        <div
                                            class="sv-skill-icon violet"
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
                                                        m4 20
                                                        4.5-1
                                                        10-10
                                                        a2.1 2.1 0 0 0-3-3
                                                        l-10 10
                                                        L4 20
                                                        Z
                                                    "
                                                />
                                            </svg>

                                        </div>

                                        <p
                                            class="sv-preview-skill-name"
                                        >
                                            Writing
                                        </p>

                                        <p
                                            class="sv-preview-skill-score"
                                        >
                                            70%
                                        </p>

                                    </div>


                                    {{-- SPEAKING --}}
                                    <div class="sv-preview-skill">

                                        <div
                                            class="sv-skill-icon rose"
                                        >

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <rect
                                                    x="9"
                                                    y="3"
                                                    width="6"
                                                    height="11"
                                                    rx="3"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="
                                                        M5 11
                                                        a7 7 0 0 0 14 0
                                                        M12 18v3
                                                        M9 21h6
                                                    "
                                                />
                                            </svg>

                                        </div>

                                        <p
                                            class="sv-preview-skill-name"
                                        >
                                            Speaking
                                        </p>

                                        <p
                                            class="sv-preview-skill-score"
                                        >
                                            75%
                                        </p>

                                    </div>

                                </div>


                                {{-- AI --}}
                                <div class="sv-ai-row">

                                    <div class="sv-ai-icon">

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
                                                    M12 18.5
                                                    a6.5 6.5 0 1 0 0-13
                                                    6.5 6.5 0 0 0 0 13
                                                    Z
                                                "
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="
                                                    M9.5 11.5h.01
                                                    M14.5 11.5h.01
                                                    M10 14.5
                                                    c.6.5 1.3.75 2 .75
                                                    s1.4-.25 2-.75
                                                "
                                            />
                                        </svg>

                                    </div>


                                    <div>

                                        <strong>
                                            AI-powered feedback
                                        </strong>

                                        <span>
                                            Personalized feedback after
                                            completing learning activities.
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            {{-- ===================================================== --}}
            {{-- VIDEO INTRODUCTION --}}
            {{-- ===================================================== --}}

            <section class="sv-video-section">

                <div class="sv-video-heading">

                    <div class="sv-video-label">
                        <span class="sv-video-label-dot"></span>
                        Discover SpeakVerse
                    </div>

                    <h2 class="sv-video-title">
                        Learn smarter.
                        <span>Speak with confidence.</span>
                    </h2>

                    <p class="sv-video-description">
                        See how SpeakVerse helps you practice English
                        through interactive lessons, AI-powered feedback,
                        and meaningful learning progress.
                    </p>

                </div>


                <div class="sv-video-wrapper">

                    <div class="sv-video-frame">

                        <video
                            controls
                            playsinline
                            preload="metadata"
                        >
                            <source
                                src="/build/videos/video singkat fixx spekaverse.mp4"
                                type="video/mp4"
                            >

                            Your browser does not support the video tag.
                        </video>

                    </div>


                    <div class="sv-video-caption">

                        <div class="sv-video-caption-text">

                            <strong>
                                See SpeakVerse in action
                            </strong>

                            Explore the interactive English
                            learning experience.

                        </div>


                        <div class="sv-video-play-badge">

                            <svg
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M8 5v14l11-7z"
                                />
                            </svg>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ===================================================== --}}
            {{-- CORE SKILLS --}}
            {{-- ===================================================== --}}

            <section
                id="features"
                class="sv-section
                       sv-section-border"
            >

                <div class="sv-container">


                    <div class="sv-section-heading">

                        <p class="sv-section-label">
                            Core Skills
                        </p>

                        <h2 class="sv-section-title">
                            Learn the four essential English skills
                        </h2>

                        <p class="sv-section-description">
                            Everything is organized in one simple
                            learning experience.
                        </p>

                    </div>


                    <div class="sv-features">


                        {{-- LISTENING --}}
                        <article class="sv-feature-card">

                            <div
                                class="sv-feature-icon cyan"
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
                                            M5 12V9
                                            a7 7 0 0 1 14 0v3
                                        "
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="
                                            M5 12H3v5
                                            a2 2 0 0 0 2 2h2v-7H5
                                            Z
                                            m14 0h2v5
                                            a2 2 0 0 1-2 2h-2v-7h2
                                            Z
                                        "
                                    />
                                </svg>

                            </div>

                            <h3>
                                Listening
                            </h3>

                            <p>
                                Practice comprehension through
                                interactive audio activities.
                            </p>

                        </article>


                        {{-- READING --}}
                        <article class="sv-feature-card">

                            <div
                                class="sv-feature-icon blue"
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
                                            M4 5.5
                                            A2.5 2.5 0 0 1 6.5 3
                                            H11v16H6.5
                                            A2.5 2.5 0 0 0 4 21.5
                                            v-16
                                            Z
                                            m16 0
                                            A2.5 2.5 0 0 0 17.5 3
                                            H13v16h4.5
                                            A2.5 2.5 0 0 1 20 21.5
                                            v-16
                                            Z
                                        "
                                    />
                                </svg>

                            </div>

                            <h3>
                                Reading
                            </h3>

                            <p>
                                Improve comprehension through
                                contextual reading activities.
                            </p>

                        </article>


                        {{-- WRITING --}}
                        <article class="sv-feature-card">

                            <div
                                class="sv-feature-icon violet"
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
                                            m4 20
                                            4.5-1
                                            10-10
                                            a2.1 2.1 0 0 0-3-3
                                            l-10 10
                                            L4 20
                                            Z
                                        "
                                    />
                                </svg>

                            </div>

                            <h3>
                                Writing
                            </h3>

                            <p>
                                Practice structured writing with
                                AI-assisted evaluation.
                            </p>

                        </article>


                        {{-- SPEAKING --}}
                        <article class="sv-feature-card">

                            <div
                                class="sv-feature-icon rose"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <rect
                                        x="9"
                                        y="3"
                                        width="6"
                                        height="11"
                                        rx="3"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="
                                            M5 11
                                            a7 7 0 0 0 14 0
                                            M12 18v3
                                            M9 21h6
                                        "
                                    />
                                </svg>

                            </div>

                            <h3>
                                Speaking
                            </h3>

                            <p>
                                Develop confidence through conversation
                                practice and AI feedback.
                            </p>

                        </article>

                    </div>

                </div>

            </section>


            {{-- ===================================================== --}}
            {{-- HOW IT WORKS --}}
            {{-- ===================================================== --}}

            <section
                id="how-it-works"
                class="sv-section"
            >

                <div class="sv-container">


                    <div class="sv-section-heading">

                        <p class="sv-section-label">
                            How It Works
                        </p>

                        <h2 class="sv-section-title">
                            A simple learning journey
                        </h2>

                    </div>


                    <div class="sv-steps">


                        {{-- STEP 1 --}}
                        <article class="sv-step">

                            <div class="sv-step-header">

                                <span
                                    class="sv-step-number one"
                                >
                                    01
                                </span>

                                <h3>
                                    Take the Pre-Test
                                </h3>

                            </div>

                            <p>
                                Find out your starting performance
                                across the four English skills.
                            </p>

                        </article>


                        {{-- STEP 2 --}}
                        <article class="sv-step">

                            <div class="sv-step-header">

                                <span
                                    class="sv-step-number two"
                                >
                                    02
                                </span>

                                <h3>
                                    Complete Missions
                                </h3>

                            </div>

                            <p>
                                Learn through structured and
                                interactive activities.
                            </p>

                        </article>


                        {{-- STEP 3 --}}
                        <article class="sv-step">

                            <div class="sv-step-header">

                                <span
                                    class="sv-step-number three"
                                >
                                    03
                                </span>

                                <h3>
                                    Track Progress
                                </h3>

                            </div>

                            <p>
                                Review your results and see how
                                your English develops.
                            </p>

                        </article>

                    </div>

                </div>

            </section>

        </main>


        {{-- ========================================================= --}}
        {{-- FOOTER --}}
        {{-- ========================================================= --}}

        <footer class="sv-footer">

            <div
                class="sv-container
                       sv-footer-inner"
            >

                <a
                    href="{{ route('home') }}"
                    class="sv-brand"
                >

                    <span class="sv-brand-logo">
                        S
                    </span>

                    <span class="sv-brand-name">
                        Speak<span>Verse</span>
                    </span>

                </a>


                <span class="sv-footer-copy">
                    Interactive English Learning Platform
                </span>

            </div>

        </footer>

    </div>


    {{-- ========================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================================================= --}}

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {


                /* =====================================================
                   THEME
                ===================================================== */

                const themeToggle =
                    document.getElementById('themeToggle');


                function currentTheme() {

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
                            'aria-label',
                            theme === 'dark'
                                ? 'Switch to light mode'
                                : 'Switch to dark mode'
                        );


                        themeToggle.setAttribute(
                            'title',
                            theme === 'dark'
                                ? 'Light mode'
                                : 'Dark mode'
                        );

                    }

                }


                setTheme(
                    currentTheme()
                );


                if (themeToggle) {

                    themeToggle.addEventListener(
                        'click',
                        function () {

                            const nextTheme =
                                currentTheme() === 'dark'
                                    ? 'light'
                                    : 'dark';


                            setTheme(
                                nextTheme
                            );

                        }
                    );

                }


                /* =====================================================
                   MOBILE MENU
                ===================================================== */

                const menuButton =
                    document.getElementById(
                        'mobileMenuButton'
                    );


                const mobileMenu =
                    document.getElementById(
                        'mobileMenu'
                    );


                const menuOpenIcon =
                    document.getElementById(
                        'menuOpenIcon'
                    );


                const menuCloseIcon =
                    document.getElementById(
                        'menuCloseIcon'
                    );


                const mobileLinks =
                    document.querySelectorAll(
                        '.sv-mobile-nav-link'
                    );


                if (
                    menuButton &&
                    mobileMenu
                ) {


                    function closeMobileMenu() {

                        mobileMenu.classList.remove(
                            'open'
                        );


                        document.body.classList.remove(
                            'sv-menu-open'
                        );


                        menuButton.setAttribute(
                            'aria-expanded',
                            'false'
                        );


                        if (menuOpenIcon) {

                            menuOpenIcon.style.display =
                                '';

                        }


                        if (menuCloseIcon) {

                            menuCloseIcon.style.display =
                                'none';

                        }

                    }


                    function openMobileMenu() {

                        mobileMenu.classList.add(
                            'open'
                        );


                        document.body.classList.add(
                            'sv-menu-open'
                        );


                        menuButton.setAttribute(
                            'aria-expanded',
                            'true'
                        );


                        if (menuOpenIcon) {

                            menuOpenIcon.style.display =
                                'none';

                        }


                        if (menuCloseIcon) {

                            menuCloseIcon.style.display =
                                '';

                        }

                    }


                    menuButton.addEventListener(
                        'click',
                        function () {

                            if (
                                mobileMenu.classList.contains(
                                    'open'
                                )
                            ) {

                                closeMobileMenu();

                            } else {

                                openMobileMenu();

                            }

                        }
                    );


                    mobileLinks.forEach(
                        function (link) {

                            link.addEventListener(
                                'click',
                                closeMobileMenu
                            );

                        }
                    );


                    window.addEventListener(
                        'resize',
                        function () {

                            if (
                                window.innerWidth > 680
                            ) {

                                closeMobileMenu();

                            }

                        }
                    );

                }

            }
        );

    </script>

</body>

</html>