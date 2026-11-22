<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'jawaban',
        'submitted_at',
        'nilai',
        'catatan_dosen',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    protected function statusKelulusan(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->nilai)) {
                    return 'Belum Dinilai';
                }

                return $this->nilai >= 60 ? 'Lulus' : 'Tidak Lulus';
            }
        );
    }

    protected function statusNilai(): Attribute
    {
        return $this->statusKelulusan();
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}