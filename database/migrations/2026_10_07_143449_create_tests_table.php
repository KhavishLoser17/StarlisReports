<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->string('test_code', 20)->primary();
            $table->string('test_name', 150);
            $table->string('tg_code', 20);
            $table->integer('test_tat_hours')->default(24);
            $table->enum('test_active', ['Y','N'])->default('Y');
            $table->timestamps();

            $table->foreign('tg_code')->references('tg_code')->on('test_groups');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};