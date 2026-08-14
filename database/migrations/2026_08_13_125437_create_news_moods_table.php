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
        Schema::create('news_moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')
                    ->constrained('news')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            $table->string('mood')->enum('positive', 'negative', 'neutral', 'ironic');
            $table->string('text', length: 5000);
            $table->string('facts', length: 2000);
            $table->timestamps();
            $table->unique(['news_id','mood']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_moods');
    }
};
