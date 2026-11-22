<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'nilai')) {
                $table->unsignedTinyInteger('nilai')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'catatan_dosen')) {
                $table->text('catatan_dosen')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['nilai', 'catatan_dosen']);
        });
    }
};