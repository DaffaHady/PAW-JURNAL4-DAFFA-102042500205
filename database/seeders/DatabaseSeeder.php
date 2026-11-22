<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\MataKuliah;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dosen = User::updateOrCreate(
            ['email' => 'dosen@celoe.test'],
            [
                'name' => 'Dr. Ir. Budi Santoso, M.T.',
                'password' => Hash::make('password'),
                'role' => 'dosen',
            ]
        );

        $mhs1 = User::updateOrCreate(
            ['email' => 'mahasiswa@celoe.test'],
            [
                'name' => 'Ahmad Pratama',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ]
        );

        $mhs2 = User::updateOrCreate(
            ['email' => 'siti@celoe.test'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ]
        );

        $mk1 = MataKuliah::create([
            'kode_mk' => 'IF301',
            'nama_mk' => 'Pengembangan Aplikasi Website',
            'sks' => 4,
            'semester' => 'Ganjil 2026/2027',
            'dosen_id' => $dosen->id,
            'deskripsi' => 'Arsitektur aplikasi web modern menggunakan Laravel, MVC, Eloquent ORM, dan keamanan web.',
        ]);

        $mk2 = MataKuliah::create([
            'kode_mk' => 'IF204',
            'nama_mk' => 'Basis Data Relasional',
            'sks' => 3,
            'semester' => 'Genap 2025/2026',
            'dosen_id' => $dosen->id,
            'deskripsi' => 'Perancangan basis data relasional, normalisasi, ERD, dan optimasi query SQL.',
        ]);

        if (method_exists($mk1, 'students')) {
            $mk1->students()->syncWithoutDetaching([$mhs1->id, $mhs2->id]);
        }
        if (method_exists($mk2, 'students')) {
            $mk2->students()->syncWithoutDetaching([$mhs1->id]);
        }

        $tugas1 = Assignment::create([
            'course_id' => $mk1->id,
            'judul' => 'Tugas 1: Implementasi Middleware & Eloquent ORM',
            'deskripsi' => 'Jelaskan cara kerja Route Middleware untuk proteksi peran pengguna serta implementasi relasi Eloquent.',
            'deadline' => now()->addDays(7),
        ]);

        Submission::updateOrCreate(
            ['assignment_id' => $tugas1->id, 'user_id' => $mhs1->id],
            [
                'jawaban' => 'Middleware menyaring HTTP request sebelum masuk ke controller dan memvalidasi role pengguna.',
                'submitted_at' => now()->subHours(5),
                'nilai' => 80,
                'catatan_dosen' => 'Penjelasan terstruktur dan tepat sasaran.',
            ]
        );

        Submission::updateOrCreate(
            ['assignment_id' => $tugas1->id, 'user_id' => $mhs2->id],
            [
                'jawaban' => 'Eloquent ORM mempermudah relasi antar model serta query data secara ekspresif.',
                'submitted_at' => now()->subHours(3),
                'nilai' => 90,
                'catatan_dosen' => 'Sudah baik, lengkapi dengan contoh eager loading.',
            ]
        );
    }
}