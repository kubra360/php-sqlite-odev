<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
$db = get_db();

// Bitki Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    csrf_kontrol();
    try {
        $stmt = $db->prepare("
            INSERT INTO bitkiler (envanter_kodu, bitki_adi, latince_adi, anavatan, kategori_id, sera_id, familya_id, durum, eklenme_tarihi)
            VALUES (:kod, :ad, :latince, :anavatan, :kat_id, :sera_id, :fam_id, :durum, :tarih)
        ");
        $stmt->execute([
            ':kod'      => trim($_POST['envanter_kodu']),
            ':ad'       => trim($_POST['bitki_adi']),
            ':latince'  => trim($_POST['latince_adi']),
            ':anavatan' => trim($_POST['anavatan']),
            ':kat_id'   => (int)$_POST['kategori_id'],
            ':sera_id'  => (int)$_POST['sera_id'],
            ':fam_id'   => (int)$_POST['familya_id'],
            ':durum'    => trim($_POST['durum']),
            ':tarih'    => date('Y-m-d')
        ]);
        mesaj_ayarla('Yeni bitki kaydı eklendi.');
    } catch (PDOException $e) {
        mesaj_ayarla('Kayıt eklenemedi (Envanter kodu zaten mevcut olabilir).', 'hata');
    }
    header('Location: index.php');
    exit;
}

// Bitki Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    csrf_kontrol();
    $stmt = $db->prepare("
        UPDATE bitkiler
        SET bitki_adi = :ad, latince_adi = :latince, anavatan = :anavatan,
            kategori_id = :kat_id, sera_id = :sera_id, familya_id = :fam_id, durum = :durum
        WHERE id = :id
    ");
    $stmt->execute([
        ':id'       => (int)$_POST['id'],
        ':ad'       => trim($_POST['bitki_adi']),
        ':latince'  => trim($_POST['latince_adi']),
        ':anavatan' => trim($_POST['anavatan']),
        ':kat_id'   => (int)$_POST['kategori_id'],
        ':sera_id'  => (int)$_POST['sera_id'],
        ':fam_id'   => (int)$_POST['familya_id'],
        ':durum'    => trim($_POST['durum'])
    ]);
    mesaj_ayarla('Bitki kaydı güncellendi.');
    header('Location: index.php');
    exit;
}

// Bitki Silme
if (isset($_GET['sil'])) {
    $stmt = $db->prepare("DELETE FROM bitkiler WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['sil']]);
    mesaj_ayarla('Bitki kaydı silindi.');
    header('Location: index.php');
    exit;
}

$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM bitkiler WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['duzenle']]);
    $duzenlenecek = $stmt->fetch();
}

$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY kategori_adi")->fetchAll();
$seralar = $db->query("SELECT * FROM seralar ORDER BY sera_adi")->fetchAll();
$familyalar = $db->query("SELECT * FROM familyalar ORDER BY familya_adi")->fetchAll();

// Arama ve Filtreleme
$arama = trim($_GET['q'] ?? '');
$katFiltre = $_GET['kat'] ?? '';
$seraFiltre = $_GET['sera'] ?? '';
$famFiltre = $_GET['fam'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($arama !== '') {
    $where .= " AND (b.envanter_kodu LIKE :q OR b.bitki_adi LIKE :q OR b.latince_adi LIKE :q OR b.anavatan LIKE :q)";
    $params[':q'] = '%' . $arama . '%';
}
if ($katFiltre !== '') {
    $where .= " AND b.kategori_id = :kat_id";
    $params[':kat_id'] = (int)$katFiltre;
}
if ($seraFiltre !== '') {
    $where .= " AND b.sera_id = :sera_id";
    $params[':sera_id'] = (int)$seraFiltre;
}
if ($famFiltre !== '') {
    $where .= " AND b.familya_id = :fam_id";
    $params[':fam_id'] = (int)$famFiltre;
}

// Sayfalama
$sayfaBasina = 20;
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$toplamStmt = $db->prepare("SELECT COUNT(*) FROM bitkiler b $where");
$toplamStmt->execute($params);
$toplamKayit = (int)$toplamStmt->fetchColumn();
$toplamSayfa = max(1, (int)ceil($toplamKayit / $sayfaBasina));
$sayfa = min($sayfa, $toplamSayfa);
$offset = ($sayfa - 1) * $sayfaBasina;

$sql = "
    SELECT b.*, k.kategori_adi, s.sera_adi, f.familya_adi
    FROM bitkiler b
    JOIN kategoriler k ON b.kategori_id = k.id
    JOIN seralar s ON b.sera_id = s.id
    JOIN familyalar f ON b.familya_id = f.id
    $where
    ORDER BY b.id DESC
    LIMIT $sayfaBasina OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$bitkiler = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="kutu">
    <h2><?= $duzenlenecek ? 'Bitki Kaydını Güncelle (' . e($duzenlenecek['envanter_kodu']) . ')' : 'Yeni Bitki Ekle' ?></h2>
    <form method="POST" class="satir">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php if ($duzenlenecek): ?>
            <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
        <?php else: ?>
            <input type="text" name="envanter_kodu" placeholder="Envanter Kodu (Örn: BTK-101)" required>
        <?php endif; ?>

        <input type="text" name="bitki_adi" placeholder="Bitki Adı" value="<?= e($duzenlenecek['bitki_adi'] ?? '') ?>" required>
        <input type="text" name="latince_adi" placeholder="Latince Adı" value="<?= e($duzenlenecek['latince_adi'] ?? '') ?>" required>
        <input type="text" name="anavatan" placeholder="Anavatan" value="<?= e($duzenlenecek['anavatan'] ?? '') ?>">

        <select name="familya_id" required>
            <option value="">Familya Seçin</option>
            <?php foreach ($familyalar as $f): ?>
                <option value="<?= $f['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['familya_id'] == $f['id']) ? 'selected' : '' ?>>
                    <?= e($f['familya_adi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="kategori_id" required>
            <option value="">Kategori Seçin</option>
            <?php foreach ($kategoriler as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['kategori_id'] == $k['id']) ? 'selected' : '' ?>>
                    <?= e($k['kategori_adi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="sera_id" required>
            <option value="">Sera Seçin</option>
            <?php foreach ($seralar as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['sera_id'] == $s['id']) ? 'selected' : '' ?>>
                    <?= e($s['sera_adi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="durum">
            <?php foreach (['Sağlıklı', 'Çiçeklenmede', 'Bakımda'] as $d): ?>
                <option value="<?= $d ?>" <?= ($duzenlenecek && $duzenlenecek['durum'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
        </select>

        <?php if ($duzenlenecek): ?>
            <button type="submit" name="guncelle">Güncelle</button>
            <a href="index.php" class="btn btn-ikincil">İptal</a>
        <?php else: ?>
            <button type="submit" name="ekle">Kaydet</button>
        <?php endif; ?>
    </form>
</div>

<div class="kutu">
    <h2>Bitki Listesi (Toplam <?= $toplamKayit ?> Kayıt)</h2>
    <form method="GET" class="satir">
        <input type="text" name="q" placeholder="Kod, bitki veya Latince adı ara..." value="<?= e($arama) ?>">
        <select name="fam">
            <option value="">Tüm Familyalar</option>
            <?php foreach ($familyalar as $f): ?>
                <option value="<?= $f['id'] ?>" <?= ($famFiltre == $f['id']) ? 'selected' : '' ?>><?= e($f['familya_adi']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="kat">
            <option value="">Tüm Kategoriler</option>
            <?php foreach ($kategoriler as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($katFiltre == $k['id']) ? 'selected' : '' ?>><?= e($k['kategori_adi']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sera">
            <option value="">Tüm Seralar</option>
            <?php foreach ($seralar as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($seraFiltre == $s['id']) ? 'selected' : '' ?>><?= e($s['sera_adi']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Ara / Filtrele</button>
        <?php if ($arama !== '' || $katFiltre !== '' || $seraFiltre !== '' || $famFiltre !== ''): ?>
            <a href="index.php" class="btn btn-ikincil">Temizle</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>Kod</th>
                <th>Bitki Adı</th>
                <th>Latince Adı</th>
                <th>Familya</th>
                <th>Kategori</th>
                <th>Sera</th>
                <th>Anavatan</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bitkiler as $b): ?>
                <tr>
                    <td><?= e($b['envanter_kodu']) ?></td>
                    <td><?= e($b['bitki_adi']) ?></td>
                    <td><?= e($b['latince_adi']) ?></td>
                    <td><?= e($b['familya_adi']) ?></td>
                    <td><?= e($b['kategori_adi']) ?></td>
                    <td><?= e($b['sera_adi']) ?></td>
                    <td><?= e($b['anavatan']) ?></td>
                    <td><?= e($b['durum']) ?></td>
                    <td>
                        <a href="index.php?duzenle=<?= $b['id'] ?>" class="islem-duzenle">Düzenle</a>
                        <a href="index.php?sil=<?= $b['id'] ?>" class="islem-sil" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($toplamSayfa > 1): ?>
        <div class="sayfalama">
            <?php for ($i = 1; $i <= $toplamSayfa; $i++): ?>
                <?php $qs = http_build_query(array_merge($_GET, ['sayfa' => $i])); ?>
                <a href="?<?= $qs ?>" class="<?= $i === $sayfa ? 'aktif' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
