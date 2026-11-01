<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modul 4 — Adaptation Policy
    |--------------------------------------------------------------------------
    |
    | Semua di file ini adalah TABEL ATURAN if-else (bukan machine
    | learning) sesuai instruksi dokumen rencana: "Jangan pakai ML
    | dulu." policy_version dicatat di setiap event hint (lewat
    | payload learning_events) supaya kalau angka-angka di bawah
    | diubah nanti (tuning selama DBR), data lama tetap bisa dilihat
    | dihasilkan oleh kebijakan versi berapa.
    */

    'policy_version' => 'v1',

    'adaptation' => [

        // response_ms dianggap "jauh di atas median soal" kalau lebih
        // dari (median x angka ini). Proksi sederhana untuk P75 tanpa
        // perlu hitung percentile asli — cukup untuk versi awal,
        // silakan di-tuning setelah lihat data nyata dari DBR.
        'slow_response_multiplier' => 1.5,

        // Jumlah revisi (ganti jawaban) minimal yang, DIGABUNG dengan
        // response_ms lambat, dianggap tanda ketidakpastian siswa.
        'high_revision_threshold' => 2,

        // "Fading": berapa soal beruntun harus benar TANPA hint
        // sebelum level bantuan di soal berikutnya dibatasi.
        'fading_streak' => 2,
        'fading_max_level' => 1,

        // Saran perbaikan: fading sekarang LINTAS SESI (lihat
        // AdaptationPolicy::isFadingActive()) — angka ini membatasi
        // berapa baris learning_events MENTAH yang diambil sebelum
        // dikelompokkan per (sesi, soal), supaya query tetap ringan
        // walau riwayat siswa sudah sangat panjang.
        'fading_lookback_limit' => 200,

        // Batas atas level bantuan yang ada di sistem.
        'max_level' => 3,

    ],

    /*
    |--------------------------------------------------------------------------
    | Modul 5 — In-Quiz Tutor UX
    |--------------------------------------------------------------------------
    |
    | Keputusan wajib dari dokumen rencana: "skor akhir memakai
    | percobaan pertama atau rumus partial credit (tetapkan satu,
    | tulis di config)."
    |
    | DIPILIH: 'first_attempt'. Siswa boleh tetap mencoba ulang &
    | minta hint sampai max_checks_per_question (untuk BELAJAR), tapi
    | skor soal itu sudah ditentukan dari percobaan PERTAMA — supaya
    | siswa tidak bisa "asal coba sampai benar" demi skor. Formula
    | 'partial_credit' belum diimplementasikan (placeholder untuk
    | eksperimen DBR lanjutan).
    */
    'scoring' => [
        'formula' => 'first_attempt',

        'max_checks_per_question' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Modul 6 — Teacher HITL Override
    |--------------------------------------------------------------------------
    |
    | Ambang batas daftar "perlu intervensi" di dashboard guru
    | (DoD Modul 6: "error inferensial > ambang atau hint L3 >= N").
    | Dihitung per siswa, per kelas, dari data yang sudah dikumpulkan
    | Modul 1 & 2 — tidak ada tabel/kolom baru yang dibutuhkan untuk
    | ini.
    */
    'intervention' => [
        // Siswa masuk daftar "perlu intervensi" kalau jumlah jawaban
        // SALAH percobaan pertama dengan error_code='inferential'
        // (Reading) melebihi angka ini.
        'inferential_wrong_threshold' => 2,

        // ATAU kalau jumlah permintaan hint level 3 (Socratic) sudah
        // mencapai angka ini.
        'hint_l3_threshold' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Saran perbaikan — alirkan cefr_band ke Modul 3
    |--------------------------------------------------------------------------
    |
    | Lihat App\Services\Learning\CefrBandResolver. BUKAN asesmen CEFR
    | resmi — proksi kasar dari skor rata-rata Pre-Test, cukup untuk
    | menyesuaikan kompleksitas bahasa hint.
    */
    'cefr' => [
        'thresholds' => [
            'A1' => 0,
            'A2' => 40,
            'B1' => 60,
            'B2' => 75,
            'C1' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Saran perbaikan — kontrol biaya LLM
    |--------------------------------------------------------------------------
    |
    | Lihat App\Services\Learning\ScaffoldingEngine (cache + rate
    | limit). Keduanya SELALU fail-safe ke template rule-based —
    | tidak pernah memblokir siswa dari bantuan sama sekali, cuma
    | menurunkan dari LLM ke template kalau perlu.
    */
    'llm' => [
        // Batas panggilan LLM (bukan cache hit, bukan template) per
        // siswa per 24 jam, khusus untuk hint (Modul 3). Tidak
        // membatasi penilaian AI Writing/Speaking — itu jalur
        // terpisah.
        'max_hint_calls_per_user_per_day' => 30,

        // Berapa lama hasil hint LLM (level 1/2, atau set pertanyaan
        // Socratic level 3) disimpan di cache dan dibagi ke SEMUA
        // siswa yang dapat soal + error_code + level + rentang CEFR
        // yang sama — HANYA untuk permintaan hint PERTAMA per soal
        // (hint_history kosong); begitu siswa sudah pernah dapat
        // hint untuk soal itu, cache dilewati demi personalisasi.
        'cache_days' => 14,
    ],

];
