<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Services\SupabaseService;

$db = Database::connect();

$q = $db->query("
    SELECT * FROM sync_queue
    WHERE status='pending' AND execute_at <= NOW()
");

while ($row = $q->fetch()) {
    $payload = json_decode($row['payload'], true);

    if (SupabaseService::send($payload)) {
        $db->prepare("
            UPDATE sync_queue SET status='done' WHERE id=?
        ")->execute([$row['id']]);
    }
}

