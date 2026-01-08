<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\MahasiswaController;

if (file_exists(__DIR__ . '/../.env')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    } catch (Throwable $e) {
    }
}

// ================================
// AMBIL DATA DARI SUPABASE
// ================================
try {
    $data = MahasiswaController::getAll();
    if (!is_array($data)) {
        $data = [];
    }
} catch (Throwable $e) {
    $data = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa | Sistem Terdistribusi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f3f4f6;
            font-family: 'Inter', sans-serif;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }
        h1 { color: #111827; }
        p { color: #6b7280; font-size: 14px; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        thead { background: #2563eb; color: #fff; }
        th, td { padding: 12px 14px; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:hover { background: #eef2ff; }

        .badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .strong { background: #dcfce7; color: #166534; }
        .eventual { background: #fef3c7; color: #92400e; }
        .guid { background: #e0e7ff; color: #3730a3; }

        .btn {
            background: #2563eb;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Data Mahasiswa</h1>
    <p>Hasil input dan replikasi data pada sistem terdistribusi (Supabase Cloud)</p>

    <a href="index.php" class="btn">+ Input Data Baru</a>

    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Prodi</th>
            <th>Angkatan</th>
            <th>Mode</th>
            <th>Waktu</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($row['nim'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['prodi'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['angkatan'] ?? '-') ?></td>
                <td>
                    <span class="badge <?= htmlspecialchars($row['mode'] ?? '') ?>">
                        <?= strtoupper($row['mode'] ?? '-') ?>
                    </span>
                </td>
                <td>
                    <?= isset($row['created_at'])
                        ? date('d-m-Y H:i', strtotime($row['created_at']))
                        : '-' ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <footer>© <?= date('Y') ?> Tugas Akhir Sistem Terdistribusi</footer>
</div>

</body>
</html>
