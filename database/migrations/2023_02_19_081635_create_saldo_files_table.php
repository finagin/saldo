<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saldo_files', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('saldo_id');

            $table->string('name', 1024); // 1024 characters is max length for file name
            $table->string('disk', 32)->nullable();
            $table->string('path', 1024);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_files');
    }
};
