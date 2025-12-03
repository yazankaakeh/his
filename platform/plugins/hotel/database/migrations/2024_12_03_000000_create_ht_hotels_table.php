<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ht_hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ht_hotels');
    }
};
