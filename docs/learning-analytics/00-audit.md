# Audit data Learning Analytics SpeakVerse (26 Okt 2026)

Disusun dari migrasi dan kode di repo (commit dasar b8d64c6). Tidak ada asumsi nama kolom di luar yang tertulis di migrasi.

## 1. Data yang tersedia
| Sumber | Kolom penting | Dipakai untuk |
|---|---|---|
| `users` | role, school, major, grade, parallel, xp, last_activity_date | cakupan/filter, XP |
| `assessment_submissions` | user_id, unit_id, lesson_id, type, skill, final_score, status, submitted_at, quiz_session_id, criteria_scores | hasil belajar, jejak waktu |
| `assessment_answers` | skor Writing/Speaking per jawaban | kualitas butir (Fase 2) |
| `user_lesson_progress` | user_id, unit_id, lesson_id, skill_type, status, score, completed_at | progres, jejak waktu |
| `learning_events` | quiz_session_id, lesson_id, question_id, skill (reading/vocabulary/listening), event_type (start/select/revise/hint_request/submit/abandon), is_correct, error_code, hint_level_shown, response_ms, attempt_no, payload, created_at | SRL, hint, waktu respons, jejak waktu |
| `gamification_transactions` | user_id, type, amount, reason, event_key, created_at | jejak waktu |
| `reading_questions`, `listening_questions`, `vocabulary_pretests` | sub_skill, metadata diagnostik, error labels | diagnosis, kualitas butir |
| `teacher_scaffolding_policies`, `notifications` | kebijakan guru, notifikasi intervensi | evaluasi intervensi (Fase 4) |

## 2. Celah data
1. **Tidak ada log login / page-view / durasi sesi.** Tabel `sessions` bawaan Laravel hanya menyimpan sesi aktif terakhir, bukan riwayat. Akibatnya "konsistensi akses" hanya dapat diturunkan dari aktivitas yang meninggalkan jejak, dan durasi hanya estimasi (jeda antar-aktivitas dipangkas).
2. `learning_events` hanya mencatat Reading, Vocabulary, Listening. Writing dan Speaking tidak punya log per-butir (hanya submission dan skor).
3. Tidak ada tanggal tenggat/jadwal tes di basis data, sehingga belajar menumpuk menjelang tes tidak dapat dideteksi. Fase 1 mengukur penumpukan beban antar hari.
4. Tidak ada kolom jenis kelamin atau kelompok riset (kontrol/perlakuan), sehingga analisis kesenjangan dan evaluasi eksperimen perlu kolom tambahan (Fase 2/4).
5. `learning_events` dapat diarsipkan manual (`learning:archive-events`); agregat harian (`la_daily_student`) menyimpan ringkasan agar analisis tidak hilang bila log mentah dihapus.

## 3. Rencana migrasi (additive, reversible)
- Fase 1: `la_daily_student`.
- Fase 2: `la_item_stats` (kualitas butir), kolom opsional `users.gender`, `users.research_group`.
- Fase 3: `la_mastery`.
- Fase 4: `la_risk_scores`, `la_model_runs`, `la_interventions`.
- Opsional (perlu keputusan pemilik): middleware ping aktivitas ringan (`la_activity_pings`) agar login/akses tercatat. Dampak: satu penulisan per siswa per menit-aktif; data pribadi tambahan sehingga perlu persetujuan penelitian.

## 4. Fase
1. Agregat harian + keterlibatan & konsistensi (dikerjakan, branch `la-fase-1-keterlibatan`).
2. Gain Hake, ukuran efek, uji berpasangan, ICC, kualitas butir.
3. BKT, wheel spinning, SRL/klaster.
4. Peringatan dini, evaluasi intervensi.
5. Halaman guru/siswa, ekspor riset pseudonim, halaman Metodologi lengkap.
