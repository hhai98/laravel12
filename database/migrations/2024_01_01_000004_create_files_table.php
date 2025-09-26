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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->string('thumb')->nullable();
            $table->string('type')->default('image'); // image, document, video, etc.
            $table->unsignedBigInteger('fileable_id')->nullable();
            $table->string('fileable_type')->nullable();
            $table->timestamps();

            $table->index(['fileable_id', 'fileable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
