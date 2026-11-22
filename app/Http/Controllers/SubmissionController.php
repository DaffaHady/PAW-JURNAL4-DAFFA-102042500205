<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isMahasiswa()) {
            abort(403, 'Akses ditolak. Pengumpulan tugas hanya dapat dilakukan oleh Mahasiswa.');
        }

        $user = Auth::user();

        $enrollment = $assignment->course->students()->where('users.id', $user->id)->first();
        if (!$enrollment || $enrollment->pivot->status !== 'approved') {
            abort(403, 'Akses ditolak. Anda belum disetujui oleh Dosen untuk mengikuti mata kuliah tugas ini.');
        }

        if ($assignment->isOverdue()) {
            return back()->with('error', 'Gagal mengumpulkan: Batas waktu pengumpulan (deadline) tugas telah berakhir.');
        }

        $validated = $request->validate([
            'jawaban' => ['required', 'string', 'min:10', 'max:20000'],
        ], [
            'jawaban.required' => 'Teks jawaban tugas wajib diisi.',
            'jawaban.min' => 'Jawaban terlalu pendek. Tuliskan jawaban minimal 10 karakter.',
            'jawaban.max' => 'Jawaban melebihi batas maksimal 20.000 karakter.',
        ]);

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ],
            [
                'jawaban' => $validated['jawaban'],
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Jawaban tugas Anda berhasil disimpan ke sistem.');
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        // TODO 5a (grade): Cek apakah dosen adalah pengampu mata kuliah, validasi nilai (0-100) & catatan_dosen, lalu update submission.
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $submission->loadMissing('assignment.course');

        if (!Auth::user()->isDosen() || ($submission->assignment->course->dosen_id ?? null) !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah tugas ini.');
        }

        $validated = $request->validate([
            'nilai' => ['required', 'integer', 'min:0', 'max:100'],
            'catatan_dosen' => ['nullable', 'string', 'max:1000'],
        ], [
            'nilai.required' => 'Nilai wajib diisi.',
            'nilai.integer' => 'Nilai harus berupa angka bulat.',
            'nilai.min' => 'Nilai minimal adalah 0.',
            'nilai.max' => 'Nilai maksimal adalah 100.',
            'catatan_dosen.max' => 'Catatan dosen maksimal 1000 karakter.',
        ]);

        $submission->update([
            'nilai' => $validated['nilai'],
            'catatan_dosen' => $validated['catatan_dosen'] ?? null,
        ]);

        return back()->with('success', 'Nilai dan catatan dosen berhasil disimpan.');
    }

        abort(501, 'Fitur belum diimplementasikan');
    }

    public function exportCsv(Assignment $assignment): StreamedResponse
    {
        // TODO 5b (exportCsv): Cek kepemilikan dosen, lalu gunakan response()->streamDownload() dan fputcsv() untuk mengunduh data nilai mahasiswa.
    {
        if (!Auth::check()) {
            abort(401);
        }

        $assignment->loadMissing(['course.approvedStudents', 'submissions.mahasiswa']);

        if (!Auth::user()->isDosen() || ($assignment->course->dosen_id ?? null) !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah tugas ini.');
        }

        $fileName = 'rekap-nilai-tugas-' . $assignment->id . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($assignment) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No',
                'Kode MK',
                'Mata Kuliah',
                'Judul Tugas',
                'Nama Mahasiswa',
                'Email',
                'Waktu Kumpul',
                'Nilai',
                'Status Kelulusan',
                'Catatan Dosen',
            ]);

            $submissionsByUser = $assignment->submissions->keyBy('user_id');
            $students = $assignment->course->approvedStudents;

            foreach ($students as $index => $student) {
                $submission = $submissionsByUser->get($student->id);

                fputcsv($handle, [
                    $index + 1,
                    $assignment->course->kode_mk ?? '-',
                    $assignment->course->nama_mk ?? '-',
                    $assignment->judul ?? '-',
                    $student->name ?? '-',
                    $student->email ?? '-',
                    optional($submission?->submitted_at)->format('Y-m-d H:i:s') ?? 'Belum Mengumpulkan',
                    $submission?->nilai ?? '-',
                    $submission ? $submission->status_kelulusan : 'Belum Dinilai',
                    $submission?->catatan_dosen ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

        abort(501, 'Fitur belum diimplementasikan');
    }
}
