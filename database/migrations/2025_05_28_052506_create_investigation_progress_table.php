<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvestigationProgressTable extends Migration
{
    public function up()
    {
        Schema::create('investigation_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign Keys
      
            $table->foreign('case_id')->references('case_id')->on('cases')->onDelete('cascade');
            $table->foreign('staff_id')->references('staff_id')->on('police_staff')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('investigation_progress');
    }
}
