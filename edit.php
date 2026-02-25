<?php
require 'connection.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Ambil data mahasiswa
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npm     = trim($_POST['npm']     ?? '');
    $nama    = trim($_POST['nama']    ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');

    if (empty($npm) || empty($nama) || empty($jurusan)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!preg_match('/^\d{10,12}$/', $npm)) {
        $error = 'NPM harus berupa 10–12 digit angka.';
    } else {
        // Cek NPM duplikat (selain id ini sendiri)
        $cek = $pdo->prepare("SELECT id FROM mahasiswa WHERE npm = ? AND id != ?");
        $cek->execute([$npm, $id]);
        if ($cek->fetch()) {
            $error = 'NPM sudah digunakan mahasiswa lain.';
        } else {
            $sql  = "UPDATE mahasiswa SET npm = ?, nama = ?, jurusan = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$npm, $nama, $jurusan, $id])) {
                header("Location: index.php?success=edit");
                exit;
            }
        }
    }

    // Perbarui preview data jika ada error
    $data['npm']     = $npm;
    $data['nama']    = $nama;
    $data['jurusan'] = $jurusan;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
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
            <span class="current">Edit Mahasiswa</span>
        </div>

        <div class="form-header">
            <h2>Edit Data <span style="color:var(--accent2)">Mahasiswa</span></h2>
            <p>Perbarui informasi mahasiswa yang sudah terdaftar di sistem.</p>
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
                        value="<?= htmlspecialchars($data['npm']) ?>"
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
                        value="<?= htmlspecialchars($data['nama']) ?>"
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
                        value="<?= htmlspecialchars($data['jurusan']) ?>"
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
                        Update Data
                    </button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>

    </div>
</div>
</body>
</html>