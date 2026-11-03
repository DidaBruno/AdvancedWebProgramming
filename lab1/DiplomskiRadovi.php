<?php
require_once __DIR__ . '/iRadovi.php';
require_once __DIR__ . '/config.php';

// klasa DiplomskiRadovi sadrzi jedan diplomski rad s portala stup.ferit.hr
// implementira sucelje iRadovi (create, save, read)
class DiplomskiRadovi implements iRadovi
{
    private $naziv_rada;
    private $tekst_rada;
    private $link_rada;
    private $oib_tvrtke;

    /** @var mysqli|null */
    private $conn;

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->create($data);
        }
    }

    // veza prema bazi (stvara se pri prvom koristenju)
    private function db()
    {
        if ($this->conn === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $this->conn->set_charset('utf8mb4');
        }
        return $this->conn;
    }

     // sprema podatke dohvacene sa stranice u varijable objekta
     // @param array $data kljucevi: naziv_rada, tekst_rada, link_rada, oib_tvrtke
    public function create($data)
    {
        $this->naziv_rada = $data['naziv_rada'] ?? null;
        $this->tekst_rada = $data['tekst_rada'] ?? null;
        $this->link_rada  = $data['link_rada']  ?? null;
        $this->oib_tvrtke = $data['oib_tvrtke'] ?? null;
        return $this;
    }

    // sprema rad u tablicu diplomski_radovi (preskace rad ciji link vec postoji)
    // @return bool true ako je novi zapis upisan, false ako vec postoji
    public function save()
    {
        $db = $this->db();

        $chk = $db->prepare('SELECT id FROM diplomski_radovi WHERE link_rada = ? LIMIT 1');
        $chk->bind_param('s', $this->link_rada);
        $chk->execute();
        $chk->store_result();
        $exists = $chk->num_rows > 0;
        $chk->close();
        if ($exists) {
            return false;
        }

        $stmt = $db->prepare(
            'INSERT INTO diplomski_radovi (naziv_rada, tekst_rada, link_rada, oib_tvrtke)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('ssss', $this->naziv_rada, $this->tekst_rada, $this->link_rada, $this->oib_tvrtke);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    // dohvaca sve radove iz tablice diplomski_radovi
    // @return array lista asocijativnih polja
    public function read()
    {
        $res = $this->db()->query(
            'SELECT id, naziv_rada, tekst_rada, link_rada, oib_tvrtke FROM diplomski_radovi ORDER BY id'
        );
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Getteri
    public function getNazivRada() { return $this->naziv_rada; }
    public function getTekstRada() { return $this->tekst_rada; }
    public function getLinkRada()  { return $this->link_rada; }
    public function getOibTvrtke() { return $this->oib_tvrtke; }
}
