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
        Schema::create('rekon_telkom', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('rekon_projects')->onDelete('cascade');
        $table->string('no_layanan');
        $table->string('nama_pelanggan')->nullable();
        $table->decimal('nilai', 15, 2)->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekon_telkom');
    }
};
