<?php session_start();
unset($_SESSION['access']);
?>

<!DOCTYPE html>
<html>
  
  <head>
    <title>Formulaire</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/css_page_annonce.css"/>
  </head>
  
  <body>
    
    <div class="head">
       <i><h1>LaBonneCoin</h1></i>
    </div>
    
    <div  class="icone_inscription">
      <a href="./inscription.php">Inscription</a>
    </div>
    
    <div id="icone_connexion">
      <?php
      
      //  Affecte true a '$errno' pour les cas ou aucune donnée n'a été envoyé  
      $errno = true;
      
      //  Affecte false a $errno si $_POST['identifiant'] et $_POST['mdp'] si
      //  les 2 existents ou aucun n'exite
      if ( !((!empty($_POST['identifiant']) && empty($_POST['mdp']))
          || (empty($_POST['identifiant']) && isset($_POST['mdp'])))){
        $errno = false;
      }
      
      //  Si les 2 variables existent, effectue le code suivant
      if (!empty($_POST['identifiant']) && !empty($_POST['mdp'])){
        $identifiant = $_POST['identifiant'];
        $mdp = $_POST['mdp'];
        $errno = true;
        
        //  Connexion au serveur local, et codage en UTF8 par sécurité
        $connexion = mysqli_connect("localhost","root","","localhost");
        $res = mysqli_query($connexion,"SET NAMES UTF8");
        $res = mysqli_query($connexion,"SET CHARACTER UTF8");
        
        //  Vérifie si existe une inscription correspondant aux données envoyé
        //  par l'utilisateur lors de sa connection. Unique correspondance
        //  grâce a la PRIMARY KEY sur l'identifiant
        $requete = "SELECT * FROM inscription
                     WHERE id='$identifiant' AND mdp='$mdp'";
        
        //  Si correspondance, création des variable $_SESSION['session_id']
        //  ainsi $_SESSION['grade'] de la personne connecté et affecte false
        //  a '$errno'
        $res = mysqli_query($connexion,$requete);
        while ($fetch = mysqli_fetch_array($res)){
          $_SESSION['session_id']=$fetch['id'];
          $_SESSION['grade']=$fetch['grade'];
          $errno = false;
        }
        
        //  Fermeture de la connexion au serveur local
        mysqli_close($connexion);
        
        //  Si pression du bouton deconnexion, suppression des variables
        //  session 'session_id' et 'grade'
      } else if (isset($_POST['deconnexion'])){
        unset($_SESSION['session_id']);
        unset($_SESSION['grade']);
      }
      
      //  Si '$errno' vaut toujours true, affiche une erreur
      if ($errno){
          echo '<div class="deconnexion">
                  Erreur d\'identifiant<br/>ou de mot de passe
               </div>';
      }
      
      //  Si connexion d'une personne, affiche un div possédant son nom, ainsi
      //  que son grade, un accès a ses informations personnelles et un bouton
      //  de déconnexion, sinon affiche un div permettant à l'utilisateur de
      //  ce connecter
      if (isset($_SESSION['session_id'])){
        echo '<div class="connexion">
                <form action="./page_utilisateur.php" method="post">
                  <p class="icone_connexion">
                    <span style="float:left;">
                      Utilisateur : <strong>' .$_SESSION['session_id']. '</strong>
                    </span>
                    <span style="float:right;">
                      Grade : <strong>' .$_SESSION['grade']. '</strong><br/>
                    </span>
                    <input type="submit" name="profil" value="Accéder au profil">
                  </p>
                </form>
                <form action="./annonce.php" method="post">
                  <p class="validation_connexion">
                    <input type="submit" name="deconnexion" value="Se déconnecter">
                  </p>
                </form>
              </div>';
      } else {
        echo '<div class="connexion">
                <form action="../page/annonce.php" method="post">
                  
                  <p class="icone_connexion">
                    <label>Identifiant : <input type="text" name="identifiant"></label></br>
                    <label>Mot de passe : <input type="password" name="mdp"></label>
                  </p>
                  
                  <p class="validation_connexion">
                    <input type="submit" value="VALIDER">
                  </p>
                  
                </form>
              </div>';
        
      }
      
      ?>
    </div>
    
    <div class="formulaire">
      
      <!--
        Formulaire permettant de créer une annonce
      -->
      <form action="annonce.php" method="post" enctype="multipart/form-data">
        <p class="annonce">
          <h3>Information</h3>
          <label for="titre">Titre de l'annonce (max. 50 caractères) :</label>
          <input class="text" type="text" name="titre" id="titre" required /><br />
        </p>
        <p>
          <label for="icone">Icône du fichier (JPG, PNG ou GIF | max. 1 Mo) (pas d'accent dans le nom du fichier) :</label><br />
          <input type="file" name="icone" id="icone"/><br />
        </p>
        <p>
          <label for="localisation">Departement :</label>
          <?php
            //  Connexion au serveur local et codage en utf8 par sécurité
            $connexion = mysqli_connect("localhost","root","","localhost");
            $res = mysqli_query($connexion,"SET NAMES UTF8");
            $res = mysqli_query($connexion,"SET CHARACTER UTF8");
          ?>
          
          <select name="localisation" id="localisation" required >
            <?php
            
            //  Récupération des départements
            $requete = "SELECT * FROM departement";
            $res = mysqli_query($connexion,$requete);
            
            echo "<option value='' style='background-color:#D6D6D6;' selected>- Sélectionner un département -</option>";
            while($fetch = mysqli_fetch_array($res)){
              $fetch_id = $fetch['id'];
              $fetch_dep = $fetch['departement'];
              
              //  Affichage des départements dans un 'select'
              echo '<option value="' .$fetch_id. '">' .$fetch_id. ' - ' .$fetch_dep. '</option>';
            }
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
            echo "<option value='' style='background-color:#C6C6C6;' selected>- Sélectionner une catégorie -</option>";
            while($fetch = mysqli_fetch_array($res)){
              $fetch_id = $fetch['id'];
              $fetch_nom = $fetch['nom'];
              
              //  Affichage des catégories dans un 'select'
              echo '<option value="' .$fetch_id. '">' .$fetch_nom. '</option>';
              echo $fetch_id;
            }
            
            //  Fermeture de la connexion au serveur local
            mysqli_close($connexion);
            ?>
          </select>
        </p>
        <p>
          <label for="tel">Numéro de téléphone : </label>
          <?php
            
            //  Si une personnes est connecté, on effectue le code suivant
            //  Sinon affichage d'un simple 'input' 
            if (isset($_SESSION['session_id'])){
              $id = $_SESSION['session_id'];
              
              //  Connexion au serveur local et codage en utf8 par sécurité
              $connexion = mysqli_connect("localhost","root","","localhost");
              $res = mysqli_query($connexion,"SET NAMES UTF8");
              $res = mysqli_query($connexion,"SET CHARACTER UTF8");
              
              //  Récupération du numéro de téléphone de la personne connecté
              $requete = "SELECT tel FROM inscription 
                      WHERE id='$id'";
              $res = mysqli_query($connexion,$requete);
              $fetch = mysqli_fetch_array($res);
              
              //  Affichage d'un input avec le numéro de téléphone de la
              //  personne connecté de base
              echo '<input type="text" name="tel" id="tel" value="' .$fetch["tel"]. '" required/>';
              
              //  Fermeture de la connexion au serveur local
              mysqli_close($connexion);
            } else {
              echo '<input type="text" name="tel" id="tel" required/>';
            }
            
            
          ?>
          <p>
            <label for="prix">Prix (entier): </label>
            <span style="border:1px #b3b3b3 solid;background-color:white;padding:3px;">
              <input class="input_off" type="tel" name="prix" id="prix" maxlength="6" required>
              €
            </span>
            
          </p>
        </p>
        <p>
          <label for="description">Description de votre fichier (max. 255 caractères) :</label><br />
          <textarea class="description" name="description" id="description" ></textarea><br />
        </p>
        <p class="input">
          <input type="submit" name="submit" value="Envoyer" />
        </p>
      </form>
    </div>
    
    <div id="page_acceuil">
      <p class="page_accueil">
        <a href="../page_principale.php">Page d'acceuil</a>
      </p>
    </div>
    
    <div id="inscription_bdd_annonce">
      <?php
        
        //  Connexion au seveur local
        $connexion = mysqli_connect("localhost","root","","localhost");
        
        //  Si connection d'une personne, création de la variable '$id' pour
        //  affecter le nom de la personne connecté à l'annonce dans la bdd
        if (isset($_SESSION['session_id'])){
          $id = $_SESSION['session_id'];
        }
        
        //  Si envoie du formulaire, exécution du code suivant
        if (isset($_POST['titre'])){
          
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
        }
        
        //  Si erreur lors du transfert de l'image, affectation à la variable
        //  '$erreur' une raison de l'erreur
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
        
        //  Si aucune erreur existante, et si existance de la variable
        //  $titre_annonce, exécution du code suivant, sinon affichage
        //  d'un message d'erreur en fonction de la valeur de '$erreur'
        if (!empty($titre_annonce) && !isset($erreur)){
          
          
          $num_annonce = mysqli_fetch_array(mysqli_query($connexion,"SELECT MAX(num) FROM annonce"));
          $num_annonce[0] += 1;
          
          //  Si une personne est connecté, affecte par son identifiant la
          //  valeur de 'id', sinon lui affecte 'null'
          if (isset($id)){
            $requete = "
              INSERT INTO 
                annonce(`num`,`id`,`localisation`,`telephone`,`titre`,`image`,
                `description`,`categorie`,`prix`)
              VALUES ('".$num_annonce[0]."','$id','$departement','$tel','$titre_annonce','$url_file',
                  '$description','$categorie','$prix')";
          } else {
            $requete = "
              INSERT INTO 
                annonce(`num`,`id`,`localisation`,`telephone`,`titre`,`image`,
                `description`,`categorie`,`prix`)
              VALUES ('".$num_annonce[0]."','null','$departement','$tel','$titre_annonce','$url_file',
                  '$description','$categorie','$prix')";
          }
          echo $requete;
          //  Déplace le fichier temporaire dans le fichier souhaité pour
          //  l'enregistrement de l'image
          move_uploaded_file($tmp_name,$fichier_img);
          mysqli_query($connexion,$requete);
          echo '<div class="erreur">Annonce enregistré</div>';
        } else if (isset($erreur)){
          echo "<div class='erreur'>{$erreur}</div>";
        }
        
        //  Fermeture de la connexion au serveur local
        mysqli_close($connexion);
      ?>
    </div>
    
    <div class="footer">
      <a href="./contact.php">Nous contacter</a>
    </div>
    
  </body>
  
</html>
