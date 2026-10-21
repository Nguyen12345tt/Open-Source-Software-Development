<?php
declare(strict_types=1);
function canComplete(array $session, int $requestedEventId, int $now, int $ttl): bool {
    if ($ttl <= 0 || $requestedEventId <= 0) {
        return false;
    }
    if (!isset($session['draft']) || !is_array($session['draft'])) {
        return false;
    }
    $draft = $session['draft'];
    if (!isset($draft['data'], $draft['created_at']) || !is_array($draft['data']) || !is_int($draft['created_at'])) {
        return false;
    }
    $eventId = $draft['data']['event_id'] ?? null;
    if (!is_int($eventId) || $eventId !== $requestedEventId) {
        return false;
    }
    $createdAt = $draft['created_at'];
    if ($createdAt > $now) {
        return false;
    }
    return ($now - $createdAt) < $ttl;
}
