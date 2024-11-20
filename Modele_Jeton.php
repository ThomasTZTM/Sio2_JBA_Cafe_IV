<?php
class Modele_Jeton {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function genererJeton($email) {
        $jeton = bin2hex(random_bytes(32));
        $expiration = time() + 3600;

        $stmt = $this->db->prepare("INSERT INTO jetons (email, jeton, expiration) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE jeton = ?, expiration = ?");
        $stmt->execute([$email, $jeton, $expiration, $jeton, $expiration]);

        return $jeton;
    }

    public function validerJeton($email, $jeton) {
        $stmt = $this->db->prepare("SELECT expiration FROM jetons WHERE email = ? AND jeton = ?");
        $stmt->execute([$email, $jeton]);
        $result = $stmt->fetch();

        if ($result && $result['expiration'] >= time()) {
            return true;
        }

        return false;
    }

    public function supprimerJeton($email) {
        $stmt = $this->db->prepare("DELETE FROM jetons WHERE email = ?");
        $stmt->execute([$email]);
    }
}
?>
