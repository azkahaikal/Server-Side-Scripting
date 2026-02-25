<?php
require 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npm     = trim($_POST['npm']     ?? '');
    $nama    = trim($_POST['nama']    ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');

    // Validasi server-side
    if (empty($npm) || empty($nama) || empty($jurusan)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!preg_match('/^\d{10,12}$/', $npm)) {
        $error = 'NPM harus berupa 10–12 digit angka.';
    } else {
        // Cek NPM duplikat
        $cek = $pdo->prepare("SELECT id FROM mahasiswa WHERE npm = ?");
        $cek->execute([$npm]);
        if ($cek->fetch()) {
            $error = 'NPM sudah terdaftar, gunakan NPM yang berbeda.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO mahasiswa (npm, nama, jurusan) VALUES (?, ?, ?)");
            if ($stmt->execute([$npm, $nama, $jurusan])) {
                header("Location: index.php?success=tambah");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">

    <header class="site-header animate-in">
        <div>
            <h1 class="site-title">Sistem <span>Akademik</span></h1>
            <p class="site-subtitle">Manajemen Data Mahasiswa — Universitas</p>
        </div>
    </header>

    <div class="form-page animate-in" style="animation-delay:0.04s">

        <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span class="sep">›</span>
            <span class="current">Tambah Mahasiswa</span>
        </div>

        <div class="form-header">
            <h2>Tambah <span style="color:var(--accent)">Mahasiswa</span> Baru</h2>
            <p>Isi formulir di bawah untuk mendaftarkan mahasiswa baru ke sistem.</p>
        </div>

        <?php if ($error): ?>
        <div class="flash flash-error animate-in">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="npm">NPM (Nomor Pokok Mahasiswa)</label>
                    <input 
                        type="text" 
                        id="npm"
                        name="npm" 
                        class="form-control" 
                        placeholder="Contoh: 220401010001"
                        value="<?= htmlspecialchars($_POST['npm'] ?? '') ?>"
                        maxlength="12"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="nama"
                        name="nama" 
                        class="form-control" 
                        placeholder="Masukkan nama lengkap"
                        value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="jurusan">Program Studi / Jurusan</label>
                    <input 
                        type="text" 
                        id="jurusan"
                        name="jurusan" 
                        class="form-control"
                        placeholder="Contoh: Teknik Informatika"
                        value="<?= htmlspecialchars($_POST['jurusan'] ?? '') ?>"
                        list="jurusan-list"
                        required
                    >
                    <datalist id="jurusan-list">
                        <?php
                        $jList = $pdo->query("SELECT DISTINCT jurusan FROM mahasiswa ORDER BY jurusan")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($jList as $j) echo "<option value=\"" . htmlspecialchars($j) . "\">";
                        ?>
                    </datalist>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                        </svg>
                        Simpan Data
                    </button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>

    </div>
</div>
</body>
</html>