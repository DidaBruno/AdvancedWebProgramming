<?php

class StupScraper
{
    public static function fetch($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (NWP lab vjezba 1)',
            CURLOPT_ENCODING       => '', 
        ]);
        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($html === false || $code >= 400) {
            fwrite(STDERR, "Greška pri dohvaćanju $url (HTTP $code) $err\n");
            return null;
        }
        return $html;
    }

    private static function loadXPath($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        return new DOMXPath($dom);
    }

    private static function oibFromSrc($src)
    {
        if (preg_match('~/(\d{11})(?:-\d+x\d+)?\.(?:png|jpe?g|gif|webp)~i', $src, $m)) {
            return $m[1];
        }
        return null;
    }

    private static function isWorkLink($href)
    {
        return (bool) preg_match('~/\d{4}/\d{2}/\d{2}/[^/]+/?$~', $href);
    }


    public static function parseListing($html)
    {
        $xp = self::loadXPath($html);
        $items = [];
        $seen  = [];

        // 1) sve poveznice na radove koje imaju tekst (naslov)
        foreach ($xp->query('//a[@href]') as $a) {
            $href  = trim($a->getAttribute('href'));
            $title = trim(preg_replace('/\s+/u', ' ', $a->textContent));
            if ($title === '' || !self::isWorkLink($href) || isset($seen[$href])) {
                continue;
            }
            $seen[$href] = true;

            // 2) OIB: krece od poveznice prema gore dok ne pronade kontejner koji sadrzi sliku s OIB-om
            $oib  = null;
            $node = $a->parentNode;
            while ($node instanceof DOMElement && $node->nodeName !== 'body') {
                $imgs = $xp->query('.//img[@src]', $node);
                $links = [];
                foreach ($xp->query('.//a[@href]', $node) as $l) {
                    $h = trim($l->getAttribute('href'));
                    if (self::isWorkLink($h)) { $links[$h] = true; }
                }
                if (count($links) > 1) { break; }
                foreach ($imgs as $img) {
                    $o = self::oibFromSrc($img->getAttribute('src'));
                    if ($o) { $oib = $o; break 2; }
                }
                $node = $node->parentNode;
            }

            $items[] = [
                'naziv_rada' => $title,
                'link_rada'  => $href,
                'oib_tvrtke' => $oib,
            ];
        }

        // 3) ako OIB-ovi nisu povezani, spoji po redoslijedu
        $missing = array_filter($items, fn($i) => $i['oib_tvrtke'] === null);
        if ($missing) {
            $oibs = [];
            foreach ($xp->query('//img[@src]') as $img) {
                $o = self::oibFromSrc($img->getAttribute('src'));
                if ($o) { $oibs[] = $o; }
            }
            if (count($oibs) === count($items)) {
                foreach ($items as $k => $_) { $items[$k]['oib_tvrtke'] = $oibs[$k]; }
            }
        }

        return $items;
    }


    // iz HTML-a stranice samog rada izvlaci tekst rada
    public static function parseText($html)
    {
        $xp = self::loadXPath($html);

        // uklanja skripte, stilove i navigaciju
        foreach ($xp->query('//script|//style|//noscript|//nav|//header|//footer') as $n) {
            $n->parentNode->removeChild($n);
        }

        // najceseci WordPress kontejneri sadrzaja, od najspecificnijeg
        $candidates = [
            '//*[contains(concat(" ", normalize-space(@class), " "), " entry-content ")]',
            '//*[contains(concat(" ", normalize-space(@class), " "), " post-content ")]',
            '//article',
            '//main',
            '//body',
        ];
        foreach ($candidates as $q) {
            $res = $xp->query($q);
            if ($res->length > 0) {
                // spaja tekstne cvorove s razmakom da se odlomci ne slijepe
                $parts = [];
                foreach ($xp->query('.//text()', $res->item(0)) as $t) {
                    $parts[] = $t->nodeValue;
                }
                $text = trim(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
                if ($text !== '') {
                    return $text;
                }
            }
        }
        return '';
    }
}
