<?php session_start();
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title>Modification de l'annonce</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/css_modification_annonce.css"/>
    <?php
      
      //  acces_level_autorise : renvoie true si '$a' est stricement
      //  supérieure à '$b', sinon renvoie faux
      function access_level_autorise($a,$b){
        return ( ($a >= $b) - ($b >= $a) > 0 ? true : false);
      }
    
      //  Renvoie le niveau d'accès du grade '$session_grade'
      function set_access_level($session_grade){
        $array_moderateur = array("Comrade", "P'tite larve");
        
        if ($session_grade == "admin"){
          return 3;
        } else if (in_array(''.$session_grade.'',$array_moderateur)){
          return 2;
        } else if ($session_grade == "conseiller"){
          return 1;
        } else {
          return 0;
        }
      }
      
    ?>
  </head>
  
  <body>
    
    <div class="head">
       <i><h1>LaBonneCoin</h1></i>
    </div>
    
    <?php
      
      $array_moderateur = array("Comrade", "P'tite larve");
      
      //  Si arrivé d'une page anormal, initialisation de la variable '$erreur'
      if (empty($_SESSION['access'])){
        $erreur = "Vous ne parvenez pas de la bonne page<br>Veuillez accéder a la page des utilisateurs";
      }
      
      //  Si manque d'information dans l'url, initialisation de la
      //  variable '$erreur'
      if ((empty($_GET['change']) || empty($_GET['num'])) && empty($_POST['titre'])){
        $erreur = "Manque d'information pour modifier une annonce";
      }
      
      //  Test si bonne valeur de la variable $_GET['param']
      if (isset($_GET['change']) && $_GET['change'] != "modifier" && $_GET['change'] != "supprimer"){
        $erreur = "Mauvaise information pour le changement";
      }
      
      //  Si aucune erreur, on effectue le code suivant
      if (empty($erreur)){
        //  Connexion au serveur local et codage en utf8 par sécurité
        $connexion = mysqli_connect("localhost","root","","localhost");
        $res = mysqli_query($connexion,"SET NAMES UTF8");
        $res = mysqli_query($connexion,"SET CHARACTER UTF8");
        
        //  Récupère l'auteur de l'annonce
        if (isset($_GET['num'])){
          $requete = "SELECT id FROM annonce WHERE num='{$_GET['num']}'";
        } else {
          $requete = "SELECT id FROM annonce WHERE num='0'";
        }
        $res = mysqli_query($connexion,$requete);
        $annonce = mysqli_fetch_array($res);
        
        if ($annonce == null && empty($_POST['titre'])){
          
          $erreur = "Annonce inexistante";
          
        //  Si l'identifiant est différent de null ou si le grade de la
        //  personne connecté est suffisament élevé
        } else if ($annonce['id'] != "null" || 
         (isset($_SESSION['grade']) && ($_SESSION['grade'] == "admin" || in_array(''.$_SESSION['grade'].'',$array_moderateur)) ) ){
          
          //  Récupère le grade de l'auteur de l'annonce
          $requete = "SELECT grade FROM inscription WHERE id='{$annonce['id']}'";
          $res = mysqli_query($connexion,$requete);
          $perso_annonce = mysqli_fetch_array($res);
          
          //  Initialise la valeur d'acces requise pour l'annonce
          $access_required = set_access_level($perso_annonce['grade']);
          
          //  Test si le niveau d'acces est assez élevé
          if (!(access_level_autorise($_SESSION['access'],$access_required))){
            if ($_SESSION['access'] != $access_required){
              $erreur = "Acces non autorisé à cette annonce";
            }
          }
        } else {
          $erreur = "Aucune personne ne peut modifier cette annonce a part les 
          admins et les modérateurs";
        }
      }
      
      //  Si aucune erreur lors l'exécution du code précédent, on initialise
      //  la variable '$continuer' a true pour confirmer les changements, sinon
      //  on affiche un message d'erreur
      if (empty($erreur)){
        $continuer = true;
      } else {
        $continuer = false;
        echo "<div class='erreur'>$erreur</div>";
        echo "<br><a href='./page_utilisateur.php'>Retourner a la page des utilisateurs</a>";
      }
      
      if (isset($_POST['titre'])){
        $continuer = false;
        echo "<div class='change'>
                Changement de l'annonce correctement effectué
              </div>";
        echo "<br><a href='./page_utilisateur.php'>Retourner a la page des utilisateurs</a>";
        
        mysqli_close($connexion);
        $connexion = mysqli_connect("localhost","root","","localhost");
        
        //  Remplacement des caractères ' et " par un caractère affichable
        //  et création de variable plus simple d'utilisation
        $titre_annonce = str_replace("'","\'",$_POST['titre']);
        $titre_annonce = str_replace('"','\"',$titre_annonce);
        $departement = $_POST['localisation'];
        $categorie = $_POST['categorie'];
        $tel = $_POST['tel'];
        $prix = $_POST['prix'];
        $description = str_replace("'","\'",$_POST['description']);
        $description = str_replace('"','\"',$description);
        
        //  Récupération du nom du fichier envoyé
        $url_file = basename($_FILES['icone']['name']);
        
        //  Récupération de l'emplacement du fichier temporaire contenant
        //  l'image envoyé par le formulaire
        $tmp_name = $_FILES['icone']['tmp_name'];
        
        //  Affecte l'emplacement choisi ou sera enregistré l'image a la
        //  variable '$fichier_img'
        $fichier_img = "C:\\xampp\\htdocs\\projet\\image\\".$url_file;
        
        if (isset($_FILES['icone']) && $_FILES['icone']['error'] > 0 && file_exists($_FILES['icone']['name'])){
          $erreur = "Erreur lors du transfert de l'image ou taille de l'image trop grande";
        }
        
        //  Vérifie si l'extension de l'image est correct, sinon affecte à la
        //  variable '$erreur' une raison de l'erreur
        if (isset($titre_annonce) && file_exists($_FILES['icone']['name'])){
          
          //  Déclaration des extensions valables
          $extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
          
          //  Récupération de l'extension de l'image
          $extension_upload = strtolower(  substr(  strrchr($_FILES['icone']['name'], '.')  ,1)  );
          if ( !(in_array($extension_upload,$extensions_valides)) && isset($_FILES['icone'])){
            $erreur = "Extension incorrecte";
          }
        }
        
        //  Récupère le numéro maximum
        $num_annonce = mysqli_fetch_array(mysqli_query($connexion,"SELECT MAX(num) FROM annonce"));
        $num_annonce[0] += 1;
        
        //  Si aucune erreur existante, et si existance de la variable
        //  $titre_annonce, exécution du code suivant, sinon affichage
        //  d'un message d'erreur en fonction de la valeur de '$erreur'
        if (!empty($titre_annonce) && !isset($erreur)){
          if (empty($url_file)){
            $requete = "UPDATE annonce
                      SET num='$num_annonce[0]',
                          localisation='$departement',telephone='$tel',
                          titre='$titre_annonce',
                          description='$description',
                          categorie='$categorie',prix='$prix' 
                      WHERE num={$_POST['num']}";
          } else {
            $requete = "UPDATE annonce
                      SET num='$num_annonce[0]',
                          localisation='$departement',telephone='$tel',
                          titre='$titre_annonce',image='$url_file',
                          description='$description',
                          categorie='$categorie',prix='$prix' 
                      WHERE num={$_POST['num']}";
          }
          
          $res = mysqli_query($connexion,$requete);
        } else if (isset($erreur)){
          echo "<div class='erreur'>{$erreur}</div>";
        }
      }
      
      if ($continuer && !(isset($_POST['conf_modif']) || isset($_POST['conf_supp'])) ){
        
        //  Connexion au serveur local et codage en utf8 par sécurité
        $connexion = mysqli_connect("localhost","root","","localhost");
        
        //  Récupération des informations de l'annonce
        $requete = "SELECT * FROM annonce WHERE num='{$_GET['num']}'";
        $res = mysqli_query($connexion,$requete);
        $para_annonce = mysqli_fetch_array($res);
        
        //  Formulaire de confimation du changement de l'annonce
        if ($_GET['change'] == "modifier"){
          ?>
            <div class="formulaire">
            
            <!--
              Formulaire permettant de modifier une annonce
            -->
            <form action="modification_annonce.php" method="post" enctype="multipart/form-data">
              <p class="annonce">
                <h3>Information</h3>
                <?php
                  echo '<input type="hidden" name="num" value="'.$para_annonce['num'].'">';
                ?>
                <label for="titre">Titre de l'annonce (max. 50 caractères) :</label>
                <?php
                  echo '<input class="text" type="text" name="titre" id="titre"';
                  if (isset($para_annonce['titre'])){
                    echo 'value="'.$para_annonce['titre'].'" required >'; 
                  } else { 
                    echo ' required><br />';
                  }
                 ?>
              </p>
              <p>
                <label for="icone">Icône du fichier (JPG, PNG ou GIF | max. 1 Mo) (pas d'accent dans le nom du fichier) :</label><br />
                <input type="file" name="icone" id="icone"/><br />
              </p>
              <p>
                <label for="localisation">Departement :</label>
                
                <select name="localisation" id="localisation" required >
                  <?php
                  
                  //  Récupération des informations de l'annonce en utf8
                  $res = mysqli_query($connexion,"SET NAMES UTF8");
                  $res = mysqli_query($connexion,"SET CHARACTER UTF8");
                  $requete = "SELECT * FROM annonce WHERE num='{$_GET['num']}'";
                  $res = mysqli_query($connexion,$requete);
                  $para_annonce = mysqli_fetch_array($res);
                  
                  //  Récupération des départements
                  $requete = "SELECT * FROM departement";
                  $res = mysqli_query($connexion,$requete);
                  
                  echo "<option value='' >- Sélectionner un département -</option>";
                  while($fetch = mysqli_fetch_array($res)){
                    $fetch_id = $fetch['id'];
                    $fetch_dep = $fetch['departement'];
                    
                    //  Affichage des départements dans un 'select'
                    if ($para_annonce['localisation'] == $fetch_id){
                      echo '<option value="' .$fetch_id. '" selected>' .$fetch_id. ' - ' .$fetch_dep. '</option>';
                    } else {
                      echo '<option value="' .$fetch_id. '">' .$fetch_id. ' - ' .$fetch_dep. '</option>';
                    }
                    
                  }
                  mysqli_close($connexion);
                  $connexion = mysqli_connect("localhost","root","","localhost");
                  $requete = "SELECT * FROM annonce WHERE num='{$_GET['num']}'";
                  $res = mysqli_query($connexion,$requete);
                  $para_annonce = mysqli_fetch_array($res);
                  ?>
                </select>
              </p>
              <p>
                <label for="categorie">Catégorie :</label>
                <select name="categorie" id="categorie" required>
                  <?php
                  
                  //  Récupération des catégories
                  $requete = "SELECT * FROM categorie";
                  $res = mysqli_query($connexion,$requete);
                  echo "<option value='' selected>- Sélectionner une catégorie -</option>";
                  while($fetch = mysqli_fetch_array($res)){
                    $fetch_id = $fetch['id'];
                    $fetch_nom = $fetch['nom'];
                    
                    //  Affichage des catégories dans un 'select'
                    if ($para_annonce['categorie'] == $fetch_id){
                      echo '<option value="' .$fetch_id. '" selected>' .$fetch_nom. '</option>';
                    } else {
                      echo '<option value="' .$fetch_id. '">' .$fetch_nom. '</option>';
                    }
                    
                  }
                  
                  ?>
                </select>
              </p>
              <p>
                <label for="tel">Numéro de téléphone : </label>
                <?php
                  
                  //  Si une personnes est connecté, on effectue le code suivant
                  //  Sinon affichage d'un simple 'input' 
                  if (isset($para_annonce['telephone'])){
                    
                    //  Affichage d'un input avec le numéro de téléphone de la
                    //  personne connecté de base
                    echo '<input type="text" name="tel" id="tel" value="' .$para_annonce['telephone']. '" required/>';
                  } else {
                    echo '<input type="text" name="tel" id="tel" required/>';
                  }
                ?>
                <p>
                  <label for="prix">Prix (entier): </label>
                  <span style="border:1px #b3b3b3 solid;background-color:white;padding:3px;">
                    <?php
                      if (isset($para_annonce['prix'])){
                        echo '<input class="input_off" type="tel" name="prix" id="prix" value="'.$para_annonce['prix'].'" maxlength="6" required>';
                      } else {
                        echo '<input class="input_off" type="tel" name="prix" id="prix" maxlength="6" required>';
                      }
                    ?>
                    €
                  </span>
                  
                </p>
              </p>
              <p>
                <label for="description">Description de votre fichier (max. 255 caractères) :</label><br />
                <?php
                  
                  if (isset($para_annonce['description'])){
                    echo '<textarea class="description" name="description" id="description">'.$para_annonce['description'].'</textarea><br />';
                  } else {
                    echo '<textarea class="description" name="description" id="description" ></textarea><br />';
                  }
                  mysqli_close($connexion);
                ?>
                
              </p>
              <p class="input">
                <input type="submit" name="submit" value="Envoyer" />
              </p>
            </form>
            
            </div>
          <?php
        } else if ($_GET['change'] == "supprimer"){
          echo "<div class='change'>
                  <form action='' method='post'>
                    Voulez-vous vraiment supprimer l'annonce?
                    <span style='margin-left:10px;'>
                      <input type='submit' name='conf_supp' value='Valider ?'>
                    </span>
                  </form>
                </div>";
        }
      }
      
      if ((isset($_POST['conf_modif']) || isset($_POST['conf_supp'])) && $continuer){
        
        //  Si confirmation de la suppression de l'annonce
        if (isset($_POST['conf_supp'])){
          $requete = "DELETE FROM annonce WHERE num='{$_GET['num']}'";
          $res = mysqli_query($connexion,$requete);
          
          echo "<div class='conf_change'>
                  Suppression de l'annonce 
                </div>";
        } else if (isset($_POST['conf_modif'])){
          
        }
      }
    ?>
    
    
    <a href="./page_utilisateur.php" class="page_accueil">
      Retour page utilisateur
    </a>
    
    <div class="footer">
      <a href="./contact.php">Nous contacter</a>
    </div>
    
  </body>
  
</html>
