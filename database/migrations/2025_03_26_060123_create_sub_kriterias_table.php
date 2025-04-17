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
            $table->enum('tipe', ['benefit', 'cost'])->default('benefit');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->decimal('bobot')->default(0);
            $table->decimal('bobot_global')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_kriterias');
    }
};