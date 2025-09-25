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
        Schema::create('case_closures', function (Blueprint $table) {
            $table->id('closure_id');
            $table->unsignedBigInteger('case_id');
            $table->text('reason');
            $table->date('closure_date');
            $table->timestamps();

            $table->foreign('case_id')->references('case_id')->on('cases')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_case_closure');
    }
};
