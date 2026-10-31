<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Credentials untuk layanan pihak ketiga.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dinoiki AI
    |--------------------------------------------------------------------------
    */

    'dinoiki' => [
        'key' => env('DINOIKI_API_KEY'),

        /*
        |----------------------------------------------------------------
        | Pembagian beban ke beberapa subscription (opsional)
        |----------------------------------------------------------------
        |
        | Isi DINOIKI_API_KEY_2 (dan seterusnya kalau mau nambah lagi)
        | di .env untuk mengaktifkan pembagian beban round-robin lewat
        | DinoikiKeyRotationServiceProvider. Kalau DINOIKI_API_KEY_2
        | kosong, array ini cuma berisi 1 key dan sistem berjalan
        | seperti biasa (tidak ada perubahan perilaku).
        |
        */
        'keys' => array_values(array_filter([
            env('DINOIKI_API_KEY'),
            env('DINOIKI_API_KEY_2'),
        ])),

        /*
        |----------------------------------------------------------------
        | Pasangan key + model (satu model per key)
        |----------------------------------------------------------------
        |
        | Key 1 memakai DINOIKI_CHAT_MODEL, key 2 memakai
        | DINOIKI_CHAT_MODEL_2 (kalau kosong, sama dengan model key 1).
        | Entri tanpa key diabaikan.
        |
        */
        'pool' => array_values(array_filter([
            [
                'key' => env('DINOIKI_API_KEY'),
                'model' => env('DINOIKI_CHAT_MODEL', 'gpt-4o'),
            ],
            [
                'key' => env('DINOIKI_API_KEY_2'),
                'model' => env('DINOIKI_CHAT_MODEL_2', env('DINOIKI_CHAT_MODEL', 'gpt-4o')),
            ],
        ], fn ($entry) => ! empty($entry['key']))),

        /*
        | 'roundrobin' : tiap request bergantian antar key (beban terbagi).
        | 'primary'    : selalu key 1; key 2 HANYA dipakai bila key 1 gagal
        |                (failover). Cocok bila ingin penilaian konsisten
        |                memakai satu model.
        */
        'rotation_mode' => env('DINOIKI_ROTATION_MODE', 'roundrobin'),

        'base_url' => env(
            'DINOIKI_API_URL',
            'https://ai.dinoiki.com/v1'
        ),

        'chat_model' => env(
            'DINOIKI_CHAT_MODEL',
            'gpt-4o'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),

        'client_secret' => env('GOOGLE_CLIENT_SECRET'),

        'redirect' => env(
            'GOOGLE_REDIRECT_URI',
            'https://speakverse.id/auth/google/callback'
        ),
    ],

];