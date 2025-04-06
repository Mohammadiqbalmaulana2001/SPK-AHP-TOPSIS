<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_kriterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_id')
                  ->constrained('kriterias')
                  ->onDelete('cascade');
            $table->foreignId('kategori_id')
                  ->constrained('kategoris')
                  ->onDelete('cascade');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->decimal('bobot', 5, 2)->default(0);
            $table->decimal('nilai', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_kriterias');
    }
};