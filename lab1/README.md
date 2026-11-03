# NWP – Laboratorijska vježba 1

Klasa `DiplomskiRadovi` (PHP) koja cURL-om dohvaća radove sa
`https://stup.ferit.hr/index.php/zavrsni-radovi/page/2` … `/page/6`, parsira ih i sprema u MySQL.

## Datoteke
| Datoteka | Sadržaj |
|---|---|
| `iRadovi.php` | sučelje s metodama `create`, `save`, `read` |
| `DiplomskiRadovi.php` | klasa s varijablama `naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke` |
| `StupScraper.php` | cURL dohvat + parser (ugrađeni DOMDocument/XPath, bez vanjskih biblioteka) |
| `config.php` | postavke baze i raspon stranica (2–6) |
| `baza.sql` | baza `radovi` i tablica `diplomski_radovi` (+ `id`) |
| `index.php` | glavna skripta: dohvat → `create` → `save` → `read` → ispis tablice |
| `test_parser.php` | test parsera bez interneta i baze |

## Pokretanje (XAMPP/WAMP)
1. Uključite Apache i MySQL; u `php.ini` mora biti uključen `extension=curl` (i `mysqli`).
2. Uvezite `baza.sql` (phpMyAdmin → Import, ili `mysql -u root < baza.sql`).
3. Po potrebi uredite korisnika/lozinku u `config.php`.
4. Mapu stavite u `htdocs` i otvorite `http://localhost/vjezba1/index.php`
   (ili `php index.php` iz naredbenog retka).

Ponovno pokretanje ne stvara duplikate (radovi se prepoznaju po `link_rada`).
