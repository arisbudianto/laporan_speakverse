<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Learning Analytics — pengaturan bawaan
    |--------------------------------------------------------------------------
    | Bobot risiko dapat ditimpa admin lewat halaman Peringatan Dini (tabel la_settings).
    | Skor risiko = Σ(bobot × komponen[0..1]) / Σ(bobot komponen yang punya data) × 100.
    */
    'risk' => [
        'weights' => [
            'inactivity' => 20,   // hari sejak aktif terakhir (14 hari = penuh)
            'irregular' => 10,    // 1 − indeks keteraturan/100
            'decline' => 10,      // kemiringan hari-aktif/minggu negatif (−2 = penuh)
            'hint_dependence' => 10, // porsi butir dengan hint level 3 (0,30 = penuh)
            'inferential' => 15,  // salah inferensial percobaan pertama relatif terhadap ambang intervensi
            'lag' => 15,          // tertinggal dari median progres kelas (0,5 = penuh)
            'low_accuracy' => 12, // 1 − akurasi percobaan pertama
            'wheel' => 8,         // jumlah kompetensi berputar-putar (2 = penuh)
        ],
        'level_high' => 60,
        'level_medium' => 35,
        'posttest_threshold' => 70,   // sasaran model statistik: posttest di bawah ini
        'min_students_model' => 60,
        'min_class_model' => 15,
        'min_auc_to_show' => 0.70,
        'started_min_items' => 10,    // butir terjawab agar dianggap "sudah mulai" bila tak ada hari aktif belajar
        'calibration' => [            // ambang berbasis persentil pada siswa yang sudah mulai
            'min_n' => 30, 'pct_high' => 8, 'pct_medium' => 17, 'floor_high' => 45, 'floor_medium' => 25,
        ],
    ],
    'outcomes' => [
        'nonequivalence_delta_p' => 0.10, // |rerata p butir pre − post| di atas ini → bentuk tes tidak sebanding
        'min_group_icc' => 5,             // ICC/design effect butuh ≥ N siswa per kelompok dan ≥ 3 kelompok
    ],
    'engagement' => [
        'stop_days' => 14,
    ],
    'page_log' => [
        'enabled' => env('LA_PAGE_LOG', true),   // catat kunjungan halaman siswa (hanya nama rute + waktu)
        'retention_days' => 180,
    ],
];
