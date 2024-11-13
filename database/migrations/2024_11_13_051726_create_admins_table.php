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
        Schema::create('admins', function (Blueprint $table) {
            $table->id('admin_id');
            $table->string('admin_name',100)->nullable(false);
            $table->string('admin_phone',15)->nullable(false);
            $table->string('admin_phone2',15)->nullable(true);
            $table->string('admin_email',100)->nullable(false);
            $table->bigInteger('admin_business_id')->unsigned()->nullable(true);
            $table->string('admin_profile_pic')->nullable(true);
            $table->string('admin_city',50)->nullable(true);
            $table->string('admin_state',50)->nullable(true);
            $table->string('admin_country',50)->nullable(true);
            $table->integer('admin_zip_code')->nullable(true);
            $table->string('admin_landmark',150)->nullable(true);
            $table->string('admin_password')->nullable(false);
            $table->string('admin_plain_password')->nullable(false);
            $table->enum('admin_type',['Super Admin','Admin','Sub Admin'])->nullable(false)->default('Sub Admin');
            $table->enum('admin_status',['Active','Blocked','Deleted'])->nullable(false)->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
