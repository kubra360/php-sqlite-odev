<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    csrf_kontrol();
    $ad = trim($_POST['sera_adi']);
    $uzman = trim($_POST['sorumlu_uzman']);
    if ($ad !== '') {
        try {
            $stmt = $db->prepare("INSERT INTO seralar (sera_adi, sorumlu_uzman) VALUES (:ad, :uzman)");
            $stmt->execute([':ad' => $ad, ':uzman' => $uzman]);
            mesaj_ayarla('Sera eklendi.');
        } catch (PDOException $e) {
            mesaj_ayarla('Bu sera zaten mevcut.', 'hata');
        }
    }
    header('Location: seralar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle'])) {
    csrf_kontrol();
    $stmt = $db->prepare("UPDATE seralar SET sera_adi = :ad, sorumlu_uzman = :uzman WHERE id = :id");
    $stmt->execute([
        ':ad'    => trim($_POST['sera_adi']),
        ':uzman' => trim($_POST['sorumlu_uzman']),
        ':id'    => (int)$_POST['id']
    ]);
    mesaj_ayarla('Sera güncellendi.');
    header('Location: seralar.php');
    exit;
}

if (isset($_GET['sil'])) {
    try {
        $stmt = $db->prepare("DELETE FROM seralar WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['sil']]);
        mesaj_ayarla('Sera silindi.');
    } catch (PDOException $e) {
        mesaj_ayarla('Bu seraya bağlı bitkiler olduğu için silinemez.', 'hata');
    }
    header('Location: seralar.php');
    exit;
}

$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM seralar WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['duzenle']]);
    $duzenlenecek = $stmt->fetch();
}

$arama = trim($_GET['q'] ?? '');
$sql = "
    SELECT s.*, COUNT(b.id) AS bitki_sayisi
    FROM seralar s
    LEFT JOIN bitkiler b ON b.sera_id = s.id
";
$params = [];
if ($arama !== '') {
    $sql .= " WHERE s.sera_adi LIKE :q OR s.sorumlu_uzman LIKE :q";
    $params[':q'] = '%' . $arama . '%';
}
$sql .= " GROUP BY s.id ORDER BY s.sera_adi";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$liste = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="kutu">
    <h2><?= $duzenlenecek ? 'Serayı Güncelle' : 'Yeni Sera Ekle' ?></h2>
    <form method="POST" class="satir">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php if ($duzenlenecek): ?>
            <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
        <?php endif; ?>
        <input type="text" name="sera_adi" placeholder="Sera adı" value="<?= e($duzenlenecek['sera_adi'] ?? '') ?>" required>
        <input type="text" name="sorumlu_uzman" placeholder="Sorumlu uzman" value="<?= e($duzenlenecek['sorumlu_uzman'] ?? '') ?>">
        <?php if ($duzenlenecek): ?>
            <button type="submit" name="guncelle">Güncelle</button>
            <a href="seralar.php" class="btn btn-ikincil">İptal</a>
        <?php else: ?>
            <button type="submit" name="ekle">Ekle</button>
        <?php endif; ?>
    </form>
</div>

<div class="kutu">
    <h2>Seralar (<?= count($liste) ?>)</h2>
    <form method="GET" class="satir">
        <input type="text" name="q" placeholder="Sera veya sorumlu ara..." value="<?= e($arama) ?>">
        <button type="submit">Ara</button>
        <?php if ($arama !== ''): ?>
            <a href="seralar.php" class="btn btn-ikincil">Temizle</a>
        <?php endif; ?>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Sera Adı</th>
                <th>Sorumlu Uzman</th>
                <th>Bağlı Bitki Sayısı</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($liste as $satir): ?>
                <tr>
                    <td><?= $satir['id'] ?></td>
                    <td><?= e($satir['sera_adi']) ?></td>
                    <td><?= e($satir['sorumlu_uzman']) ?></td>
                    <td><?= $satir['bitki_sayisi'] ?></td>
                    <td>
                        <a href="seralar.php?duzenle=<?= $satir['id'] ?>" class="islem-duzenle">Düzenle</a>
                        <a href="seralar.php?sil=<?= $satir['id'] ?>" class="islem-sil" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
