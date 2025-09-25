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
        Schema::create('cases', function (Blueprint $table) {
            $table->id('case_id');
            $table->unsignedBigInteger('complaint_id');
            $table->string('case_number')->unique();
            $table->string('case_type'); // New column added here
            $table->string('case_status')->default('Open');
            $table->timestamps();

            $table->foreign('complaint_id')->references('complaint_id')->on('complaints')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases'); // Fixed table name here
    }
};
