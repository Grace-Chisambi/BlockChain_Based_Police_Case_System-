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
        Schema::create('case_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('staff_id'); 
            $table->string('role');
            $table->timestamps();

            $table->foreign('case_id')->references('case_id')->on('cases')->onDelete('cascade');
            $table->foreign('staff_id')->references('staff_id')->on('police_staff')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_case_assignment');
    }
};
