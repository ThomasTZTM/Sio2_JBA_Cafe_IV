<?php

use App\Modele\Modele_Entreprise;
use App\Modele\Modele_Salarie;
use App\Modele\Modele_Utilisateur;
use App\Vue\Vue_Connexion_Formulaire_client;
use App\Vue\Vue_Mail_Confirme;
use App\Vue\Vue_Mail_ReinitMdp;
use App\Vue\Vue_Menu_Administration;
use App\Vue\Vue_Structure_BasDePage;
use PHPMailer\PHPMailer\PHPMailer;

// Ce contrôleur gère le formulaire de connexion pour les visiteurs

$Vue->setEntete(new Vue_Structure_Entete());

switch ($action) {
    case "reinitmdpconfirm":
        $Vue->addToCorps(new Vue_Mail_Confirme());
        break;

    case "reinitmdp":
        if (isset($_POST["email"])) {
            $email = $_POST["email"];

            // Vérifier si l'utilisateur existe
            $utilisateur = Modele_Utilisateur::Utilisateur_Select_ParEmail($email);
            if ($utilisateur) {
                // Générer le jeton et l'enregistrer
                $jeton = Modele_Jeton::genererJeton($email);

                // Construire le lien avec le port de débogage
                $lien = "http://localhost:" . $_SERVER['SERVER_PORT'] . "/reinitialisation_mdp.php?token=$jeton";

                // Envoyer l'e-mail
                $mail = new PHPMailer();
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.votre-domaine.com'; // Remplacez par votre serveur SMTP
                    $mail->SMTPAuth = true;
                    $mail->Username = 'votre-email@example.com'; // Votre email
                    $mail->Password = 'votre-mot-de-passe'; // Mot de passe de l'email
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('noreply@votre-domaine.com', 'Votre Application');
                    $mail->addAddress($email);

                    $mail->Subject = 'Réinitialisation de votre mot de passe';
                    $mail->Body = "Bonjour,\n\nCliquez sur le lien ci-dessous pour réinitialiser votre mot de passe :\n\n$lien\n\nCordialement,\nL'équipe.";

                    if ($mail->send()) {
                        $Vue->addToCorps(new Vue_Mail_Confirme());
                    } else {
                        $Vue->addToCorps(new Vue_Mail_ReinitMdp("Erreur lors de l'envoi de l'e-mail."));
                    }
                } catch (Exception $e) {
                    $Vue->addToCorps(new Vue_Mail_ReinitMdp("Erreur : " . $mail->ErrorInfo));
                }
            } else {
                $Vue->addToCorps(new Vue_Mail_ReinitMdp("L'e-mail n'existe pas dans notre système."));
            }
        } else {
            $Vue->addToCorps(new Vue_Mail_ReinitMdp("Veuillez fournir une adresse e-mail."));
        }
        break;

    case "Se connecter":
        if (isset($_REQUEST["compte"]) && isset($_REQUEST["password"])) {
            // Vérification de la connexion
            $utilisateur = Modele_Utilisateur::Utilisateur_Select_ParLogin($_REQUEST["compte"]);
            if ($utilisateur) {
                if ($utilisateur["desactiver"] == 0) {
                    if ($_REQUEST["password"] == $utilisateur["motDePasse"]) {
                        $_SESSION["idUtilisateur"] = $utilisateur["idUtilisateur"];
                        $_SESSION["idCategorie_utilisateur"] = $utilisateur["idCategorie_utilisateur"];
                        if (Modele_Utilisateur::Utilisateur_Select_RGPD($_SESSION["idUtilisateur"]) == 0) {
                            include "./Controleur/Controleur_AccepterRGPD.php";
                        } else {
                            switch ($utilisateur["idCategorie_utilisateur"]) {
                                case 1:
                                    $_SESSION["typeConnexionBack"] = "administrateurLogiciel";
                                    $Vue->setMenu(new Vue_Menu_Administration($_SESSION["typeConnexionBack"]));
                                    break;
                                case 2:
                                    $_SESSION["typeConnexionBack"] = "gestionnaireCatalogue";
                                    $Vue->setMenu(new Vue_Menu_Administration($_SESSION["typeConnexionBack"]));
                                    $Vue->addToCorps(new \App\Vue\Vue_AfficherMessage("Bienvenue " . $_REQUEST["compte"]));
                                    break;
                                case 3:
                                    $_SESSION["typeConnexionBack"] = "entrepriseCliente";
                                    $_SESSION["idEntreprise"] = Modele_Entreprise::Entreprise_Select_Par_IdUtilisateur($_SESSION["idUtilisateur"])["idEntreprise"];
                                    include "./Controleur/Controleur_Gerer_Entreprise.php";
                                    break;
                                case 4:
                                    $_SESSION["typeConnexionBack"] = "salarieEntrepriseCliente";
                                    $_SESSION["idSalarie"] = $utilisateur["idUtilisateur"];
                                    $_SESSION["idEntreprise"] = Modele_Salarie::Salarie_Select_byId($_SESSION["idUtilisateur"])["idEntreprise"];
                                    include "./Controleur/Controleur_Catalogue_client.php";
                                    break;
                                case 5:
                                    $_SESSION["typeConnexionBack"] = "commercialCafe";
                                    $Vue->setMenu(new Vue_Menu_Administration($_SESSION["typeConnexionBack"]));
                                    break;
                            }
                        }
                    } else {
                        $msgError = "Mot de passe erroné";
                        $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));
                    }
                } else {
                    $msgError = "Compte désactivé";
                    $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));
                }
            } else {
                $msgError = "Identification invalide";
                $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));
            }
        } else {
            $msgError = "Identification incomplète";
            $Vue->addToCorps(new Vue_Connexion_Formulaire_client($msgError));
        }
        break;

    default:
        $Vue->addToCorps(new Vue_Connexion_Formulaire_client());
        break;
}

$Vue->setBasDePage(new Vue_Structure_BasDePage());
?>
