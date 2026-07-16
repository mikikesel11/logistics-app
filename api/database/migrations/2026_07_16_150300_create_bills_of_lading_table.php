<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Bill of Lading snapshots the load's parties and freight at generation
     * time — it is a legal document and must not change if the load later does.
     */
    public function up(): void
    {
        Schema::create('bills_of_lading', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('load_id')->constrained()->cascadeOnDelete();
            $table->string('bol_number');

            $table->string('customer_name')->nullable();
            $table->string('carrier_name')->nullable();
            $table->json('ship_from')->nullable();   // origin address snapshot
            $table->json('ship_to')->nullable();     // destination address snapshot
            $table->json('freight')->nullable();     // line-item snapshot
            $table->text('special_instructions')->nullable();

            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'bol_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills_of_lading');
    }
};
