<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Added as a nullable, indexed column without a DB-level foreign key so the
     * migration stays portable to SQLite (which cannot add FK constraints via
     * ALTER TABLE). The relationship is enforced at the application layer.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('organization_id');
        });
    }
};
