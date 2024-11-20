<?php
require_once 'Modele_Jeton.php';
try {
    // Pas la vrai connexion car pas dans le bon projet de café
    $db = new PDO('mysql:host=localhost;dbname=CAFE_2024', 'username', 'password');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $modeleJeton = new Modele_Jeton($db);

    $email = "test@example.com";
    $jeton = $modeleJeton->genererJeton($email);
    echo "Jeton généré : $jeton\n";

    $isValid = $modeleJeton->validerJeton($email, $jeton);
    echo $isValid ? "Le jeton est valide\n" : "Le jeton est invalide\n";

    $modeleJeton->supprimerJeton($email);
    $isValidAfterDelete = $modeleJeton->validerJeton($email, $jeton);
    echo $isValidAfterDelete ? "Le jeton est toujours valide\n" : "Le jeton a été supprimé\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
