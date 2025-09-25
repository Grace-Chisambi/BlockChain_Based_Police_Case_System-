<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('blockchain_logs', function (Blueprint $table) {
        $table->id();
        $table->string('case_id')->nullable();
        $table->string('tx_hash')->unique();
        $table->string('action_type'); // e.g. 'logCaseClosure', 'logAssignmentHash'
        $table->text('payload')->nullable(); // store any extra info (JSON)
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_blockchain_logs');
    }
};
