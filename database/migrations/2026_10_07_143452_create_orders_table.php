<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 30)->unique();
            $table->unsignedBigInteger('patient_id');
            $table->string('pt_code', 20);
            $table->string('st_id', 20);
            $table->string('ua_user_id', 30);
            $table->dateTime('received_at');
            $table->dateTime('released_at')->nullable();
            $table->enum('status', ['PENDING','PARTIAL','COMPLETED','CANCELLED'])->default('PENDING');
            $table->enum('priority', ['Routine','STAT','ASAP'])->default('Routine');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('pt_code')->references('pt_code')->on('patient_types');
            $table->foreign('st_id')->references('st_id')->on('source_table');
            $table->foreign('ua_user_id')->references('ua_user_id')->on('user_accounts');

            $table->index('received_at');
            $table->index('released_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};