<?php
require 'connection.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: index.php?success=hapus");
        exit;
    }
}

header("Location: index.php");
exit;
?>