<?php
require_once __DIR__ . '/StupScraper.php';

$listing = <<<HTML
<html><body>
<article class="post">
  <a href="https://stup.ferit.hr/index.php/2016/12/28/tehnologija-rada-pod-naponom-na-niskonaponskim-postrojenjima-u-republici-hrvatskoj/">
    <img width="320" height="202" src="https://stup.ferit.hr/wp-content/uploads/2016/11/08393361027.png" class="attachment-blog-medium" alt=""
         srcset="https://stup.ferit.hr/wp-content/uploads/2016/11/08393361027-200x126.png 200w" />
  </a>
  <h2><a href="https://stup.ferit.hr/index.php/2016/12/28/tehnologija-rada-pod-naponom-na-niskonaponskim-postrojenjima-u-republici-hrvatskoj/"> Tehnologija rada pod naponom na niskonaponskim postrojenjima u Republici Hrvatskoj </a></h2>
</article>
</body></html>
HTML;

$items = StupScraper::parseListing($listing);
print_r($items);

assert(count($items) === 1);
assert($items[0]['oib_tvrtke'] === '08393361027');
assert($items[0]['naziv_rada'] === 'Tehnologija rada pod naponom na niskonaponskim postrojenjima u Republici Hrvatskoj');

$page = '<html><body><nav>Menu</nav><div class="entry-content"><p>Prvi odlomak.</p><p>Drugi odlomak.</p></div></body></html>';
assert(StupScraper::parseText($page) === 'Prvi odlomak. Drugi odlomak.');

echo "SVE OK\n";
