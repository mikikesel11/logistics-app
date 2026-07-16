<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('load_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('pieces')->default(1);
            $table->unsignedInteger('weight_lbs')->nullable();
            $table->string('freight_class', 20)->nullable(); // NMFC class, e.g. "70"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_items');
    }
};
