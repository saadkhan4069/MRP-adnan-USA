<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAppointmentsAddCustomerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add customer_id column
            $table->unsignedBigInteger('customer_id')->nullable()->after('email');
            
            // Drop customer_type column if exists (or you can keep it and set default)
            // Uncomment below line if you want to remove customer_type completely
            // $table->dropColumn('customer_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            // If you dropped customer_type, add it back here
            // $table->string('customer_type')->nullable();
        });
    }
}

