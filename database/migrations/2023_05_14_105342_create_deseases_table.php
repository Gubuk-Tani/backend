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
        Schema::create('deseases', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('description');
            $table->string('image')->nullable();
            $table->bigInteger('article_id')->unsigned();

            $table->foreign('article_id')->references('id')->on('articles')->onUpdate('cascade')->onDelete('no action');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deseases');
    }
};
