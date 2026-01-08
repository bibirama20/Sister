<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => $_ENV['SUPABASE_URL'] . '/rest/v1/',
    'headers' => [
        'apikey' => $_ENV['SUPABASE_KEY'],
        'Authorization' => 'Bearer ' . $_ENV['SUPABASE_KEY'],
        'Content-Type' => 'application/json'
    ]
]);

$data = $pdo->query(
    "SELECT * FROM mahasiswa_local WHERE synced = 0"
)->fetchAll();

foreach ($data as $row) {
    $client->post('mahasiswa', [
        'json' => [
            'guid' => $row['guid'],
            'nama' => $row['nama'],
            'nim'  => $row['nim'],
            'jurusan' => $row['jurusan']
        ]
    ]);

    $pdo->prepare(
        "UPDATE mahasiswa_local SET synced = 1 WHERE id = ?"
    )->execute([$row['id']]);
}

echo "Sinkronisasi ke Supabase selesai";
