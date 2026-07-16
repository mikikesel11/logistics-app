<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('mc_number')->nullable();  // Motor Carrier number
            $table->string('dot_number')->nullable(); // USDOT number
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('insurance_expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // MC numbers are unique per organization, not globally (multi-tenant).
            $table->unique(['organization_id', 'mc_number']);
            $table->index(['organization_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
