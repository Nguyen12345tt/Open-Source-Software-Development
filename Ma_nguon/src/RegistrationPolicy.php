<?php
declare(strict_types=1);

final class RegistrationPolicy
{
    private string $lastReason = '';

    public function decide(
        int $capacity,
        int $registered,
        bool $open,
        bool $waitingEnabled
    ): string {
        if ($capacity <= 0) {
            $this->lastReason = 'invalid_capacity';
            return 'rejected';
        }

        if ($registered < 0 || $registered > $capacity) {
            $this->lastReason = 'invalid_registered_count';
            return 'rejected';
        }

        if (!$open) {
            $this->lastReason = 'event_closed';
            return 'rejected';
        }

        if ($registered < $capacity) {
            $this->lastReason = 'seat_available';
            return 'confirmed';
        }

        if ($waitingEnabled) {
            $this->lastReason = 'waitlist_enabled';
            return 'waiting';
        }

        $this->lastReason = 'full_no_waitlist';
        return 'rejected';
    }

    public function getLastReason(): string
    {
        return $this->lastReason;
    }

    /**
     * @return array{status: string, reason: string}
     */
    public function decideWithReason(
        int $capacity,
        int $registered,
        bool $open,
        bool $waitingEnabled
    ): array {
        $status = $this->decide($capacity, $registered, $open, $waitingEnabled);
        return [
            'status' => $status,
            'reason' => $this->lastReason,
        ];
    }
}
