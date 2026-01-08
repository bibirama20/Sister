<?php
namespace App\Services;

use App\Database\Database;

class MysqlService
{
    public static function insertMahasiswa(array $data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO mahasiswa (nim, nama, prodi, angkatan, mode, created_at)
            VALUES (:nim, :nama, :prodi, :angkatan, :mode, :created_at)
        ");

        $stmt->execute($data);
    }

    public static function addQueue(array $payload, string $executeAt)
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO queue_jobs (payload, execute_at)
            VALUES (:payload, :execute_at)
        ");

        $stmt->execute([
            'payload' => json_encode($payload),
            'execute_at' => $executeAt
        ]);
    }

    public static function getPendingJobs()
    {
        $db = Database::connect();

        return $db->query("
            SELECT * FROM queue_jobs
            WHERE status='pending' AND execute_at <= NOW()
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function markDone($id)
    {
        $db = Database::connect();
        $db->prepare("UPDATE queue_jobs SET status='done' WHERE id=?")
           ->execute([$id]);
    }
}
