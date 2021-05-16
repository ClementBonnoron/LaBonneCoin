<?php session_start();
//  On met dans le code dans le tampon
ob_start();
unset($_SESSION['access']);
?>

<!DOCTYPE html>
<html>
  
  <head>
    <title>Vérification de l'inscription</title>
    <meta charset="utf-8" />
  </head>
  
  <body>
    
    <?php
      //  Récupère les informations du formulaire
      $prenom = $_POST['prenom'];
      $nom = $_POST['nom'];
      $identifiant = $_POST['identifiant'];
      $mdp = $_POST['mdp'];
      $conf_mdp = $_POST['conf_mdp'];
      $tel = $_POST['tel'];
      $adresse = $_POST['adresse'];
      $ville = $_POST['ville'];
      $email = $_POST['email'];
      $errno = false;
      
      //  Connexion au serveur local et codate en utf8 par sécurité
      $connexion = mysqli_connect("localhost","root","","localhost");
      $res = mysqli_query($connexion,"SET NAMES UTF8");
      $res = mysqli_query($connexion,"SET CHARACTER UTF8");
      $requete = "SELECT * FROM inscription WHERE id='$identifiant'";
      $res = mysqli_query($connexion,$requete);
      
      //  Si l'identifiant choisi est déjà sélectionné, vide le tampon et ce
      //  dirige vers la page d'inscription
      while ($etu = mysqli_fetch_array($res)){
        ob_end_clean();
        $_SESSION['error']="Identifiant déjà utilisé<br/>Vous devez créer un nouveau profil";
        header("location:http://localhost/projet/part_site/page/inscription.php");
      }
      
      //  Si le mdp et la confirmation du mdp est différente, ou si le
      //  téléphone n'est pas sous une forme d'un numéro de téléphone,
      //  ous si l'email n'est pas sous la forme __@__.__, effectue le code
      //  suivant, sinon inscrit la personne dans la bdd
      if (($mdp != $conf_mdp) || (strlen($tel) != 10)
        || !(filter_var($email, FILTER_VALIDATE_EMAIL)) ){
        echo "erreur, confirmation du mot de passe différent ou telephone
        incompatible ou adresse inconnue<br/>";
        
      } else {
        $requete = "INSERT INTO inscription
                    SET nom='$nom', prenom='$prenom', id='$identifiant', mdp='$mdp',
                        tel='$tel', adresse='$adresse', ville='$ville',
                        email='$email', grade='membre'";
        mysqli_query($connexion,$requete);
        echo "Inscription de {$identifiant} correctement effectué<br>";
        echo "<a href='../page_principale.php'>Retourner a la page principale</a>";
        
        
      }
      mysqli_close($connexion);
    ?>
  </body>
  
</html>

<?php
//  Affiche le tampon en le vidant
ob_end_flush();
?>
