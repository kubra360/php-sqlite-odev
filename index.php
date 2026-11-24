<?php
require_once __DIR__ . '/baglanti.php';
$db = get_db();
$db->exec('PRAGMA foreign_keys = ON;');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategori_ekle'])) {
    $yeniKat = trim($_POST['yeni_kategori']);
    if ($yeniKat !== '') {
        $stmt = $db->prepare("INSERT OR IGNORE INTO kategoriler (kategori_adi) VALUES (:ad)");
        $stmt->execute([':ad' => $yeniKat]);
    }
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sera_ekle'])) {
    $yeniSera = trim($_POST['yeni_sera']);
    $uzman = trim($_POST['sorumlu_uzman']);
    if ($yeniSera !== '') {
        $stmt = $db->prepare("INSERT OR IGNORE INTO seralar (sera_adi, sorumlu_uzman) VALUES (:sera, :uzman)");
        $stmt->execute([':sera' => $yeniSera, ':uzman' => $uzman]);
    }
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bitki_ekle'])) {
    $stmt = $db->prepare("
        INSERT INTO bitkiler (envanter_kodu, bitki_adi, latince_adi, familya, anavatan, kategori_id, sera_id, durum, eklenme_tarihi)
        VALUES (:kod, :ad, :latince, :familya, :anavatan, :kat_id, :sera_id, :durum, :tarih)
    ");
    $stmt->execute([
        ':kod'      => trim($_POST['envanter_kodu']),
        ':ad'       => trim($_POST['bitki_adi']),
        ':latince'  => trim($_POST['latince_adi']),
        ':familya'  => trim($_POST['familya']),
        ':anavatan' => trim($_POST['anavatan']),
        ':kat_id'   => (int) $_POST['kategori_id'],
        ':sera_id'  => (int) $_POST['sera_id'],
        ':durum'    => trim($_POST['durum']),
        ':tarih'    => date('Y-m-d'),
    ]);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bitki_guncelle'])) {
    $stmt = $db->prepare("
        UPDATE bitkiler
        SET bitki_adi = :ad, latince_adi = :latince, familya = :familya, anavatan = :anavatan,
            kategori_id = :kat_id, sera_id = :sera_id, durum = :durum
        WHERE id = :id
    ");
    $stmt->execute([
        ':id'       => (int) $_POST['id'],
        ':ad'       => trim($_POST['bitki_adi']),
        ':latince'  => trim($_POST['latince_adi']),
        ':familya'  => trim($_POST['familya']),
        ':anavatan' => trim($_POST['anavatan']),
        ':kat_id'   => (int) $_POST['kategori_id'],
        ':sera_id'  => (int) $_POST['sera_id'],
        ':durum'    => trim($_POST['durum']),
    ]);
    header('Location: index.php');
    exit;
}

$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM bitkiler WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['duzenle']]);
    $duzenlenecek = $stmt->fetch();
}

$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY kategori_adi")->fetchAll();
$seralar = $db->query("SELECT * FROM seralar ORDER BY sera_adi")->fetchAll();

$arama = trim($_GET['q'] ?? '');
$katFiltre = $_GET['kat'] ?? '';

$sql = "
    SELECT b.id, b.envanter_kodu, b.bitki_adi, b.latince_adi, b.familya, b.anavatan,
           k.kategori_adi, s.sera_adi, s.sorumlu_uzman, b.durum
    FROM bitkiler b
    JOIN kategoriler k ON b.kategori_id = k.id
    JOIN seralar s ON b.sera_id = s.id
    WHERE 1=1
";
$params = [];

if ($arama !== '') {
    $sql .= " AND (b.bitki_adi LIKE :q OR b.latince_adi LIKE :q OR b.familya LIKE :q OR b.anavatan LIKE :q OR s.sera_adi LIKE :q)";
    $params[':q'] = '%' . $arama . '%';
}
if ($katFiltre !== '') {
    $sql .= " AND b.kategori_id = :kat_id";
    $params[':kat_id'] = (int) $katFiltre;
}

$sql .= " ORDER BY b.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$bitkiler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Botanik Bahçesi Kataloğu</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; max-width: 1100px; margin: 20px auto; padding: 0 15px; }
        h1 { font-size: 22px; border-bottom: 1px solid #ccc; padding-bottom: 8px; }
        h3 { font-size: 15px; margin: 0 0 10px 0; }
        .kutu { border: 1px solid #ddd; padding: 14px; margin-bottom: 16px; background: #fafafa; }
        .ikili { display: flex; gap: 16px; margin-bottom: 16px; }
        .ikili .kutu { flex: 1; margin-bottom: 0; }
        .satir { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
        input, select { padding: 6px 8px; border: 1px solid #bbb; font-size: 13px; }
        button { padding: 6px 14px; background: #333; color: #fff; border: none; font-size: 13px; cursor: pointer; }
        button:hover { background: #000; }
        a.link-btn { padding: 6px 10px; background: #eee; color: #333; text-decoration: none; font-size: 13px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 7px 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

    <h1>Botanik Bahçesi Kataloğu</h1>

    <div class="ikili">
        <div class="kutu">
            <h3>Yeni Kategori Ekle</h3>
            <form method="POST" class="satir">
                <input type="text" name="yeni_kategori" placeholder="Kategori adı" required>
                <button type="submit" name="kategori_ekle">Ekle</button>
            </form>
        </div>

        <div class="kutu">
            <h3>Yeni Sera Ekle</h3>
            <form method="POST" class="satir">
                <input type="text" name="yeni_sera" placeholder="Sera adı" required>
                <input type="text" name="sorumlu_uzman" placeholder="Sorumlu uzman">
                <button type="submit" name="sera_ekle">Ekle</button>
            </form>
        </div>
    </div>

    <div class="kutu">
        <h3><?= $duzenlenecek ? 'Kaydı Düzenle (' . htmlspecialchars($duzenlenecek['envanter_kodu']) . ')' : 'Yeni Bitki Ekle' ?></h3>
        <form method="POST">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            <div class="satir">
                <?php if (!$duzenlenecek): ?>
                    <input type="text" name="envanter_kodu" placeholder="Envanter Kodu" required>
                <?php endif; ?>
                <input type="text" name="bitki_adi" placeholder="Bitki Adı" value="<?= htmlspecialchars($duzenlenecek['bitki_adi'] ?? '') ?>" required>
                <input type="text" name="latince_adi" placeholder="Latince Adı" value="<?= htmlspecialchars($duzenlenecek['latince_adi'] ?? '') ?>" required>
                <input type="text" name="familya" placeholder="Familya" value="<?= htmlspecialchars($duzenlenecek['familya'] ?? '') ?>">
                <input type="text" name="anavatan" placeholder="Anavatan" value="<?= htmlspecialchars($duzenlenecek['anavatan'] ?? '') ?>">

                <select name="kategori_id" required>
                    <option value="">Kategori Seçin</option>
                    <?php foreach ($kategoriler as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['kategori_id'] == $kat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['kategori_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sera_id" required>
                    <option value="">Sera Seçin</option>
                    <?php foreach ($seralar as $sera): ?>
                        <option value="<?= $sera['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['sera_id'] == $sera['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sera['sera_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="durum">
                    <?php foreach (['Sağlıklı', 'Çiçeklenmede', 'Bakımda'] as $d): ?>
                        <option value="<?= $d ?>" <?= ($duzenlenecek && $duzenlenecek['durum'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>

                <?php if ($duzenlenecek): ?>
                    <button type="submit" name="bitki_guncelle">Güncelle</button>
                    <a href="index.php" class="link-btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="bitki_ekle">Kaydet</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="kutu">
        <h3>Bitki Listesi (<?= count($bitkiler) ?>)</h3>
        <form method="GET" class="satir">
            <input type="text" name="q" placeholder="Bitki, familya veya sera ara..." value="<?= htmlspecialchars($arama) ?>">
            <select name="kat">
                <option value="">Tüm Kategoriler</option>
                <?php foreach ($kategoriler as $kat): ?>
                    <option value="<?= $kat['id'] ?>" <?= ($katFiltre == $kat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['kategori_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ara</button>
            <?php if ($arama !== '' || $katFiltre !== ''): ?>
                <a href="index.php" class="link-btn">Temizle</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Bitki Adı</th>
                    <th>Latince Adı</th>
                    <th>Familya</th>
                    <th>Anavatan</th>
                    <th>Kategori</th>
                    <th>Sera</th>
                    <th>Sorumlu</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bitkiler as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['envanter_kodu']) ?></td>
                        <td><?= htmlspecialchars($b['bitki_adi']) ?></td>
                        <td><?= htmlspecialchars($b['latince_adi']) ?></td>
                        <td><?= htmlspecialchars($b['familya']) ?></td>
                        <td><?= htmlspecialchars($b['anavatan']) ?></td>
                        <td><?= htmlspecialchars($b['kategori_adi']) ?></td>
                        <td><?= htmlspecialchars($b['sera_adi']) ?></td>
                        <td><?= htmlspecialchars($b['sorumlu_uzman']) ?></td>
                        <td><?= htmlspecialchars($b['durum']) ?></td>
                        <td><a href="index.php?duzenle=<?= $b['id'] ?>">Düzenle</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
EOFcd ~/Desktop/odevim && cat << 'EOF' > index.php
<?php
require_once __DIR__ . '/baglanti.php';
$db = get_db();
$db->exec('PRAGMA foreign_keys = ON;');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategori_ekle'])) {
    $yeniKat = trim($_POST['yeni_kategori']);
    if ($yeniKat !== '') {
        $stmt = $db->prepare("INSERT OR IGNORE INTO kategoriler (kategori_adi) VALUES (:ad)");
        $stmt->execute([':ad' => $yeniKat]);
    }
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sera_ekle'])) {
    $yeniSera = trim($_POST['yeni_sera']);
    $uzman = trim($_POST['sorumlu_uzman']);
    if ($yeniSera !== '') {
        $stmt = $db->prepare("INSERT OR IGNORE INTO seralar (sera_adi, sorumlu_uzman) VALUES (:sera, :uzman)");
        $stmt->execute([':sera' => $yeniSera, ':uzman' => $uzman]);
    }
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bitki_ekle'])) {
    $stmt = $db->prepare("
        INSERT INTO bitkiler (envanter_kodu, bitki_adi, latince_adi, familya, anavatan, kategori_id, sera_id, durum, eklenme_tarihi)
        VALUES (:kod, :ad, :latince, :familya, :anavatan, :kat_id, :sera_id, :durum, :tarih)
    ");
    $stmt->execute([
        ':kod'      => trim($_POST['envanter_kodu']),
        ':ad'       => trim($_POST['bitki_adi']),
        ':latince'  => trim($_POST['latince_adi']),
        ':familya'  => trim($_POST['familya']),
        ':anavatan' => trim($_POST['anavatan']),
        ':kat_id'   => (int) $_POST['kategori_id'],
        ':sera_id'  => (int) $_POST['sera_id'],
        ':durum'    => trim($_POST['durum']),
        ':tarih'    => date('Y-m-d'),
    ]);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bitki_guncelle'])) {
    $stmt = $db->prepare("
        UPDATE bitkiler
        SET bitki_adi = :ad, latince_adi = :latince, familya = :familya, anavatan = :anavatan,
            kategori_id = :kat_id, sera_id = :sera_id, durum = :durum
        WHERE id = :id
    ");
    $stmt->execute([
        ':id'       => (int) $_POST['id'],
        ':ad'       => trim($_POST['bitki_adi']),
        ':latince'  => trim($_POST['latince_adi']),
        ':familya'  => trim($_POST['familya']),
        ':anavatan' => trim($_POST['anavatan']),
        ':kat_id'   => (int) $_POST['kategori_id'],
        ':sera_id'  => (int) $_POST['sera_id'],
        ':durum'    => trim($_POST['durum']),
    ]);
    header('Location: index.php');
    exit;
}

$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM bitkiler WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['duzenle']]);
    $duzenlenecek = $stmt->fetch();
}

$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY kategori_adi")->fetchAll();
$seralar = $db->query("SELECT * FROM seralar ORDER BY sera_adi")->fetchAll();

$arama = trim($_GET['q'] ?? '');
$katFiltre = $_GET['kat'] ?? '';

$sql = "
    SELECT b.id, b.envanter_kodu, b.bitki_adi, b.latince_adi, b.familya, b.anavatan,
           k.kategori_adi, s.sera_adi, s.sorumlu_uzman, b.durum
    FROM bitkiler b
    JOIN kategoriler k ON b.kategori_id = k.id
    JOIN seralar s ON b.sera_id = s.id
    WHERE 1=1
";
$params = [];

if ($arama !== '') {
    $sql .= " AND (b.bitki_adi LIKE :q OR b.latince_adi LIKE :q OR b.familya LIKE :q OR b.anavatan LIKE :q OR s.sera_adi LIKE :q)";
    $params[':q'] = '%' . $arama . '%';
}
if ($katFiltre !== '') {
    $sql .= " AND b.kategori_id = :kat_id";
    $params[':kat_id'] = (int) $katFiltre;
}

$sql .= " ORDER BY b.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$bitkiler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Botanik Bahçesi Kataloğu</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; max-width: 1100px; margin: 20px auto; padding: 0 15px; }
        h1 { font-size: 22px; border-bottom: 1px solid #ccc; padding-bottom: 8px; }
        h3 { font-size: 15px; margin: 0 0 10px 0; }
        .kutu { border: 1px solid #ddd; padding: 14px; margin-bottom: 16px; background: #fafafa; }
        .ikili { display: flex; gap: 16px; margin-bottom: 16px; }
        .ikili .kutu { flex: 1; margin-bottom: 0; }
        .satir { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
        input, select { padding: 6px 8px; border: 1px solid #bbb; font-size: 13px; }
        button { padding: 6px 14px; background: #333; color: #fff; border: none; font-size: 13px; cursor: pointer; }
        button:hover { background: #000; }
        a.link-btn { padding: 6px 10px; background: #eee; color: #333; text-decoration: none; font-size: 13px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 7px 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

    <h1>Botanik Bahçesi Kataloğu</h1>

    <div class="ikili">
        <div class="kutu">
            <h3>Yeni Kategori Ekle</h3>
            <form method="POST" class="satir">
                <input type="text" name="yeni_kategori" placeholder="Kategori adı" required>
                <button type="submit" name="kategori_ekle">Ekle</button>
            </form>
        </div>

        <div class="kutu">
            <h3>Yeni Sera Ekle</h3>
            <form method="POST" class="satir">
                <input type="text" name="yeni_sera" placeholder="Sera adı" required>
                <input type="text" name="sorumlu_uzman" placeholder="Sorumlu uzman">
                <button type="submit" name="sera_ekle">Ekle</button>
            </form>
        </div>
    </div>

    <div class="kutu">
        <h3><?= $duzenlenecek ? 'Kaydı Düzenle (' . htmlspecialchars($duzenlenecek['envanter_kodu']) . ')' : 'Yeni Bitki Ekle' ?></h3>
        <form method="POST">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            <div class="satir">
                <?php if (!$duzenlenecek): ?>
                    <input type="text" name="envanter_kodu" placeholder="Envanter Kodu" required>
                <?php endif; ?>
                <input type="text" name="bitki_adi" placeholder="Bitki Adı" value="<?= htmlspecialchars($duzenlenecek['bitki_adi'] ?? '') ?>" required>
                <input type="text" name="latince_adi" placeholder="Latince Adı" value="<?= htmlspecialchars($duzenlenecek['latince_adi'] ?? '') ?>" required>
                <input type="text" name="familya" placeholder="Familya" value="<?= htmlspecialchars($duzenlenecek['familya'] ?? '') ?>">
                <input type="text" name="anavatan" placeholder="Anavatan" value="<?= htmlspecialchars($duzenlenecek['anavatan'] ?? '') ?>">

                <select name="kategori_id" required>
                    <option value="">Kategori Seçin</option>
                    <?php foreach ($kategoriler as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['kategori_id'] == $kat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['kategori_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sera_id" required>
                    <option value="">Sera Seçin</option>
                    <?php foreach ($seralar as $sera): ?>
                        <option value="<?= $sera['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['sera_id'] == $sera['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sera['sera_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="durum">
                    <?php foreach (['Sağlıklı', 'Çiçeklenmede', 'Bakımda'] as $d): ?>
                        <option value="<?= $d ?>" <?= ($duzenlenecek && $duzenlenecek['durum'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>

                <?php if ($duzenlenecek): ?>
                    <button type="submit" name="bitki_guncelle">Güncelle</button>
                    <a href="index.php" class="link-btn">İptal</a>
                <?php else: ?>
                    <button type="submit" name="bitki_ekle">Kaydet</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="kutu">
        <h3>Bitki Listesi (<?= count($bitkiler) ?>)</h3>
        <form method="GET" class="satir">
            <input type="text" name="q" placeholder="Bitki, familya veya sera ara..." value="<?= htmlspecialchars($arama) ?>">
            <select name="kat">
                <option value="">Tüm Kategoriler</option>
                <?php foreach ($kategoriler as $kat): ?>
                    <option value="<?= $kat['id'] ?>" <?= ($katFiltre == $kat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['kategori_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ara</button>
            <?php if ($arama !== '' || $katFiltre !== ''): ?>
                <a href="index.php" class="link-btn">Temizle</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Bitki Adı</th>
                    <th>Latince Adı</th>
                    <th>Familya</th>
                    <th>Anavatan</th>
                    <th>Kategori</th>
                    <th>Sera</th>
                    <th>Sorumlu</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bitkiler as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['envanter_kodu']) ?></td>
                        <td><?= htmlspecialchars($b['bitki_adi']) ?></td>
                        <td><?= htmlspecialchars($b['latince_adi']) ?></td>
                        <td><?= htmlspecialchars($b['familya']) ?></td>
                        <td><?= htmlspecialchars($b['anavatan']) ?></td>
                        <td><?= htmlspecialchars($b['kategori_adi']) ?></td>
                        <td><?= htmlspecialchars($b['sera_adi']) ?></td>
                        <td><?= htmlspecialchars($b['sorumlu_uzman']) ?></td>
                        <td><?= htmlspecialchars($b['durum']) ?></td>
                        <td><a href="index.php?duzenle=<?= $b['id'] ?>">Düzenle</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
