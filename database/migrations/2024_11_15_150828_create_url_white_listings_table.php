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
        Schema::create('url_white_listings', function (Blueprint $table) {
            $table->id('uwl_id');
            $table->unsignedBigInteger('uwl_merchant_id');
            $table->string('uwl_url', 255);
            $table->string('uwl_ip_address', 45)->nullable();
            $table->enum('uwl_status', ['Active', 'Inactive'])->default('Active');
            $table->enum('uwl_environment', ['Production', 'UAT'])->default('Production'); // Environment column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_white_listings');
    }
};
