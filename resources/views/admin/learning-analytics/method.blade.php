@extends('layouts.admin')

@section('content')
    @php $card = 'rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.03]'; @endphp
    <div class="space-y-6">
        @include('admin.learning-analytics._tabs')
        <div>
            <h1 class="text-3xl font-black">Metodologi &amp; Keterbatasan</h1>
            <p class="mt-1 text-sm text-slate-500">Ringkasan rumus, ambang, dan batas klaim setiap metrik agar dapat dilaporkan secara jujur dalam penelitian.</p>
        </div>

        @foreach ([
            ['Keterlibatan', 'Aktivitas diturunkan dari learning_events, pengumpulan asesmen, kemajuan pelajaran, dan transaksi gamifikasi karena tidak ada log login/halaman. Sesi = rangkaian peristiwa dengan jeda ≤ 30 menit; durasi sesi adalah estimasi. Indeks keteraturan 0–100 dari sebaran hari aktif 28 hari; pola: konsisten, sporadis, menumpuk (≥ 50% menit aktif dalam sehari), menurun, berhenti (≥ 14 hari tidak aktif), tidak aktif.'],
            ['Hasil belajar', 'Gain = post − pre; gain ternormalisasi Hake = (post − pre)/(100 − pre); uji-t berpasangan, Wilcoxon (eksak bila n ≤ 25), ukuran efek Hedges g dengan CI95. Tanpa kelompok kontrol, perubahan tidak dapat diklaim sebagai efek pembelajaran.'],
            ['Kualitas soal', 'Teori tes klasik: tingkat kesulitan p, korelasi biserial-titik terkoreksi (rest-score), indeks daya beda D (27% atas–bawah), α Cronbach/KR-20, SEM. Penanda ditampilkan hanya bila jumlah jawaban memadai.'],
            ['Penguasaan (BKT)', 'Bayesian Knowledge Tracing per kompetensi (p_init, p_learn, p_slip, p_guess) dicari lewat grid dengan batas p_slip, p_guess ≤ 0,3. Penguasaan ≥ 0,95. "Berputar-putar" bila banyak percobaan tanpa kenaikan penguasaan. Parameter butuh data cukup; kompetensi dengan data sedikit tidak ditampilkan.'],
            ['SRL & klaster', 'Fitur perilaku (hint, waktu jawab, usaha menghadapi salah) distandarkan lalu k-means (k dipilih dengan siluet). Profil adalah deskripsi perilaku, bukan sifat siswa; dapat berubah antar periode.'],
            ['Peringatan dini', 'Skor aturan 0–100 dari komponen berbobot yang dapat diubah admin; dinormalkan hanya pada komponen yang punya data dan diberi tingkat keyakinan. Model regresi logistik (ridge) memprediksi posttest < 70, divalidasi silang 5-lipat, dan hanya ditampilkan bila n ≥ 60, tiap kelas ≥ 15, AUC ≥ 0,70. Diperiksa per sekolah untuk keadilan. Skor adalah alat bantu keputusan guru, bukan keputusan otomatis.'],
            ['Evaluasi intervensi', 'Perubahan hari aktif 14 hari sebelum vs sesudah tindak lanjut, dibandingkan dengan siswa berisiko yang tidak ditindaklanjuti (Welch). Bukan acak: ada bias seleksi (guru memilih siswa) sehingga hasilnya asosiasi.'],
            ['Privasi', 'Ekspor riset memakai ID pseudonim HMAC-SHA256 dan kode sekolah; hanya siswa yang setuju. Ekspor teridentifikasi atau tanpa persetujuan memerlukan alasan dan dicatat. Guru hanya melihat kelas yang diampu; siswa hanya melihat data sendiri.'],
            ['Kualitas data & kohort', 'Akun uji dan akun yang dikecualikan (halaman Kohort) tidak dihitung di seluruh analisis. Aktivitas belajar dipisahkan dari aktivitas tes: hari yang hanya berisi tes tidak dihitung sebagai keterlibatan belajar. Siswa tanpa aktivitas belajar diberi kategori "belum mulai", bukan risiko rendah.'],
            ['Kesetaraan bentuk tes', 'Selisih pre-post hanya bermakna bila kesulitan butir kedua bentuk setara. Sistem membandingkan rerata p butir per keterampilan; bila selisih > 0,10 hasil ditandai tidak sebanding dan analisis relatif (selisih z per sekolah) ditampilkan sebagai pengganti. ICC hanya dihitung bila ada ≥ 3 kelompok berukuran ≥ 5.'],
            ['Kalibrasi risiko', 'Ambang tinggi/sedang disesuaikan dengan sebaran skor (persentil 92 dan 83) dengan lantai 45 dan 25 agar daftar tetap bermakna. Ini penandaan relatif, bukan prediksi mutlak; model logistik hanya dilaporkan bila datanya cukup.'],
            ['Koreksi uji ganda', 'Korelasi perilaku–gain dilaporkan dengan p mentah, p terkoreksi Holm (FWER) dan Benjamini–Hochberg (FDR). Klaim sebaiknya merujuk nilai terkoreksi.'],
            ['Efektivitas konten', 'Gain diregresikan pada dosis konten dan pretes (OLS) per pelajaran; hanya ditampilkan bila n ≥ 20 dengan ≥ 5 siswa terpapar. Bersifat asosiasi: siswa yang rajin berbeda dari yang tidak, sehingga bukan bukti kausal.'],
            ['Model multilevel', 'Gain diregresikan pada pretest (dan kelompok penelitian bila ada ≥ 2 kelas kontrol dan ≥ 2 kelas perlakuan) dengan intersep acak per kelas, diestimasi REML. SE mengabaikan ketidakpastian varians kelas; df efek tingkat-kelas = jumlah kelas − 2. Uji varians kelas memakai LRT dengan campuran ½χ²₁. Hasil observasional kecuali kelompok diacak.'],
            ['Jejak halaman', 'Kunjungan halaman siswa dicatat hanya sebagai nama rute dan waktu (maks. satu per rute per 5 menit), tanpa URL, IP, atau isi; siswa yang menolak persetujuan tidak dicatat; data dihapus setelah 180 hari. Dipakai untuk membedakan "berkunjung tanpa belajar" dari hari aktif belajar. Dapat dimatikan dengan LA_PAGE_LOG=false.'],
            ['Filter kohort & gabung akun', 'Filter kohort (sekolah/kelompok) berlaku untuk tabel per siswa; analisis butir dan parameter BKT dihitung dari seluruh kohort. Penggabungan akun memindahkan riwayat ke akun utama tanpa menghapus apa pun, mencatat riwayat, dan dapat dibatalkan; hitung ulang analitik setelahnya.'],
        ] as [$t, $d])
            <div class="{{ $card }}"><h2 class="text-lg font-black">{{ $t }}</h2><p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $d }}</p></div>
        @endforeach
    </div>
@endsection
