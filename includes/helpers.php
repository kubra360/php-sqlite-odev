<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $metin): string {
    return htmlspecialchars((string)$metin, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_kontrol(): void {
    $gelen = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $gelen)) {
        die('Geçersiz güvenlik anahtarı (CSRF).');
    }
}

function mesaj_ayarla(string $metin, string $tip = 'bilgi'): void {
    $_SESSION['flash_mesaj'] = ['metin' => $metin, 'tip' => $tip];
}

function mesaj_goster(): void {
    if (!empty($_SESSION['flash_mesaj'])) {
        $m = $_SESSION['flash_mesaj'];
        unset($_SESSION['flash_mesaj']);
        $sinif = $m['tip'] === 'hata' ? 'mesaj hata' : 'mesaj';
        echo '<div class="' . $sinif . '">' . e($m['metin']) . '</div>';
    }
}
