<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoliceStaffTable extends Migration
{
    public function up()
    {
        Schema::create('police_staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id');
            $table->boolean('available')->default(true);
            $table->string('specialization')->nullable();
            $table->timestamps();


            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('cascade');


        });
    }

    public function down()
    {
        Schema::dropIfExists('police_staff');
    }
}
