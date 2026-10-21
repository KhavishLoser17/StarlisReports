<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('specimen_id', 30)->nullable();
            $table->string('test_code', 20);
            $table->dateTime('received_at');
            $table->dateTime('resulted_at')->nullable();
            $table->dateTime('released_at')->nullable();
            $table->string('resulted_by', 30)->nullable();
            $table->string('verified_by', 30)->nullable();
            $table->integer('tat_minutes')->nullable();
            $table->enum('status', ['PENDING','RESULTED','VERIFIED','RELEASED','CANCELLED'])->default('PENDING');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('test_code')->references('test_code')->on('tests');

            $table->index('order_id');
            $table->index('test_code');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tests');
    }
};