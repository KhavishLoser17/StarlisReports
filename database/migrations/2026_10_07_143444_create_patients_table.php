<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_no', 30)->unique();
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['M','F'])->nullable();
            $table->string('address', 255)->nullable();
            $table->string('contact_no', 30)->nullable();
            $table->timestamps();
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};