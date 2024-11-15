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
        Schema::create('account_details', function (Blueprint $table) {
            $table->id('acc_id');
            $table->unsignedBigInteger('acc_merchant_id'); // Foreign key to merchant_infos
            $table->unsignedBigInteger('acc_business_id'); // Foreign key to business_details
            $table->string('acc_account_number', 20)->unique(); // Bank account number
            $table->string('acc_bank_name', 100); // Name of the bank
            $table->string('acc_branch_name', 100)->nullable(); // Bank branch name
            $table->string('acc_ifsc_code', 15); // IFSC code
            $table->string('acc_micr_code', 15)->nullable(); // Optional MICR code
            $table->string('acc_swift_code', 15)->nullable(); // Optional SWIFT code
            $table->enum('acc_account_type', ['Savings', 'Current', 'Business', 'Other'])->default('Savings'); // Account type
            $table->enum('acc_status', ['Active', 'Inactive', 'Suspended', 'Closed'])->default('Active'); // Account status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_details');
    }
};
