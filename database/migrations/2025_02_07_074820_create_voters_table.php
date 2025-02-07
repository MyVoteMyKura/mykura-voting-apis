<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.'name',
     */
    public function up(): void
    {
        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->text('phone');
            $table->text('pfNumber');
            $table->text('email');
            $table->boolean('email_verified')->default(false);
            $table->text('google_id');
            $table->text('picture_url');
            $table->text('ip_address');
            $table->text('inline_url');
            $table->text('secret');  

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};
