<?php
require_once __DIR__ . '/baglanti.php';

$bitkiler = get_db()
    ->query("SELECT * FROM bitkiler WHERE kategori = 'Tropikal Bitkiler' ORDER BY bitki_adi")
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Botanik Bahçesi Kataloğu</title>
</head>
<body>
    <h1>Bitkiler (<?= count($bitkiler) ?>)</h1>
    <table border="1" cellpadding="4" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Envanter Kodu</th>
                <th>Bitki Adı</th>
                <th>Latince Adı</th>
                <th>Familya</th>
                <th>Anavatan</th>
                <th>Kategori</th>
                <th>Sera Bölümü</th>
                <th>Durum</th>
                <th>Eklenme Tarihi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bitkiler as $bitki): ?>
                <tr>
                    <?php foreach ($bitki as $deger): ?>
                        <td><?= htmlspecialchars((string) $deger) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
