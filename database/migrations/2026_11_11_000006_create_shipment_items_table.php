<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('item_name', 150);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 30)->default('pcs');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['shipment_id', 'item_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
