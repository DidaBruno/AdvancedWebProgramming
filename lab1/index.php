<?php

require_once __DIR__ . '/DiplomskiRadovi.php';
require_once __DIR__ . '/StupScraper.php';

header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);

$novi = 0;
$preskoceni = 0;
$greske = [];

for ($i = PAGE_FROM; $i <= PAGE_TO; $i++) {
    $url  = BASE_URL . $i;
    $html = StupScraper::fetch($url);
    if ($html === null) {
        $greske[] = "Stranica nije dohvaćena: $url";
        continue;
    }

    foreach (StupScraper::parseListing($html) as $rad) {
        // tekst rada nalazi se na linku rada
        $radHtml = StupScraper::fetch($rad['link_rada']);
        $rad['tekst_rada'] = $radHtml !== null ? StupScraper::parseText($radHtml) : '';

        $objekt = new DiplomskiRadovi();
        $objekt->create($rad);
        if ($objekt->save()) { $novi++; } else { $preskoceni++; }

        usleep(200000); // 0,2 s pauze da ne opterecujemo server
    }
}

$radovi = (new DiplomskiRadovi())->read();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Diplomski radovi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; vertical-align: top; text-align: left; }
        th { background: #f0f0f0; }
        .err { color: #b00020; }
    </style>
</head>
<body>
<h1>Diplomski radovi</h1>
<p>Novo spremljeno: <strong><?= $novi ?></strong>, već postojalo: <strong><?= $preskoceni ?></strong>,
   ukupno u bazi: <strong><?= count($radovi) ?></strong></p>
<?php foreach ($greske as $g): ?>
    <p class="err"><?= htmlspecialchars($g) ?></p>
<?php endforeach; ?>

<table>
    <tr><th>ID</th><th>Naziv rada</th><th>Tekst rada</th><th>Link rada</th><th>OIB tvrtke</th></tr>
    <?php foreach ($radovi as $r): ?>
    <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['naziv_rada']) ?></td>
        <td><?= htmlspecialchars(mb_strimwidth((string)$r['tekst_rada'], 0, 300, '…')) ?></td>
        <td><a href="<?= htmlspecialchars($r['link_rada']) ?>"><?= htmlspecialchars($r['link_rada']) ?></a></td>
        <td><?= htmlspecialchars((string)$r['oib_tvrtke']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
