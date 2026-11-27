<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_legs', function (Blueprint $table) {
            $table->id();

            // Relasi ke shipment utama
            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            // Urutan perjalanan dalam satu shipment
            $table->unsignedInteger('leg_number');

            // Jenis transportasi: LAND, SEA, AIR
            $table->string('transport_mode', 20);

            // Perusahaan pengiriman untuk tahap ini
            $table->foreignId('carrier_id')
                ->nullable()
                ->constrained('carriers')
                ->nullOnDelete();

            // Lokasi asal dan tujuan
            $table->foreignId('origin_id')
                ->constrained('locations')
                ->restrictOnDelete();

            $table->foreignId('destination_id')
                ->constrained('locations')
                ->restrictOnDelete();

            // Jadwal keberangkatan dan kedatangan
            $table->dateTime('expected_departure_at')->nullable();
            $table->dateTime('expected_arrival_at')->nullable();

            // Waktu aktual
            $table->dateTime('actual_departure_at')->nullable();
            $table->dateTime('actual_arrival_at')->nullable();

            // Status tahap perjalanan
            $table->string('status', 30)->default('PLANNED');

            // Keterangan tambahan
            $table->text('notes')->nullable();

            $table->timestamps();

            // Satu shipment tidak boleh memiliki nomor tahap yang sama
            $table->unique(
                ['shipment_id', 'leg_number'],
                'shipment_leg_number_unique'
            );

            $table->index(['shipment_id', 'status']);
            $table->index('transport_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_legs');
    }
};
