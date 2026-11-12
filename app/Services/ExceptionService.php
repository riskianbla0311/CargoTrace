<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Support\Facades\DB;

class ExceptionService
{
    public function create(Shipment $shipment, array $data, int $actorId): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $actorId) {
            $exception = $shipment->exceptions()->create($data + [
                'status' => 'OPEN',
                'detected_at' => now(),
                'created_by' => $actorId,
            ]);

            AuditLog::create([
                'user_id' => $actorId,
                'action' => 'exception.created',
                'entity_type' => ShipmentException::class,
                'entity_id' => $exception->id,
                'details' => ['shipment_id' => $shipment->id, 'type' => $exception->type, 'severity' => $exception->severity],
                'created_at' => now(),
            ]);

            $shipment->loadMissing('customer');
            Notification::create([
                'user_id' => $shipment->customer_id,
                'title' => 'Shipment exception reported',
                'message' => 'An issue was reported for shipment '.$shipment->shipment_number.': '.str_replace('_', ' ', $exception->type),
                'type' => 'exception',
                'data' => ['shipment_id' => $shipment->id, 'exception_id' => $exception->id],
            ]);

            return $exception;
        });
    }

    public function update(ShipmentException $exception, array $data, int $actorId): ShipmentException
    {
        return DB::transaction(function () use ($exception, $data, $actorId) {
            if ($data['status'] === 'RESOLVED') {
                $data['resolved_at'] = now();
            } else {
                $data['resolved_at'] = null;
            }

            $exception->update($data);

            AuditLog::create([
                'user_id' => $actorId,
                'action' => 'exception.updated',
                'entity_type' => ShipmentException::class,
                'entity_id' => $exception->id,
                'details' => ['status' => $exception->status],
                'created_at' => now(),
            ]);

            $exception->loadMissing('shipment');
            if ($exception->shipment) {
                Notification::create([
                    'user_id' => $exception->shipment->customer_id,
                    'title' => 'Shipment exception updated',
                    'message' => 'The exception for shipment '.$exception->shipment->shipment_number.' is now '.$exception->status.'.',
                    'type' => 'exception',
                    'data' => ['shipment_id' => $exception->shipment_id, 'exception_id' => $exception->id],
                ]);
            }

            return $exception->refresh();
        });
    }
}
