<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    csrf_kontrol();
    $ad = trim($_POST['familya_adi']);
    if ($ad !== '') {
        try {
            $stmt = $db->prepare("INSERT INTO familyalar (familya_adi) VALUES (:ad)");
            $stmt->execute([':ad' => $ad]);
            mesaj_ayarla('Familya eklendi.');
        } catch (PDOException $e) {
            mesaj_ayarla('Bu familya zaten mevcut.', 'hata');
        }
    }
    header('Location: familyalar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    csrf_kontrol();
    $stmt = $db->prepare("UPDATE familyalar SET familya_adi = :ad WHERE id = :id");
    $stmt->execute([':ad' => trim($_POST['familya_adi']), ':id' => (int)$_POST['id']]);
    mesaj_ayarla('Familya güncellendi.');
    header('Location: familyalar.php');
    exit;
}

if (isset($_GET['sil'])) {
    try {
        $stmt = $db->prepare("DELETE FROM familyalar WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['sil']]);
        mesaj_ayarla('Familya silindi.');
    } catch (PDOException $e) {
        mesaj_ayarla('Bu familyaya bağlı bitkiler olduğu için silinemez.', 'hata');
    }
    header('Location: familyalar.php');
    exit;
}

$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM familyalar WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['duzenle']]);
    $duzenlenecek = $stmt->fetch();
}

$arama = trim($_GET['q'] ?? '');
$sql = "
    SELECT f.*, COUNT(b.id) AS bitki_sayisi
    FROM familyalar f
    LEFT JOIN bitkiler b ON b.familya_id = f.id
";
$params = [];
if ($arama !== '') {
    $sql .= " WHERE f.familya_adi LIKE :q";
    $params[':q'] = '%' . $arama . '%';
}
$sql .= " GROUP BY f.id ORDER BY f.familya_adi";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$liste = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="kutu">
    <h2><?= $duzenlenecek ? 'Familyayı Güncelle' : 'Yeni Familya Ekle' ?></h2>
    <form method="POST" class="satir">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php if ($duzenlenecek): ?>
            <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
        <?php endif; ?>
        <input type="text" name="familya_adi" placeholder="Familya adı" value="<?= e($duzenlenecek['familya_adi'] ?? '') ?>" required>
        <?php if ($duzenlenecek): ?>
            <button type="submit" name="guncelle">Güncelle</button>
            <a href="familyalar.php" class="btn btn-ikincil">İptal</a>
        <?php else: ?>
            <button type="submit" name="ekle">Ekle</button>
        <?php endif; ?>
    </form>
</div>

<div class="kutu">
    <h2>Familyalar (<?= count($liste) ?>)</h2>
    <form method="GET" class="satir">
        <input type="text" name="q" placeholder="Familya ara..." value="<?= e($arama) ?>">
        <button type="submit">Ara</button>
        <?php if ($arama !== ''): ?>
            <a href="familyalar.php" class="btn btn-ikincil">Temizle</a>
        <?php endif; ?>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Familya Adı</th>
                <th>Bağlı Bitki Sayısı</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($liste as $satir): ?>
                <tr>
                    <td><?= $satir['id'] ?></td>
                    <td><?= e($satir['familya_adi']) ?></td>
                    <td><?= $satir['bitki_sayisi'] ?></td>
                    <td>
                        <a href="familyalar.php?duzenle=<?= $satir['id'] ?>" class="islem-duzenle">Düzenle</a>
                        <a href="familyalar.php?sil=<?= $satir['id'] ?>" class="islem-sil" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
