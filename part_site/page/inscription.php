<?php session_start();
unset($_SESSION['access']);
?>

<!DOCTYPE html>
<html>
  
  <head>
    <title>Inscription</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/css_page_inscription.css">
  </head>
  
  <body>
    
    <div class="head">
       <i><h1>LaBonneCoin</h1></i>
    </div>
    
    <?php
    
      //  Si une erreur existe, provient de la page 'inscription_base', et
      //  affiche une erreur
      if (isset($_SESSION['error'])){
        echo '<div class="erreur">' .$_SESSION['error']. '</div>';
        unset($_SESSION['error']);
      }
    ?>
    
    <!--
      Affiche le formulaire pour l'inscription de la personne
    -->
    <div class="formulaire">
      <h1>Formulaire d'inscription</h1>
      
      <form action="../page/inscription_base.php" method="post">
        <p>(*) Champs obligatoire</p>
        <p>Nom : <input type="text" name="nom" required autofocus /> (*) </p>
        <p>Prénom :<input type="text" name="prenom" required /> (*) </p>
        <p>Identifiant :<input type="text" name="identifiant" required /> (*)</p>
        <p>Mot de passe : <input type="password" name="mdp" required /> (*) </p>
        <p>Confirmation du mot de passe : <input type="password" name="conf_mdp" required /> (*) </p>
        <p>N° de Téléphone :<input type="text" name="tel" required /> (*) </p>
        <p>Adresse :<input type="text" name="adresse" /></p>
        <p>Ville :<input type="text" name="ville" /></p>
        <p>Email :<input type="text" name="email" required /> (*) </p>
        
        <p>&nbsp <input name="verification" type="submit" value="Inscription"></p>
      </form>
    </div>
    
    <a href="../page_principale.php" class="page_accueil">Page d'accueil</a>
    
    <div class="footer">
      <a href="./page/contact.php">Nous contacter</a>
    </div>
    
  </body>
  
</html>
