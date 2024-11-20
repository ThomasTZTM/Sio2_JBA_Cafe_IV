<?php
namespace App\Vue;

use App\Utilitaire\Vue_Composant;

class Vue_Connexion_Formulaire_client extends Vue_Composant
{
    private string $msgErreur;

    public function __construct(string $msgErreur = "")
    {
        $this->msgErreur = $msgErreur;
    }

    function donneTexte(): string
    {
        $str = "
<h1>Café : Connexion</h1>
<div style='width: 50%; display: block; margin: auto;'>  
  <form action='index.php' method='post'>
  
                <h1>Connexion</h1>
                
                <label><b>Compte</b></label>
                <input type='text' placeholder='Identifiant du compte' name='compte' required>

                <label><b>Mot de passe</b></label>
                <input type='password' placeholder='Mot de passe' name='password' required>
                
                <button type='submit' id='submit' name='action' value='Se connecter'>
                    Se connecter
                </button>";

        if ($this->msgErreur != "") {
            $str .= "<label style='color: red;'><b>Erreur : $this->msgErreur</b></label>";
        }

        $str .= "</form>";

        $str .= "
<form action='index.php' method='post'>

<h1>Mot de passe perdu ?</h1>
<p>Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation.</p>

<label><b>Adresse e-mail</b></label>
<input type='email' placeholder='Votre adresse e-mail' name='email' required>

<button type='submit' id='submit' name='action' value='reinitmdp'>
    Réinitialiser le mot de passe
</button>
</form>";

        $str .= "
</div>";

        return $str;
    }
}
