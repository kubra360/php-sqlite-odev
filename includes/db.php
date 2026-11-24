<?php
require_once __DIR__ . '/seed_data.php';

function get_db(): PDO {
    static $db = null;
    if ($db !== null) {
        return $db;
    }

    $dbDir = dirname(__DIR__) . '/database';
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $dbPath = $dbDir . '/botanik.sqlite';
    $yeniMi = !file_exists($dbPath);

    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON;');

    $db->exec("
        CREATE TABLE IF NOT EXISTS kategoriler (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kategori_adi TEXT NOT NULL UNIQUE
        );

        CREATE TABLE IF NOT EXISTS seralar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sera_adi TEXT NOT NULL UNIQUE,
            sorumlu_uzman TEXT
        );

        CREATE TABLE IF NOT EXISTS familyalar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            familya_adi TEXT NOT NULL UNIQUE
        );

        CREATE TABLE IF NOT EXISTS bitkiler (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            envanter_kodu TEXT NOT NULL UNIQUE,
            bitki_adi TEXT NOT NULL,
            latince_adi TEXT NOT NULL,
            anavatan TEXT,
            kategori_id INTEGER NOT NULL,
            sera_id INTEGER NOT NULL,
            familya_id INTEGER NOT NULL,
            durum TEXT,
            eklenme_tarihi TEXT,
            FOREIGN KEY (kategori_id) REFERENCES kategoriler(id),
            FOREIGN KEY (sera_id) REFERENCES seralar(id),
            FOREIGN KEY (familya_id) REFERENCES familyalar(id)
        );
    ");

    $sayi = (int)$db->query("SELECT COUNT(*) FROM bitkiler")->fetchColumn();
    if ($yeniMi || $sayi === 0) {
        veritabani_doldur($db);
    }

    return $db;
}
