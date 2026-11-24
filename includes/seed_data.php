<?php
function veritabani_doldur(PDO $db): void {
    $kategoriler = [
        'Tropikal Bitkiler', 'Sukulentler', 'Tıbbi ve Aromatik', 'Akdeniz Florası',
        'Kaktüsler', 'Süs Bitkileri', 'Orman Ağaçları', 'Su Bitkileri',
        'Endemik Türler', 'Soğanlı Bitkiler', 'Eğrelti Otları', 'Sarmaşıklar'
    ];
    $stmtKat = $db->prepare("INSERT INTO kategoriler (kategori_adi) VALUES (?)");
    foreach ($kategoriler as $k) {
        $stmtKat->execute([$k]);
    }

    $seralar = [
        ['Sera-A1', 'Ayşe Yılmaz'], ['Sera-A2', 'Mehmet Kaya'], ['Sera-B1', 'Zeynep Demir'],
        ['Sera-B2', 'Can Öztürk'], ['Sera-C1', 'Elif Şahin'], ['Sera-C2', 'Burak Çelik'],
        ['Sera-D1', 'Selin Arslan'], ['Sera-D2', 'Emre Aydın'], ['Tropik Bölüm-1', 'Deniz Koç'],
        ['Tropik Bölüm-2', 'Merve Yıldız'], ['Kurak Bölge-1', 'Oğuzhan Kurt'], ['Kurak Bölge-2', 'Gizem Özkan'],
        ['Tıbbi Bahçe-1', 'Hakan Aksoy'], ['Açık Alan-1', 'Fatma Polat'], ['Açık Alan-2', 'Ali Doğan']
    ];
    $stmtSera = $db->prepare("INSERT INTO seralar (sera_adi, sorumlu_uzman) VALUES (?, ?)");
    foreach ($seralar as $s) {
        $stmtSera->execute($s);
    }

    $familyalar = [
        'Araceae', 'Asparagaceae', 'Asphodelaceae', 'Oleaceae', 'Lamiaceae',
        'Orchidaceae', 'Moraceae', 'Crassulaceae', 'Cactaceae', 'Lauraceae',
        'Myrtaceae', 'Fabaceae', 'Asteraceae', 'Malvaceae', 'Pinaceae',
        'Nymphaeaceae', 'Begoniaceae', 'Geraniaceae', 'Rosaceae', 'Liliaceae'
    ];
    $stmtFam = $db->prepare("INSERT INTO familyalar (familya_adi) VALUES (?)");
    foreach ($familyalar as $f) {
        $stmtFam->execute([$f]);
    }

    $ornekler = [
        ['Deve Tabanı', 'Monstera deliciosa', 'Orta Amerika', 1, 1, 1],
        ['Paşa Kılıcı', 'Dracaena trifasciata', 'Batı Afrika', 2, 3, 2],
        ['Barış Çiçeği', 'Spathiphyllum wallisii', 'Kolombiya', 1, 1, 1],
        ['Aloe Vera', 'Aloe barbadensis', 'Arap Yarımadası', 3, 4, 3],
        ['Zeytin Ağacı', 'Olea europaea', 'Akdeniz Havzası', 4, 14, 4],
        ['Lavanta', 'Lavandula angustifolia', 'Akdeniz', 3, 13, 5],
        ['Biberiye', 'Salvia rosmarinus', 'Akdeniz', 3, 13, 5],
        ['Adaçayı', 'Salvia officinalis', 'Balkanlar', 3, 13, 5],
        ['Kekik', 'Thymus vulgaris', 'Güney Avrupa', 3, 13, 5],
        ['Orkide', 'Phalaenopsis amabilis', 'Güneydoğu Asya', 1, 2, 6],
        ['Kauçuk Ağacı', 'Ficus elastica', 'Hindistan', 1, 9, 7],
        ['Benjamin', 'Ficus benjamina', 'Asya', 6, 2, 7],
        ['Yuka', 'Yucca elephantipes', 'Meksika', 6, 3, 2],
        ['Kurdele Çiçeği', 'Chlorophytum comosum', 'Güney Afrika', 6, 2, 2],
        ['Kalanşo', 'Kalanchoe blossfeldiana', 'Madagaskar', 2, 3, 8],
        ['Kaynana Dili', 'Opuntia ficus-indica', 'Meksika', 5, 11, 9],
        ['Altın Fıçı Kaktüsü', 'Echinocactus grusonii', 'Meksika', 5, 11, 9],
        ['Defne', 'Laurus nobilis', 'Akdeniz', 4, 14, 10],
        ['Mersin', 'Myrtus communis', 'Akdeniz', 4, 14, 11],
        ['Keçiboynuzu', 'Ceratonia siliqua', 'Akdeniz', 4, 15, 12],
        ['Papatya', 'Matricaria chamomilla', 'Avrupa', 3, 13, 13],
        ['Ihlamur', 'Tilia tomentosa', 'Anadolu', 7, 15, 14],
        ['Sedir', 'Cedrus libani', 'Toros Dağları', 7, 15, 15],
        ['Nilüfer', 'Nymphaea alba', 'Avrupa', 8, 5, 16],
        ['Begonya', 'Begonia semperflorens', 'Brezilya', 6, 6, 17],
        ['Sardunya', 'Pelargonium zonale', 'Güney Afrika', 6, 14, 18],
        ['Kuşburnu', 'Rosa canina', 'Anadolu', 9, 14, 19],
        ['Ters Lale', 'Fritillaria imperialis', 'Anadolu', 10, 7, 20],
        ['Yeşim Bitkisi', 'Crassula ovata', 'Güney Afrika', 2, 4, 8],
        ['Salon Sarmaşığı', 'Epipremnum aureum', 'Solomon Adaları', 12, 10, 1]
    ];

    $durumlar = ['Sağlıklı', 'Çiçeklenmede', 'Bakımda'];
    $stmtBitki = $db->prepare("
        INSERT INTO bitkiler (envanter_kodu, bitki_adi, latince_adi, anavatan, kategori_id, sera_id, familya_id, durum, eklenme_tarihi)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    for ($i = 1; $i <= 100; $i++) {
        $b = $ornekler[($i - 1) % count($ornekler)];
        $kod = 'BTK-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
        $ad = $i <= 30 ? $b[0] : $b[0] . ' (' . ceil($i / 30) . '. Koleksiyon)';
        $durum = $durumlar[$i % 3];
        $gun = str_pad((string)(($i % 28) + 1), 2, '0', STR_PAD_LEFT);
        $stmtBitki->execute([$kod, $ad, $b[1], $b[2], $b[3], $b[4], $b[5], $durum, "2026-10-$gun"]);
    }
}
