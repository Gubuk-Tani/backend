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
        Schema::create('detections', function (Blueprint $table) {
            $table->id();

            $table->string('image');
            $table->string('result')->nullable();
            $table->string('confidence')->nullable();
            $table->bigInteger('plant_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();

            $table->foreign('plant_id')->references('id')->on('plants')->onUpdate('cascade')->onDelete('no action');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('no action');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};
