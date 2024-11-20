<?php

use App\Modele\Modele_Jeton;
use App\Modele\Modele_Utilisateur;
use App\Vue\Vue_Connexion_Formulaire_client;
use App\Vue\Vue_Mail_ChoisirNouveauMdp;
use App\Vue\Vue_Mail_ReinitMdp;
use App\Vue\Vue_Structure_BasDePage;
use App\Vue\Vue_Structure_Entete;

// Ce contrôleur gère les actions liées aux visiteurs.
$Vue->setEntete(new Vue_Structure_Entete());

switch ($action) {
    case "reinitmdpconfirm":
        // Confirme l'envoi d'un mail de réinitialisation
        $Vue->addToCorps(new Vue_Mail_ReinitMdp());
        break;

    case "reinitmdp":
        // Génère un jeton et envoie un lien de réinitialisation
        if (isset($_REQUEST["email"])) {
            $email = $_REQUEST["email"];
            $utilisateur = Modele_Utilisateur::Utilisateur_Select_ParEmail($email);

            if ($utilisateur) {
                // Génération et enregistrement du jeton
                $token = bin2hex(random_bytes(16));
                Modele_Jeton::Jeton_Insert($utilisateur["idUtilisateur"], $token);

                // Envoi du mail
                $lien = "http://localhost:8080/index.php?action=choisirnouveaumdp&token=$token";
                mail($email, "Réinitialisation du mot de passe", "Cliquez sur le lien pour réinitialiser votre mot de passe : $lien");

                $Vue->addToCorps(new Vue_Mail_ReinitMdp());
            } else {
                $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Aucun utilisateur trouvé avec cet email."));
            }
        } else {
            $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Email manquant."));
        }
        break;

    case "choisirnouveaumdp":
        // Affiche le formulaire de choix du nouveau mot de passe
        if (isset($_REQUEST["token"])) {
            $token = $_REQUEST["token"];
            $jeton = Modele_Jeton::Jeton_Select_ParToken($token);

            if ($jeton) {
                // On stocke temporairement le token en session pour sécuriser la modification
                $_SESSION["token"] = $token;
                $Vue->addToCorps(new Vue_Mail_ChoisirNouveauMdp($token));
            } else {
                $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Lien de réinitialisation invalide ou expiré."));
            }
        } else {
            $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Aucun token fourni."));
        }
        break;

    case "choixmdp":
        // Traitement du formulaire de nouveau mot de passe
        if (isset($_SESSION["token"]) && isset($_REQUEST["mdp1"]) && isset($_REQUEST["mdp2"])) {
            $token = $_SESSION["token"];
            $mdp1 = $_REQUEST["mdp1"];
            $mdp2 = $_REQUEST["mdp2"];

            if ($mdp1 === $mdp2) {
                $jeton = Modele_Jeton::Jeton_Select_ParToken($token);

                if ($jeton) {
                    // Mise à jour du mot de passe
                    Modele_Utilisateur::Utilisateur_Update_MotDePasse($jeton["idUtilisateur"], password_hash($mdp1, PASSWORD_DEFAULT));

                    // Suppression du jeton pour qu'il ne soit plus réutilisable
                    Modele_Jeton::Jeton_Delete($token);

                    unset($_SESSION["token"]);
                    $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Mot de passe modifié avec succès. Veuillez vous connecter."));
                } else {
                    $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Jeton invalide ou expiré."));
                }
            } else {
                $Vue->addToCorps(new Vue_Mail_ChoisirNouveauMdp($token));
                $Vue->addToCorps("<p>Les mots de passe ne correspondent pas. Veuillez réessayer.</p>");
            }
        } else {
            $Vue->addToCorps(new Vue_Connexion_Formulaire_client("Erreur lors de la modification du mot de passe."));
        }
        break;

    default:
        $Vue->addToCorps(new Vue_Connexion_Formulaire_client());
        break;
}

$Vue->setBasDePage(new Vue_Structure_BasDePage());
