<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('shipments') && !Schema::hasColumn('shipments', 'deleted_at')) {
            Schema::table('shipments', function (Blueprint $table) { $table->softDeletes(); });
        }
        if (Schema::hasTable('locations') && !Schema::hasColumn('locations', 'is_active')) {
            Schema::table('locations', function (Blueprint $table) { $table->boolean('is_active')->default(true)->after('location_type'); });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('locations') && Schema::hasColumn('locations', 'is_active')) {
            Schema::table('locations', function (Blueprint $table) { $table->dropColumn('is_active'); });
        }
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'deleted_at')) {
            Schema::table('shipments', function (Blueprint $table) { $table->dropSoftDeletes(); });
        }
    }
};
