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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('api_id', length: 128)->unique();
            $table->string('title', length: 512);
            $table->string('text', length: 5000);
            $table->string('source', length: 1024);
            $table->string('facts', length: 2000);
            $table->string('status')->enum('no_mod', 'mod');
            $table->string('published_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
