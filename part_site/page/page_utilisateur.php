<?php session_start();

?>

<!DOCTYPE html>
<html>
  
  <head>
    <title>Information Personnelle</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/css_page_utilisateur.css"/>
    <?php
    
      //  issame : renvoie true si '$a' est égal a '$b', sinon renvoie faux
      function issame($a, $b){
        return ($a == $b ? true : false);
      }
      
      //  acces_level_autorise : renvoie true si '$a' est stricement
      //  supérieure à '$b', sinon renvoie faux
      function access_level_autorise($a,$b){
        return ( ($a >= $b) - ($b >= $a) > 0 ? true : false);
      }
      
      //  Renvoie le niveau d'accès du grade '$session_grade'
      function set_access_level($session_grade,$array_moderateur){
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
      
      //  autorise_access : renvoie true si le niveau d'acces de la personne
      //  ayant pour identifiant '$session_id' est suffisament élevé, ou si il
      //  s'agit de lui-même, pour autoriser à accèder aux informations
      //  personnelles de la personne désigné par '$perso_id'. Si une erreur
      //  survient lors du processus, ou il n'existe pas de personne ayant pour
      //  identifiant '$perso_id', initialise '$erreur' a true;
      function autorise_access($perso_id,$perso_grade,$session_id,$session_grade,$array_moderateur,&$erreur){
        
        //  Affectation du niveau d'accès de '$session_id'
        $access_level = set_access_level($session_grade,$array_moderateur);
        
        //  Vérification du droit d'acces
        if ($perso_grade == "admin"){
          
          //  Accès seulement a l'admin en question
          $access = issame($perso_id,$session_id);
        } else if (in_array(''.$perso_grade.'',$array_moderateur)){
          
          //  Accès si level_access suffisament élevé ou au moderateur lui-même
          $access = access_level_autorise($access_level,2);
          if (!($access)){
            $access = issame($perso_id,$session_id);
          }
        } else if ($perso_grade == "conseiller"){
          
          //  Accès si level_access suffisament élevé ou au conseiller lui-même
          $access = access_level_autorise($access_level,1);
          if (!($access)){
            $access = issame($perso_id,$session_id);
          }
        } else if ($perso_grade == "membre"){
          
          //  Accès si level_access suffisament élevé ou au membre lui-même
          $access = access_level_autorise($access_level,0);
          if (!($access)){
            $access = issame($perso_id,$session_id);
          }
        } else {
          
          //  Sinon, personne dans la bdd n'a cette identifiant
          $erreur = "Aucune information sur {$_GET['param']}";
          $access = false;
        }
        if ($access){
          return true;
        }
      }
      
    ?>
  </head>

  <body>
    
    <div class="head">
      <i><h1>LaBonneCoin</h1></i>
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
                <form action="./modification_information.php" method="post">
                  <p class="icone_connexion">
                    <span style="float:left;">
                      Utilisateur : <strong>' .$_SESSION['session_id']. '</strong>
                    </span>
                    <span style="float:right;">
                      Grade : <strong>' .$_SESSION['grade']. '</strong><br/>
                    </span>
                    <input type="submit" value="Modifier les informations">
                  </p>
                </form>
                <form action="./page_utilisateur.php" method="post">
                  <p class="validation_connexion">
                    <input type="submit" name="deconnexion" value="Se déconnecter">
                  </p>
                </form>
              </div>';
      } else {
        echo '<div class="connexion">
                <form action="./page_utilisateur.php" method="post">
                  
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
    
    <div id="affichage_liste_inscrits">
      <?php
      
        //  Initialisation des grade correspondant a moderateur : 
        //  '$liste..' pour les requêtes 'IN' et '$array..' pour les 'in_array'
        $liste_moderateur = "('Comrade', 'P\'tite larve')";
        $array_moderateur = array("Comrade", "P'tite larve");
        
        
        //  Connexion au serveur local et codage en utf8 par sécurité
        $connexion = mysqli_connect("localhost","root","","localhost");
        $res = mysqli_query($connexion,"SET NAMES UTF8");
        $res = mysqli_query($connexion,"SET CHARACTER UTF8");
        
        //  Récupération des informations de la personne connecté, on de t
        if (isset($_SESSION['session_id'])){
          $requete = "SELECT * FROM inscription WHERE id='" .$_SESSION['session_id']. "'";
          $res = mysqli_query($connexion,$requete);
          $info = mysqli_fetch_array($res);
        }
        
        //  Affichage de la liste des personnes inscrites sur le site avec
        //  accès à leur information selon son grade
        //
        //  Un admin a accès à tout les monde sauf les autres admins
        //  Un modérateur a accès aux conseillers et aux membres
        //  Un conseiller a accès à tout les membres
        //  Les membres n'ont accès qu'a leur compte
        echo "<div class='affichage_inscrit'>";
          echo "<div class='head_div'>Listes des inscrits</div>";
          echo "<ul>";
          
          //  Récupération des inscrits avec le grade 'admin'
          $requete = "SELECT * FROM inscription WHERE grade='admin'";
          $res = mysqli_query($connexion,$requete);
          echo "<strong> Admin - </strong><ul>";
          while ($li_pers = mysqli_fetch_array($res)){
            echo "<li> identifiant : ";
            
            //  Si la personne a le droit d'accès a la personne correspondante,
            //  lui permet d'accéder à son profil
            if ((isset($_SESSION['session_id'])) && ($li_pers['id'] == $_SESSION['session_id'])){
              echo "<a href='?param=".$li_pers['id']."'>".$li_pers['id']. "</a></li>";
            } else {
              echo $li_pers['id']. "</li>";
            }
          }
          echo "</ul>";
          
          //  Récupération des inscrits avec le grade 'moderateur'
          $requete = "SELECT * FROM inscription WHERE grade IN ".$liste_moderateur."";
          $res = mysqli_query($connexion,$requete);
          echo "<strong> Moderateur - </strong><ul>";
          while ($li_pers = mysqli_fetch_array($res)){
            echo "<li> identifiant :";
            
            //  Si la personne a le droit d'accès a la personne correspondante,
            //  lui permet d'accéder à son profil
            if ((isset($_SESSION['session_id'])) && ($info['grade'] == "admin" || $li_pers['id'] == $_SESSION['session_id'])){
              echo "<a href='?param=".$li_pers['id']."'>".$li_pers['id']. "</a></li>";
            } else {
              echo $li_pers['id']. "</li>";
            }
          }
          echo "</ul>";
          
          //  Récupération des inscrits avec le grade 'conseiller'
          $requete = "SELECT * FROM inscription WHERE grade='conseiller'";
          $res = mysqli_query($connexion,$requete);
          echo "<strong> Conseiller - </strong><ul>";
          while ($li_pers = mysqli_fetch_array($res)){
            echo "<li> identifiant :";
            
            //  Si la personne a le droit d'accès a la personne correspondante,
            //  lui permet d'accéder à son profil
            if ((isset($_SESSION['session_id'])) && 
                ($info['grade'] == "admin" || in_array(''.$info['grade'].'',$array_moderateur) || 
                 $li_pers['id'] == $_SESSION['session_id'])){
              echo "<a href='?param=".$li_pers['id']."'>".$li_pers['id']. "</a></li>";
            } else {
              echo $li_pers['id']. "</li>";
            }
          }
          echo "</ul>";
          
          //  Récupération des inscrits avec le grade 'membre'
          $requete = "SELECT * FROM inscription WHERE grade='membre'";
          $res = mysqli_query($connexion,$requete);
          echo "<strong> Membre - </strong><ul>";
          while ($li_pers = mysqli_fetch_array($res)){
            echo "<li> identifiant :";
            
            //  Si la personne a le droit d'accès a la personne correspondante,
            //  lui permet d'accéder à son profil
            if ((isset($_SESSION['session_id'])) && 
                ($info['grade'] == "admin" || in_array(''.$info['grade'].'',$array_moderateur) || 
                 $info['grade'] == "conseiller" || $li_pers['id'] == $_SESSION['session_id'])){
              echo "<a href='?param=".$li_pers['id']."'>".$li_pers['id']. "</a></li>";
            } else {
              echo $li_pers['id']. "</li>";
            }
          }
          echo "</ul>";
          
          echo "</ul>";
          echo "</div>";
        
        ?>
    </div>
    
    <div id="information_personne">
      <?php
        
        //  Test si une personne est connecté et si l'accès aux informations
        //  d'une personne a bien été confirmé
        if (isset($_SESSION['session_id']) && isset($_GET['param'])){
          
          //  Connexion au serveur local et codage en utf8 par sécurité
          $connexion = mysqli_connect("localhost","root","","localhost");
          $res = mysqli_query($connexion,"SET NAMES UTF8");
          $res = mysqli_query($connexion,"SET CHARACTER UTF8");
          
          //  Récupère le grade et l'identifian de la personne
          $requete = "SELECT * FROM inscription WHERE id='".$_GET['param']."'";
          $res = mysqli_query($connexion,$requete);
          $perso = mysqli_fetch_array($res);
          
          //  Vérification du droit d'accès aux informations
          $access = false;
          $access = autorise_access(
                    $perso['id'],$perso['grade'],
                    $_SESSION['session_id'],$_SESSION['grade'],
                    $array_moderateur,$erreur);
        }
        
        //  Initialise le niveau d'acces dans la session. Si aucune connexion,
        //  initialise la valeur a 0 (aucun accès)
        if (isset($_SESSION['grade'])){
          $_SESSION['access'] = set_access_level($_SESSION['grade'],$array_moderateur);
        } else {
          $_SESSION['access'] = 0;
        }
        
        
        //  Si une erreur a eu lieu lors de la vérification du droit d'acces
        //  ou si l'identifiant de la personne ciblé n'existe pas, affiche un
        //  message d'erreur. Sinon affecte a '$id_perso' le nom de la personne
        //  ciblé par la variable '$_GET['param']', si '$_GET['param']' n'existe
        //  pas, accède automatique a la personne connecté
        echo "<div class='erreur'>";
        
        //  Si accès interdit ou identifiant recherché non trouvable
        if (isset($_GET['param']) && !empty($_SESSION['session_id']) && !($access) ){
          if (isset($erreur)){
            echo "<span class='sup'>{$erreur}</span>";
          } else {
            echo "<span class='sup'>Vous n'avez pas accès à cette personne</span>";
          }
        
        //  Si identifiant recherché trouvé et accès autorisé 
        } else if (isset($_GET['param']) && !empty($_SESSION['session_id']) && $access ){
          $id_info = $_GET['param'];
          echo "<span class='sup'>Information sur {$_GET['param']}</span>";
          
        //  Si aucune recherche d'identifiant ou aucune connexion
        } else if (empty($_GET['param'])){
          if (!empty($_SESSION['session_id'])){
            $id_info = $_SESSION['session_id'];
            echo "<span class='sup'>Information sur {$_SESSION['session_id']}</span>";
          } else {
            echo "<span class='sup'>Aucune information disponible</span>";
          }
        
        //  Sécurité d'autre cas
        } else {
           echo "<span class='sup'>Aucune information disponible</span>";
        }
        
        //  Affichage des différents bouton de modification lorsque
        //  l'accès est autorisé
        echo "<span class='inf'>";
        if (isset($id_info)){
          if (isset($_GET['param'])){
            if (isset($_GET['page'])){
              $href = "page_utilisateur.php?param={$_GET['param']}&page={$_GET['page']}";
            } else {
              $href = "page_utilisateur.php?param={$_GET['param']}";
            }
            ;
          } else {
            if (isset($_GET['page'])){
              $href = "page_utilisateur.php?page={$_GET['page']}";
            } else {
              $href = "page_utilisateur.php";
            }
          }
          
          //  Si bouton sélectionné, affichage fond orange pour montrer
          //  quelle bouton a été sélectionné
          echo "<form action='{$href}' method='post'>";
                  if (isset($_POST['modifier'])){
                    echo "<span class='left'></span>";
                    echo  "<input class='push_modif' type='submit' name='modifier' value='Modifier'>";
                  } else {
                    echo "<input class='push_modif' type='submit' name='modifier' value='Modifier'>";
                  }
                  if (isset($_POST['supprimer'])){
                    echo "<span class='right'></span>";
                    echo  "<input class='push_del' type='submit' name='supprimer' value='Supprimer'>";
                  } else {
                    echo "<input class='push_del' type='submit' name='supprimer' value='Supprimer'>";
                  }
                  
                  
                echo "</form>";
        }
        
        echo "</span>";
        echo "</div>";
        
        //  Si acces autorisé, affichage des annonces de la personne
        //  connecté ou de la personne sélectionnée
        if (isset($id_info)){
          
          //  Connexion au serveur local et codage en utf8 par sécurité
          $connexion = mysqli_connect("localhost","root","","localhost");
          
          //  Récupère le nombre d'annonce de la personne connectée/sélectionnée
          //  et initialise '$erreur' a false
          $erreur = false;
          $requete = "SELECT COUNT(*) FROM annonce WHERE id='".$id_info."'";
          $res = mysqli_query($connexion,$requete);
          $provi = mysqli_fetch_array($res);
          
          //  Stocke dans '$nb_total_annonce' le nombre d'annonce et dans
          //  '$nb_total_page' le nombre de page nécessaire pour afficher toutes
          //  les annonces (rappel : 5 annonces par pages)
          $nb_total_annonce = $provi[0];
          $nb_total_page = ceil($nb_total_annonce / 5);
          
          //  Affecte a '$offset' 5 fois la valeur de la variable 
          //  $_GET['page'] - 1. Si $_GET['page'] n'existe pas, affecte 0 a
          //  '$offset'
          if (isset($_GET['page'])){
            $offset = 5 * ($_GET['page'] - 1);
            
            //  Si la valeur de la variable est strictement supérieur a 
            //  '$nb_total_page' ou strictement inférieur a 1, affecte false
            //  à $erreur
            if ($_GET['page'] > $nb_total_page || $_GET['page'] < 1){
              $erreur = true;
            }
          } else {
            $offset = 0;
          }
          
          if ($erreur || $nb_total_annonce == 0){
            echo '<ul class="annonce"><li class="annonce"> Aucune annonce posté </li></ul>';
          } else {
            $requete = "SELECT * FROM annonce WHERE id='{$id_info}' ORDER BY num DESC LIMIT 5 OFFSET " .$offset;
            $res = mysqli_query($connexion,$requete);
            
            //  Affiche au maximum 5 annonce grâce a "LIMIT 5" dans '$requete'
            echo "<ul class='annonce'>";
            while ($annonce = mysqli_fetch_array($res)){
              echo "<li class='annonce'>";
              if (isset($_POST['modifier'])){
                $href = "./modification_annonce.php?change=modifier&num={$annonce['num']}";
              } else if (isset($_POST['supprimer'])){
                $href = "./modification_annonce.php?change=supprimer&num={$annonce['num']}";
              } else {
                $href= "information_annonce.php?num={$annonce['num']}&info=on";
              }
              
              //  Permet de cliquer sur l'annonce afin d'accéder a une page ayant
              //  plus d'information sur l'annonce
              echo "<a href='{$href}' class='ref_annonce'>";
              echo "  <span class='titre'>{$annonce['titre']}</span><br>";
                      if ($annonce['id'] != "null"){
                        echo "<span class='auteur'><strong>auteur :</strong><br>
                                  {$annonce['id']}
                              </span><br>";
                      }
                      echo "<span class='image'><img class='image' src='http://localhost/projet/image/".$annonce['image']."' alt='Image indisponible'></span><br>
                      <span class='description'>{$annonce['description']}</span><br>
                      <span class='prix'>Prix : {$annonce['prix']} €</span><br>";
              echo "</a>";
              echo "</li>";
              
            }
            echo "</ul>";
          }
          
        } else {
          echo '<ul class="annonce"><li class="annonce"> Aucune annonce trouvé </li></ul>';
          
        }
        
      ?>
      
    </div>
    
    <a href="../page_principale.php" class="page_accueil">
      Page d'accueil
    </a>
    
    <div class="footer">
      <a href="./contact.php">Nous contacter</a>
    </div>
    
  </body>
  
</html>

