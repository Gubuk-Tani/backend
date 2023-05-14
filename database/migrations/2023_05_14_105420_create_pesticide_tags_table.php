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
        Schema::create('pesticide_tags', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tag_id')->unsigned();
            $table->bigInteger('pesticide_id')->unsigned();

            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('no action');
            $table->foreign('pesticide_id')->references('id')->on('pesticides')->onUpdate('cascade')->onDelete('no action');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perticide_tags');
    }
};
