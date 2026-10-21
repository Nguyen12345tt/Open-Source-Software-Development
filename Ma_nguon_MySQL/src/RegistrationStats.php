<?php

declare(strict_types=1);
require_once __DIR__ . '/Database.php';
function registrationStats(mysqli $connection, int $eventId): array
{
    // TODO: SELECT event + COUNT registrations with LEFT JOIN.
    // Return ['event_id'=>int, 'title'=>string, 'total'=>int]. Unknown -> [].
    $result = queryParams(
        $connection,
        'SELECT e.id AS event_id, e.title, COUNT(r.id) AS total
         FROM events e
         LEFT JOIN registrations r ON r.event_id = e.id
         WHERE e.id = ?
         GROUP BY e.id, e.title',
        [$eventId]
    );
    try {
        $row = $result->get_result()->fetch_assoc();
    } finally {
        $result->close();
    }
    if ($row === null) {
        return [];
    }
    return [
        'event_id' => (int)$row['event_id'],
        'title' => (string)$row['title'],
        'total' => (int)$row['total'],
    ];
}
