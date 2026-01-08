<?php
require __DIR__ . '/bootstrap.php';
use App\Database\Database;
use App\Services\SupabaseService;

$db = Database::connect();
$q = $db->query("SELECT * FROM mahasiswa WHERE mode='eventual' AND status_sync='pending'");

while ($m = $q->fetch()) {
    SupabaseService::insertMahasiswa($m);
    $db->prepare("UPDATE mahasiswa SET status_sync='synced' WHERE id=?")
       ->execute([$m['id']]);
}
