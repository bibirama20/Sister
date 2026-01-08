<?php
namespace App\Services;

use GuzzleHttp\Client;

class SupabaseService
{
    private static function client()
    {
        return new Client([
            'base_uri' => rtrim($_ENV['SUPABASE_URL'], '/') . '/rest/v1/',
            'headers' => [
                'apikey'        => $_ENV['SUPABASE_KEY'],
                'Authorization' => 'Bearer ' . $_ENV['SUPABASE_KEY'],
                'Content-Type'  => 'application/json',
                'Prefer'        => 'return=representation'
            ],
            'http_errors' => false // ⬅️ JANGAN THROW, BIAR KITA BACA RESPONSE
        ]);
    }

    public static function send(array $payload): bool
{
    $response = self::client()->post('mahasiswa', [
        'json' => [
            'nim'      => $payload['nim'],
            'nama'     => $payload['nama'],
            'prodi'    => $payload['prodi'] ?? null,
            'angkatan' => $payload['angkatan'] ?? null,
            'mode'     => $payload['mode']
        ]
    ]);

    $status = $response->getStatusCode();
    $body   = (string) $response->getBody();

    if ($status >= 300) {
        file_put_contents(
            __DIR__ . '/../../storage/supabase_error.log',
            date('Y-m-d H:i:s') .
            " | STATUS: $status | BODY: $body\n",
            FILE_APPEND
        );
        return false;
    }

    return true;
}

    // ALIAS
    public static function insertMahasiswa(array $data): bool
    {
        return self::send($data);
    }
}
