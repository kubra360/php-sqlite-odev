<?php
require_once __DIR__ . '/baglanti.php';
$db = get_db();

// Yeni bitki ekleme formu gönderildiyse veritabanına kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    $stmt = $db->prepare("
        INSERT INTO bitkiler (envanter_kodu, bitki_adi, latince_adi, familya, anavatan, kategori, sera_bolumu, durum, eklenme_tarihi)
        VALUES (:kod, :ad, :latince, :familya, :anavatan, :kategori, :sera, :durum, :tarih)
    ");
    $stmt->execute([
        ':kod'      => trim($_POST['envanter_kodu']),
        ':ad'       => trim($_POST['bitki_adi']),
        ':latince'  => trim($_POST['latince_adi']),
        ':familya'  => trim($_POST['familya']),
        ':anavatan' => trim($_POST['anavatan']),
        ':kategori' => trim($_POST['kategori']),
        ':sera'     => trim($_POST['sera_bolumu']),
        ':durum'    => trim($_POST['durum']),
        ':tarih'    => date('Y-m-d'),
    ]);
    header('Location: index.php');
    exit;
}

// Arama kutusuna bir şey yazıldıysa filtrele, yazılmadıysa tüm bitkileri getir
$arama = trim($_GET['q'] ?? '');
if ($arama !== '') {
    $stmt = $db->prepare("
        SELECT * FROM bitkiler
        WHERE bitki_adi LIKE :q OR latince_adi LIKE :q OR kategori LIKE :q OR anavatan LIKE :q
        ORDER BY id DESC
    ");
    $stmt->execute([':q' => '%' . $arama . '%']);
    $bitkiler = $stmt->fetchAll();
} else {
    $bitkiler = $db->query("SELECT * FROM bitkiler ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Botanik Bahçesi Kataloğu</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f4f7f5; color: #1f2933; max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #1b4332; margin-bottom: 6px; }
        .subtitle { color: #52606d; margin-bottom: 24px; font-size: 14px; }
        .card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px; }
        .card h2 { margin-top: 0; font-size: 17px; color: #2d6a4f; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        input, select { padding: 9px 12px; border: 1px solid #cbd2d9; border-radius: 6px; font-size: 14px; width: 100%; box-sizing: border-box; }
        button { background: #2d6a4f; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 14px; cursor: pointer; font-weight: 600; }
        button:hover { background: #1b4332; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 16px; }
        .search-bar input { max-width: 350px; }
        .reset-link { display: inline-flex; align-items: center; color: #52606d; text-decoration: none; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #2d6a4f; color: #fff; text-align: left; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #e4e7eb; }
        tr:hover td { background: #f0f7f4; }
    </style>
</head>
<body>

    <h1>🌿 Botanik Bahçesi Envanter Sistemi</h1>
    <div class="subtitle">PHP & SQLite Tabanlı Bitki Kataloğu Yönetim Arayüzü</div>

    <!-- Yeni Kayıt Ekleme Formu -->
    <div class="card">
        <h2>+ Yeni Bitki Ekle</h2>
        <form method="POST">
            <div class="form-grid">
                <input type="text" name="envanter_kodu" placeholder="Envanter Kodu (Örn: BTK-106)" required>
                <input type="text" name="bitki_adi" placeholder="Bitki Adı (Örn: Orkide)" required>
                <input type="text" name="latince_adi" placeholder="Latince Adı (Örn: Orchidaceae)" required>
                <input type="text" name="familya" placeholder="Familya">
                <input type="text" name="anavatan" placeholder="Anavatan">
                <select name="kategori">
                    <option value="Tropikal Bitkiler">Tropikal Bitkiler</option>
                    <option value="Sukulentler">Sukulentler</option>
                    <option value="Tıbbi ve Aromatik">Tıbbi ve Aromatik</option>
                    <option value="Akdeniz Florası">Akdeniz Florası</option>
                </select>
                <input type="text" name="sera_bolumu" placeholder="Sera Bölümü (Örn: Sera-A1)">
                <select name="durum">
                    <option value="Sağlıklı">Sağlıklı</option>
                    <option value="Çiçeklenmede">Çiçeklenmede</option>
                    <option value="Bakımda">Bakımda</option>
                </select>
            </div>
            <div style="margin-top: 14px;">
                <button type="submit" name="ekle">Veritabanına Kaydet</button>
            </div>
        </form>
    </div>

    <!-- Arama ve Listeleme Tablosu -->
    <div class="card">
        <h2>Kayıtlı Bitkiler (<?= count($bitkiler) ?>)</h2>
        <form method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Bitki adı, Latince adı veya kategori ara..." value="<?= htmlspecialchars($arama) ?>">
            <button type="submit">Ara</button>
            <?php if ($arama !== ''): ?>
                <a href="index.php" class="reset-link">Filtreyi Temizle</a>
            <?php endif; ?>
        </form>

        <table>
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
    </div>

</body>
</html>
