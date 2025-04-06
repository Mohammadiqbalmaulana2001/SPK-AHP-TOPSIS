<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternatif_id')
                  ->constrained('alternatifs')
                  ->onDelete('cascade');
            $table->foreignId('sub_kriteria_id')
                  ->constrained('sub_kriterias')
                  ->onDelete('cascade');
            $table->decimal('nilai', 10, 2);
            $table->timestamps();

            // Ensure unique combination of alternatif and sub kriteria
            $table->unique(['alternatif_id', 'sub_kriteria_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('penilaians');
    }
};