<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->nullable(); // human-facing load #
            $table->string('status')->default('quoted')->index();

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('origin_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('commodity')->nullable();
            $table->unsignedInteger('weight_lbs')->nullable();
            $table->date('pickup_date')->nullable();
            $table->date('delivery_date')->nullable();

            // Money in cents to avoid float drift. customer_rate = revenue,
            // carrier_cost = what we pay the trucker; margin is derived.
            $table->unsignedBigInteger('customer_rate_cents')->default(0);
            $table->unsignedBigInteger('carrier_cost_cents')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
