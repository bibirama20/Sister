<?php
require __DIR__ . '/vendor/autoload.php';

use App\Database\Database;
use App\Services\SupabaseService;

$db = Database::connect();

// Ambil data queue yang sudah waktunya dikirim
$stmt = $db->query("
    SELECT * FROM queue_mahasiswa
    WHERE status = 'pending'
      AND execute_at <= NOW()
");

$queues = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($queues as $row) {

    // payload dari MySQL
    $payload = json_decode($row['payload'], true);

    // 🔥 KIRIM KE SUPABASE (INI INTINYA)
    $sent = SupabaseService::send([
        'nim'        => $payload['nim'],
        'nama'       => $payload['nama'],
        'prodi'      => $payload['prodi'] ?? null,
        'angkatan'   => $payload['angkatan'] ?? null,
        'mode'       => $payload['mode'],
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // jika sukses → update status
    if ($sent) {
        $update = $db->prepare("
            UPDATE queue_mahasiswa
            SET status = 'sent'
            WHERE id = ?
        ");
        $update->execute([$row['id']]);
    }
}

echo "Queue sync finished\n";
