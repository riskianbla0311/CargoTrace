<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('shipments')) return;
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'shipment_type')) $table->string('shipment_type', 20)->default('DOMESTIC');
            if (!Schema::hasColumn('shipments', 'hs_code')) $table->string('hs_code', 30)->nullable();
            if (!Schema::hasColumn('shipments', 'incoterms')) $table->string('incoterms', 10)->nullable();
            if (!Schema::hasColumn('shipments', 'customs_status')) $table->string('customs_status', 30)->nullable();
            if (!Schema::hasColumn('shipments', 'customs_reference')) $table->string('customs_reference', 100)->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('shipments')) return;
        Schema::table('shipments', function (Blueprint $table) {
            foreach (['shipment_type','hs_code','incoterms','customs_status','customs_reference'] as $column) {
                if (Schema::hasColumn('shipments', $column)) $table->dropColumn($column);
            }
        });
    }
};
