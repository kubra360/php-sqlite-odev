<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    csrf_kontrol();
    $ad = trim($_POST['kategori_adi']);
    if ($ad !== '') {
        try {
            $stmt = $db->prepare("INSERT INTO kategoriler (kategori_adi) VALUES (:ad)");
            $stmt->execute([':ad' => $ad]);
            mesaj_ayarla('Kategori eklendi.');
        } catch (PDOException $e) {
            mesaj_ayarla('Bu kategori zaten mevcut.', 'hata');
        }
    }
    header('Location: kategoriler.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    csrf_kontrol();
    $stmt = $db->prepare("UPDATE kategoriler SET kategori_adi = :ad WHERE id = :id");
    $stmt->execute([':ad' => trim($_POST['kategori_adi']), ':id' => (int)$_POST['id']]);
    mesaj_ayarla('Kategori güncellendi.');
    header('Location: kategoriler.php');
    exit;
}

if (isset($_GET['sil'])) {
    try {
        $stmt = $db->prepare("DELETE FROM kategoriler WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['sil']]);
        mesaj_ayarla('Kategori silindi.');
    } catch (PDOException $e) {
        mesaj_ayarla('Bu kategoriye bağlı bitkiler olduğu için silinemez.', 'hata');
    }
    header('Location: kategoriler.php');
    exit;
}

$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM kategoriler WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['duzenle']]);
    $duzenlenecek = $stmt->fetch();
}

$arama = trim($_GET['q'] ?? '');
$sql = "
    SELECT k.*, COUNT(b.id) AS bitki_sayisi
    FROM kategoriler k
    LEFT JOIN bitkiler b ON b.kategori_id = k.id
";
$params = [];
if ($arama !== '') {
    $sql .= " WHERE k.kategori_adi LIKE :q";
    $params[':q'] = '%' . $arama . '%';
}
$sql .= " GROUP BY k.id ORDER BY k.kategori_adi";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$liste = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="kutu">
    <h2><?= $duzenlenecek ? 'Kategoriyi Güncelle' : 'Yeni Kategori Ekle' ?></h2>
    <form method="POST" class="satir">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php if ($duzenlenecek): ?>
            <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
        <?php endif; ?>
        <input type="text" name="kategori_adi" placeholder="Kategori adı" value="<?= e($duzenlenecek['kategori_adi'] ?? '') ?>" required>
        <?php if ($duzenlenecek): ?>
            <button type="submit" name="guncelle">Güncelle</button>
            <a href="kategoriler.php" class="btn btn-ikincil">İptal</a>
        <?php else: ?>
            <button type="submit" name="ekle">Ekle</button>
        <?php endif; ?>
    </form>
</div>

<div class="kutu">
    <h2>Kategoriler (<?= count($liste) ?>)</h2>
    <form method="GET" class="satir">
        <input type="text" name="q" placeholder="Kategori ara..." value="<?= e($arama) ?>">
        <button type="submit">Ara</button>
        <?php if ($arama !== ''): ?>
            <a href="kategoriler.php" class="btn btn-ikincil">Temizle</a>
        <?php endif; ?>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kategori Adı</th>
                <th>Bağlı Bitki Sayısı</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($liste as $satir): ?>
                <tr>
                    <td><?= $satir['id'] ?></td>
                    <td><?= e($satir['kategori_adi']) ?></td>
                    <td><?= $satir['bitki_sayisi'] ?></td>
                    <td>
                        <a href="kategoriler.php?duzenle=<?= $satir['id'] ?>" class="islem-duzenle">Düzenle</a>
                        <a href="kategoriler.php?sil=<?= $satir['id'] ?>" class="islem-sil" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
