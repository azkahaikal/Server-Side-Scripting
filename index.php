<?php
require 'connection.php';

// Ambil parameter pencarian & filter
$search  = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$jurusan = isset($_GET['jurusan']) ? trim($_GET['jurusan']) : '';

// Ambil daftar jurusan unik untuk dropdown filter
$jurusanList = $pdo->query("SELECT DISTINCT jurusan FROM mahasiswa ORDER BY jurusan ASC")->fetchAll(PDO::FETCH_COLUMN);

// Query dinamis dengan filter
$sql    = "SELECT * FROM mahasiswa WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql    .= " AND (nama LIKE ? OR npm LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($jurusan !== '') {
    $sql    .= " AND jurusan = ?";
    $params[] = $jurusan;
}

$sql .= " ORDER BY id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total semua data
$total = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();

// Flash message
$flash = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') $flash = ['type' => 'success', 'msg' => '✓ Data mahasiswa berhasil ditambahkan.'];
    if ($_GET['success'] === 'edit')   $flash = ['type' => 'success', 'msg' => '✓ Data mahasiswa berhasil diperbarui.'];
    if ($_GET['success'] === 'hapus')  $flash = ['type' => 'success', 'msg' => '✓ Data mahasiswa berhasil dihapus.'];
}

// Fungsi warna avatar berdasarkan karakter nama
function avatarColor($name) {
    $colors = [
        ['#5b8af5','#8b5cf6'], ['#e8c547','#f97316'],
        ['#4ecb71','#06b6d4'], ['#f05d5d','#ec4899'],
        ['#06b6d4','#5b8af5'], ['#a78bfa','#f472b6'],
    ];
    return $colors[ord($name[0] ?? 'A') % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Data Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">

    <!-- HEADER -->
    <header class="site-header animate-in">
        <div>
            <h1 class="site-title">Sistem <span>Akademik</span></h1>
            <p class="site-subtitle">Manajemen Data Mahasiswa — Universitas</p>
        </div>
        <span class="count-badge"><strong><?= $total ?></strong> mahasiswa terdaftar</span>
    </header>

    <?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] ?> animate-in">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- TOOLBAR: SEARCH + FILTER + TAMBAH -->
    <div class="toolbar animate-in" style="animation-delay:0.06s">
        <form method="GET" action="index.php" style="display:flex;gap:12px;flex:1;flex-wrap:wrap;">
            <!-- Search -->
            <div class="search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Cari nama atau NPM mahasiswa…"
                    value="<?= htmlspecialchars($search) ?>"
                    autocomplete="off"
                >
            </div>

            <!-- Filter Jurusan -->
            <select name="jurusan" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Jurusan</option>
                <?php foreach ($jurusanList as $j): ?>
                <option value="<?= htmlspecialchars($j) ?>" <?= $jurusan === $j ? 'selected' : '' ?>>
                    <?= htmlspecialchars($j) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filter
            </button>

            <?php if ($search || $jurusan): ?>
            <a href="index.php" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
                Reset
            </a>
            <?php endif; ?>
        </form>

        <a href="tambah.php" class="btn btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Data
        </a>
    </div>

    <!-- TABLE CARD -->
    <div class="table-card animate-in" style="animation-delay:0.1s">

        <!-- Info hasil pencarian -->
        <?php if ($search || $jurusan): ?>
        <div class="results-info">
            Menampilkan <strong><?= count($mahasiswa) ?></strong> hasil
            <?php if ($search): ?>untuk kata kunci <mark><?= htmlspecialchars($search) ?></mark><?php endif; ?>
            <?php if ($jurusan): ?>di jurusan <mark><?= htmlspecialchars($jurusan) ?></mark><?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="table-wrap">
            <?php if (empty($mahasiswa)): ?>
            <div class="empty-state">
                <div class="icon">🔍</div>
                <p>Tidak ada data ditemukan</p>
                <span>Coba ubah kata kunci pencarian atau filter jurusan</span>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NPM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($mahasiswa as $row):
                    $initial = strtoupper(substr($row['nama'], 0, 1));
                    $colors  = avatarColor($row['nama']);
                ?>
                <tr>
                    <td class="td-no"><?= $no++ ?></td>
                    <td class="td-npm"><?= htmlspecialchars($row['npm']) ?></td>
                    <td class="td-nama">
                        <div class="nama-wrap">
                            <span class="avatar" style="background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>)">
                                <?= $initial ?>
                            </span>
                            <?= htmlspecialchars($row['nama']) ?>
                        </div>
                    </td>
                    <td><span class="jurusan-pill"><?= htmlspecialchars($row['jurusan']) ?></span></td>
                    <td>
                        <div class="td-aksi">
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </a>
                            <a href="hapus.php?id=<?= $row['id'] ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Yakin ingin menghapus data <?= htmlspecialchars(addslashes($row['nama'])) ?>?')">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                                Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
// Realtime search dengan debounce (opsional enhancement)
const searchInput = document.querySelector('.search-input');
let debounceTimer;
if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchInput.closest('form').submit();
        }, 500);
    });
}
</script>
</body>
</html>