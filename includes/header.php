<?php
require_once __DIR__ . '/helpers.php';
$aktifSayfa = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Botanik Bahçesi Kataloğu</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <h1>Botanik Bahçesi Kataloğu</h1>
    <nav>
        <a href="index.php" class="<?= $aktifSayfa === 'index.php' ? 'aktif' : '' ?>">Bitkiler</a>
        <a href="kategoriler.php" class="<?= $aktifSayfa === 'kategoriler.php' ? 'aktif' : '' ?>">Kategoriler</a>
        <a href="seralar.php" class="<?= $aktifSayfa === 'seralar.php' ? 'aktif' : '' ?>">Seralar</a>
        <a href="familyalar.php" class="<?= $aktifSayfa === 'familyalar.php' ? 'aktif' : '' ?>">Familyalar</a>
    </nav>
</header>
<?php mesaj_goster(); ?>
