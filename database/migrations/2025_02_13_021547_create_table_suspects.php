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
        Schema::create('suspects', function (Blueprint $table) {
            $table->id('suspect_id');
            $table->unsignedBigInteger('case_id');
            $table->string('name');
            $table->integer('age');
            $table->string('village');
            $table->string('job');
            $table->string('phone_number');
            $table->text('statement'); // Additional suspect details
            $table->string('status')->default('At Large');
            $table->timestamps();
        
            $table->foreign('case_id')->references('case_id')->on('cases')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_suspects');
    }
};
