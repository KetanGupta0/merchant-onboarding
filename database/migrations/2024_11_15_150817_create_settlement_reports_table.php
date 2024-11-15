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
        Schema::create('settlement_reports', function (Blueprint $table) {
            $table->id('srt_id');
            $table->unsignedBigInteger('srt_merchant_id');
            $table->unsignedBigInteger('srt_business_id');
            $table->string('srt_transaction_id', 50)->unique();
            $table->decimal('srt_amount', 10, 2);
            $table->enum('srt_status', ['Pending', 'Processed', 'Failed'])->default('Pending');
            $table->date('srt_settlement_date');
            $table->string('srt_remarks', 255)->nullable();
            $table->enum('srt_environment', ['Production', 'UAT'])->default('Production'); // Environment column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_reports');
    }
};
