<?php
namespace App\Enums;
enum ShipmentStatus: string {
    case CREATED='CREATED'; case BOOKED='BOOKED'; case PICKED_UP='PICKED_UP'; case IN_TRANSIT='IN_TRANSIT'; case DELIVERED='DELIVERED';
    public function next(): ?self { return match($this) { self::CREATED=>self::BOOKED,self::BOOKED=>self::PICKED_UP,self::PICKED_UP=>self::IN_TRANSIT,self::IN_TRANSIT=>self::DELIVERED,self::DELIVERED=>null }; }
    public function canTransitionTo(self $next): bool { return $this->next() === $next; }
}
