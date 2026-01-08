<?php
namespace App\Controllers;

use GuzzleHttp\Client;
use App\Services\MysqlService;
use App\Services\SupabaseService;
use App\Database\Database;

class MahasiswaController
{
    private static function client()
    {
        return new Client([
            'base_uri' => $_ENV['SUPABASE_URL'] . '/rest/v1/',
            'headers' => [
                'apikey'        => $_ENV['SUPABASE_KEY'],
                'Authorization' => 'Bearer ' . $_ENV['SUPABASE_KEY'],
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=minimal'
            ]
        ]);
    }

    // ============================
    // STORE DATA (BERDASARKAN MODE)
    // ============================
 public static function store(array $data): void
    {
        $db = Database::connect();

        // ============================
        // 1️⃣ SIMPAN KE MYSQL (PRIMARY NODE)
        // ============================
        $stmt = $db->prepare("
            INSERT INTO mahasiswa (nim, nama, prodi, angkatan, mode)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nim'],
            $data['nama'],
            $data['prodi'] ?? null,
            $data['angkatan'] ?? null,
            $data['mode']
        ]);

        // ============================
        // 2️⃣ STRONG CONSISTENCY
        // ============================
        if ($data['mode'] === 'strong') {
            SupabaseService::send($data);
            return;
        }

        // ============================
        // 3️⃣ EVENTUAL & GUID → QUEUE MYSQL
        // ============================
        $executeAt = match ($data['mode']) {
            'eventual' => date('Y-m-d H:i:s', time() + 120),
            'guid'     => date('Y-m-d 23:59:59'),
            default    => throw new \Exception('Mode tidak valid')
        };

        $stmt = $db->prepare("
            INSERT INTO sync_queue (payload, mode, execute_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            json_encode($data),
            $data['mode'],
            $executeAt
        ]);
    }

    // ============================
    // READ DATA (DARI MYSQL)
    // ============================
    public static function getAll(): array
    {
        $db = Database::connect();

        $stmt = $db->query("
            SELECT * FROM mahasiswa
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ============================
    // KIRIM KE SUPABASE
    // ============================
    private static function sendToCloud($payload)
    {
        self::client()->post('mahasiswa', [
            'json' => $payload
        ]);
    }

    // ============================
    // SIMPAN KE QUEUE LOKAL
    // ============================
   private static function saveToQueue($payload, $executeAt = null)
{
    $storageDir = __DIR__ . '/../../storage';
    $queueFile  = $storageDir . '/queue.json';

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0777, true);
    }

    if (!file_exists($queueFile)) {
        file_put_contents($queueFile, json_encode([]));
    }

    $queue = json_decode(file_get_contents($queueFile), true);

    $queue[] = [
        'execute_at' => $executeAt ?? (time() + 120), // default 2 menit
        'payload'    => $payload
    ];

    file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
}

private static function runBackgroundWorker()
{
    $phpPath = 'C:\\xampp\\php\\php.exe';
    $worker  = realpath(__DIR__ . '/../../process_queue.php');

    // Windows background execution
    pclose(popen(
        "start /B \"queue\" \"$phpPath\" \"$worker\"",
        "r"
    ));
}



    // ============================
    // PROSES QUEUE (CRON)
    // ============================
    public static function processQueue()
    {
        $queueFile = __DIR__ . '/../../storage/queue.json';
        if (!file_exists($queueFile)) return;

        $queue = json_decode(file_get_contents($queueFile), true);
        $now = time();
        $remaining = [];

        foreach ($queue as $job) {
            if ($job['execute_at'] <= $now) {
                self::sendToCloud($job['payload']);
            } else {
                $remaining[] = $job;
            }
        }

        file_put_contents($queueFile, json_encode($remaining, JSON_PRETTY_PRINT));
    }
}
