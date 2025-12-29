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
        Schema::create('rekon_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('rekon_projects')->onDelete('cascade');
            $table->string('mitra_name');
            $table->string('lop_name');
            $table->string('designator');
            $table->decimal('qty_gudang', 15, 2)->default(0);
            $table->decimal('qty_ta', 15, 2)->default(0);
            $table->decimal('qty_mitra', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['project_id', 'mitra_name']);
            $table->index(['project_id', 'mitra_name', 'lop_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekon_items');
    }
};
