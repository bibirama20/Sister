<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\MahasiswaController;

// Load ENV
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$message = '';

// Routing sederhana
$page = $_GET['page'] ?? 'form';

// Simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    MahasiswaController::store($_POST);
    $message = 'Data mahasiswa berhasil disimpan';
}

// Tampilkan view data
if ($page === 'view') {
    $mahasiswa = MahasiswaController::getAll();
    require __DIR__ . '/../app/Views/view.php';
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Mahasiswa | Sistem Terdistribusi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 32px;
            width: 100%;
            max-width: 480px;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        h1 { margin-bottom: 6px; }
        p { color: #6b7280; font-size: 14px; }
        label { font-size: 13px; font-weight: 500; }
        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            margin-bottom: 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }
        button, .btn-link {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-link {
            background: #f3f4f6;
            color: #2563eb;
            demonstrated: block;
            margin-top: 10px;
        }
        .success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Input Mahasiswa</h1>
    <p>Sistem Terdistribusi – Supabase Cloud</p>

    <?php if ($message): ?>
        <div class="success"><?= $message ?></div>
        <a href="view.php" class="btn-link">📊 Lihat Data Mahasiswa</a>
    <?php endif; ?>

    <form method="POST">
        <label>NIM</label>
        <input name="nim" required>

        <label>Nama</label>
        <input name="nama" required>

        <label>Program Studi</label>
        <input name="prodi">

        <label>Angkatan</label>
        <input name="angkatan" type="number">

        <label>Mode Konsistensi</label>
        <select name="mode">
            <option value="strong">Strong Consistency</option>
            <option value="eventual">Eventual Consistency</option>
            <option value="guid">GUID Scheduled</option>
        </select>

        <button type="submit">💾 Simpan Data</button>
    </form>

    <a href="view.php" class="btn-link">📄 Lihat Data Mahasiswa</a>
</div>

</body>
</html>
