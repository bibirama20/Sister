<?php
namespace App\Controllers;

use GuzzleHttp\Client;
use App\Services\SupabaseService;

class MahasiswaController
{
    private static function client()
    {
        return new Client([
            'base_uri' => getenv('SUPABASE_URL') . '/rest/v1/',
            'headers' => [
                'apikey'        => getenv('SUPABASE_KEY'),
                'Authorization' => 'Bearer ' . getenv('SUPABASE_KEY'),
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
        // ============================
        // 1️⃣ STRONG CONSISTENCY
        // ============================
        if ($data['mode'] === 'strong') {
            self::sendToCloud($data);
            return;
        }

        // ============================
        // 2️⃣ EVENTUAL & GUID → FILE QUEUE
        // ============================
        $executeAt = match ($data['mode']) {
            'eventual' => time() + 120,              // 2 menit
            'guid'     => strtotime('today 23:59'),
            default    => throw new \Exception('Mode tidak valid')
        };

        self::saveToQueue($data, $executeAt);
    }

    // ============================
    // READ DATA (DARI SUPABASE)
    // ============================
    public static function getAll(): array
    {
        $response = self::client()->get('mahasiswa?select=*');
        return json_decode($response->getBody()->getContents(), true);
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
    // SIMPAN KE QUEUE LOKAL (SERVERLESS SAFE)
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
            'execute_at' => $executeAt,
            'payload'    => $payload
        ];

        file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
    }

    // ============================
    // PROSES QUEUE (DIPANGGIL MANUAL / CRON)
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
