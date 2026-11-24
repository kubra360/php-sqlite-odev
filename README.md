# Botanik Bahçesi Kataloğu — PHP & SQLite CRUD Projesi

PHP (PDO) ve SQLite kullanılarak geliştirilmiş, harici bağımlılığı olmayan ilişkisel **Botanik Bahçesi Kataloğu** web uygulamasıdır.

## Özellikler

- **İlişkisel Veritabanı Yapısı (4 Tablo)**:
  - `bitkiler` (100 örnek bitki kaydı — kategori, sera ve familyaya foreign key ile bağlı)
  - `kategoriler` (12 kategori kaydı)
  - `seralar` (15 sera kaydı)
  - `familyalar` (20 familya kaydı)
- **Tüm Tablolarda Tam CRUD + Arama**:
  - **Bitkiler (`index.php`)**: Listeleme, sayfalama, arama, kategori/sera/familya filtreleme, ekleme, güncelleme ve silme.
  - **Kategoriler (`kategoriler.php`)**: Listeleme, bağlı bitki sayısı, arama, ekleme, güncelleme ve güvenli silme.
  - **Seralar (`seralar.php`)**: Listeleme, bağlı bitki sayısı, arama, ekleme, güncelleme ve güvenli silme.
  - **Familyalar (`familyalar.php`)**: Listeleme, bağlı bitki sayısı, arama, ekleme, güncelleme ve güvenli silme.
- **Otomatik Kurulum**: `database/botanik.sqlite` dosyası ilk açılışta tabloları ve 100 bitki kaydını otomatik oluşturur.

## Kurulum ve Çalıştırma

```bash
php -S localhost:8000
