<?php session_start();
unset($_SESSION['access']);
?>

<!DOCTYPE html>
<html>
  
  <head>
    <title>sans titre</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/css_page_inscription.css">
    <?php
      function AfficherChangement(){
        echo "<span style='color:red;float:center;'> -- Changement d'information --</span>";
      }
      
    ?>
  </head>
  
  <body>
    
    <div class="head">
      <i><h1>LaBonneCoin</h1></i>
    </div>
    
    <?php
    
      //  Connexion au serveur local et codage en utf8 par sécurité
      $connexion = mysqli_connect("localhost","root","","localhost");
      $res = mysqli_query($connexion,"SET NAMES UTF8");
      $res = mysqli_query($connexion,"SET CHARACTER UTF8");
      
      //  Récupère toutes les informations de la personne connecté
      $requete = "SELECT * FROM inscription WHERE id='" .$_SESSION['session_id']. "'";
      $res = mysqli_query($connexion,$requete);
      $perso = mysqli_fetch_array($res);
            
      $modification = false;
      
      //  Affiche erreur si mauvaise confirmation du mdp, mauvaise adresse mail,
      //  et/ou mauvais numéro de téléphone
      if (isset($_POST['verification_ok']) && (($_POST['mdp'] != $_POST['conf_mdp'])
        || (strlen($_POST['tel']) != 10) || !(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) ){
          echo "<div class='erreur'>";
        if ($_POST['mdp'] != $_POST['conf_mdp']){
          echo "Confirmation du mot de passe erronée<br>";
        }
        if (strlen($_POST['tel']) != 10){
          echo "Erreur de saisie du téléphone<br>";
        }
        if (!(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))){
          echo "Email sous une forme incorrect<br>";
        }
        
        unset($_POST['verification_ok']);
        echo "</div>";
      }
      
      //  Si 1er envoie du formulaire, sauvergarde temporairement les valeurs
      //  dans la session
      if (isset($_POST['verification_ok']) && ($_POST['mdp'] == $_POST['conf_mdp'])){
        $_SESSION['confirmation'] = true;
        
        if ($_POST['nom'] != $perso['nom'])
          {$_SESSION['tmp_nom'] = $_POST['nom'];$modification = true;}
        if ($_POST['prenom'] != $perso['prenom'])
          {$_SESSION['tmp_prenom'] = $_POST['prenom'];$modification = true;}
        if ($_POST['identifiant'] != $perso['id'])
          {$_SESSION['tmp_id'] = $_POST['identifiant'];$modification = true;}
        if ($_POST['mdp'] != $perso['mdp'])
          {$_SESSION['tmp_mdp'] = $_POST['mdp'];$modification = true;}
        if ($_POST['conf_mdp'] != $perso['mdp'])
          {$_SESSION['tmp_conf_mdp'] = $_POST['conf_mdp'];$modification = true;}
        if ($_POST['tel'] != $perso['tel'])
          {$_SESSION['tmp_tel'] = $_POST['tel'];$modification = true;}
        if ($_POST['adresse'] != $perso['adresse'])
          {$_SESSION['tmp_adresse'] = $_POST['adresse'];$modification = true;}
        if ($_POST['ville'] != $perso['ville'])
          {$_SESSION['tmp_ville'] = $_POST['ville'];$modification = true;}
        if ($_POST['email'] != $perso['email'])
          {$_SESSION['tmp_email'] = $_POST['email'];$modification = true;}
      
      //  Si envoie du 2nd formulaire pour confirmation ou annulation,
      //  exécute le code suivant
      } else if (isset($_POST['verification_confirmation']) || isset($_POST['annuler'])){
        
        //  Si il y a bien eu une modification et si ce n'est pas une annulation
        //  création de la requête pour modifier les valeurs dans la bdd de
        //  la personne connecté
        if (!empty($_SESSION['confirmation']) && empty($_POST['annuler'])){
          $requete = "UPDATE inscription SET ";
          
          //  Si la variable temporaire session existe, concatène dans la
          //  requête la nouvelle valeur, sinon concatène avec l'ancienne
          //  valeur
          if (isset($_SESSION['tmp_nom'])){$requete .= "nom='{$_SESSION['tmp_nom']}'";
          }else{$requete .= "nom='{$_POST['nom']}'";}
          if (isset($_SESSION['tmp_prenom'])){$requete .= ", prenom='{$_SESSION['tmp_prenom']}'";
          }else{$requete .= ", prenom='{$_POST['prenom']}'";}
          if (isset($_SESSION['tmp_id'])){$requete .= ", id='{$_SESSION['tmp_id']}'";
          }else{$requete .= ", id='{$_POST['identifiant']}'";}
          if (isset($_SESSION['tmp_mdp'])){$requete .= ", mdp='{$_SESSION['tmp_mdp']}'";
          }else{$requete .= ", mdp='{$_POST['mdp']}'";}if (isset($_SESSION['tmp_tel'])){$requete .= ", tel='{$_SESSION['tmp_tel']}'";
          }else{$requete .= ", tel='{$_POST['tel']}'";}
          if (isset($_SESSION['tmp_adresse'])){$requete .= ", adresse='{$_SESSION['tmp_adresse']}'";
          }else{$requete .= ", adresse='{$_POST['adresse']}'";}
          if (isset($_SESSION['tmp_ville'])){$requete .= ", ville='{$_SESSION['tmp_ville']}'";
          }else{$requete .= ", ville='{$_POST['ville']}'";}
          if (isset($_SESSION['tmp_email'])){$requete .= ", email='{$_SESSION['tmp_email']}'";
          }else{$requete .= ", email='{$_POST['email']}'";}
          
          
          //  Permet de modifier les valeurs a la personne connecté
          $requete .= " WHERE id='{$_SESSION['session_id']}'";
          $res = mysqli_query($connexion,$requete);
        }
        
        //  Supprime les valeurs stockées temporairements dans la session
        unset($_SESSION['confirmation']);
        unset($_SESSION['tmp_nom']);
        unset($_SESSION['tmp_prenom']);
        unset($_SESSION['tmp_id']);
        unset($_SESSION['tmp_mdp']);
        unset($_SESSION['tmp_conf_mdp']);
        unset($_SESSION['tmp_tel']);
        unset($_SESSION['tmp_adresse']);
        unset($_SESSION['tmp_ville']);
        unset($_SESSION['tmp_email']);
        
      }
      
      //  Si envoie d'une modification du formulaire sans aucune
      //  modification des valeurs, affiche une erreur
      if (!($modification) && isset($_POST['verification_ok'])){
        unset($_SESSION['confirmation']);
        echo "<div class='erreur'>Aucune information modifiée</div>";
      }
      
      //  Si personne connecté, récupère ses informations dans la
      //  variable '$perso'
      if (isset($_SESSION['session_id'])){
        $requete = "SELECT * FROM inscription WHERE id='".$_SESSION['session_id']."'";
        $res = mysqli_query($connexion,$requete);
        $perso = mysqli_fetch_array($res);
      }
    ?>
    
    <?php
    
      // Si personne n'est connecté
      if (!isset($_SESSION['session_id'])){
        echo "<h1>Erreur - Personne de connecté</h1>";
        
      //  Sinon affiche un formulaire pour les modifications
      } else {
    ?>
    <div class="formulaire">
      
      <!--
      Affichage dans les inputs :
        Si aucune valeur stockées correspondante dans la session, affiche la
          la valeur dans la bdd
        Sinon affiche la valeur choisi pour la modification afin de confirmer
          et affiche un message pour voir les lignes modifiées
      -->
      
      <h1>Modification du profil : <?php echo $_SESSION['session_id'];?></h1>
      <form action="./modification_information.php" method="post">
        <p>(*) Champs obligatoire</p>
        <p>Nom : 
          <?php
          echo '<input type="text" name="nom" autofocus required ';
          if ($modification){
            echo "readonly ";}          
          if (isset($_SESSION['tmp_nom'])){
            echo 'value="'.$_SESSION['tmp_nom'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['nom'].'">';
          }?>
          (*)
        </p>
        <p>Prénom :
          <?php 
          echo '<input type="text" name="prenom" required ';
          if ($modification){
            echo "readonly ";}          
          if (isset($_SESSION['tmp_prenom'])){
            echo 'value="'.$_SESSION['tmp_prenom'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['prenom'].'">';
          }?> 
          (*)
        </p>
        <p>Identifiant : 
          <?php 
          echo '<input type="text" name="identifiant" ';
          echo 'readonly value="'.$perso['id'].'">';
          echo "<span style='color:red;'>Changement de l'identifiant inpossible</span>";
          ?>
        </p>
        <p>Mot de passe : 
          <?php 
          echo '<input type="password" name="mdp" required ';
          if ($modification){
            echo "readonly ";}          
          if(isset($_SESSION['tmp_mdp'])){
            echo 'value="'.$_SESSION['tmp_mdp'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['mdp'].'">';
          }?>
          (*)
        </p>
        <p>Confirmation du mot de passe : 
          <?php 
          echo '<input type="password" name="conf_mdp" required ';
          if ($modification){
            echo "readonly ";}          
          if(isset($_SESSION['tmp_conf_mdp'])){
            echo 'value="'.$_SESSION['tmp_conf_mdp'].'">';}else{
            echo 'value="'.$perso['mdp'].'">';
          }?>
          (*)
        </p>
        <p>N° de Téléphone :
          <?php 
          echo '<input type="text" name="tel" required ';
          if ($modification){
            echo "readonly ";}          
          if(isset($_SESSION['tmp_tel'])){
            echo 'value="'.$_SESSION['tmp_tel'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['tel'].'">';
          }?>
          (*)
        </p>
        <p>Adresse :
          <?php 
          echo '<input type="text" name="adresse" ';
          if ($modification){
          echo "readonly ";}          
          if(isset($_SESSION['tmp_adresse'])){
            echo 'value="'.$_SESSION['tmp_adresse'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['adresse'].'">';
          }?>
        </p>
        <p>Ville :
          <?php 
          echo '<input type="text" name="ville" ';
          if ($modification){
          echo "readonly ";}          
          if(isset($_SESSION['tmp_ville'])){
            echo 'value="'.$_SESSION['tmp_ville'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['ville'].'">';
          }?>
        </p>
        <p>Email :
          <?php 
          echo '<input type="text" name="email" required ';
          if ($modification){
          echo "readonly ";}          
          if(isset($_SESSION['tmp_email'])){
            echo 'value="'.$_SESSION['tmp_email'].'">';AfficherChangement();}else{
            echo 'value="'.$perso['email'].'">';
          }?>
          (*)
        </p>
          <?php
          //  Si aucun envoie pour la 1ere fois du formulaire, afficher bouton
          //  d'envoie du formulaire correspondant a la 1ere modification
          if (!isset($_SESSION['confirmation'])){
            echo '<p><input name="verification_ok" type="submit" value="OK"></p>';
            
          //  Sinon affiche un bouton pour confirmer les modifications 
          //  effectuées
          } else {
            echo '<p><input style="float:left;" name="verification_confirmation" type="submit" value="Confirmer les changements"></p>';
            echo '<p><input style="float:right;" name="annuler" type="submit" value="Annuler les changements"></p>';
          }
          ?>
      </form>
    </div>
    <?php
      }
    ?>
    
    <a href="./page_utilisateur.php" class="page_accueil">Page d'information du profil - Annuler les modifications</a>
    
    <div class="footer">
      <a href="./page/contact.php">Nous contacter</a>
    </div>
    
    <?php
      mysqli_close($connexion);
    ?>
  </body>
  
</html>
