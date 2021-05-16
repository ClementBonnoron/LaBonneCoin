<?php session_start();
unset($_SESSION['access']);
?>

<!DOCTYPE html>
<html>
  
  <head>
    <title>LaBonneCoin</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../css/css_page_principale.css">
    <?php
      
      //  get_end_url_nopage : renvoie la fin de l'url contenant les variables
      //  envoyé par GET. Si aucune variable, renvoie false, sinon renvoie
      //  la partie après le symbole '?' de l'url sans la variable 'page'.
      function get_end_url_nopage(){
        
        //  Récupère l'url actuel
        $lien = "http://localhost" .$_SERVER['REQUEST_URI'];
        
        //  Si l'url ne possède pas de variable, renvoie false, sinon continue
        if (stripos($lien,'?') == false){
          $false = false;
          return $false;
        }
        
        //  stocke la partie contenant les variables dans '$end' et le reste
        //  dans '$debut' et supprime la variable 'page' ainsi que sa valeur
        list($debut,$end) = explode('?',$lien);
        $end = str_replace('page=','',$end);
        if (strlen($end) == 1){
          return false;
        }
        $first_character = substr($end,0,1);
        //  Si '$end' ne contenait que la variable 'page', renvoie false
        if (is_numeric($first_character)){
          $end = substr($end,-strlen($end) + 1);
        }
        $first_character = substr($end,0,1);
        if ($first_character != '&'){
          $end = '&' . $end;
        }
        return $end;
      }
      
      //  get_part_url : sépare les noms des variables ainsi que leur valeur de
      //  la chaîne '$lien. Stocke les noms des variables dans '$part_of_url'
      //  et leur valeur dans '$value_of_url'. Renvoie false si $lien est vide,
      //  ou si possède autre chose que des séparateurs des variables (avec '&')
      //  ou qu'une variable ne possède pas de nom ou de valeur
      function get_part_url($lien,&$part_of_url,&$value_of_url){
        
        //  Séparation des variables/valeur dans un tableau '$liste' et stocke
        //  la longueur du tableau dans '$k' pour connaître le nombre
        //  de variables utilisé en GET
        $liste=explode('&',$lien);
        $k = count($liste) - 1;
        while($k > 0){
          
          //  Si une variable n'est pas sous la forme '...=...', renvoie false
          if (stripos($liste[$k],'=') == false){
            return false;
          }
          
          //  Stocke le nom de la variable dans $part et sa valeur dans $value
          list($part,$value) = explode('=',$liste[$k]);
          
          //  Si une variable n'a pas de nom ou de valeur, renvoie false
          if (empty($part) || (empty($value) && $value != '0')){
            return false;
          }
          //  Stocke un a un les variables ainsi que leur valeur dans leur
          //  tableau respectifs
          $part_of_url[$k-1] = $part;
          $value_of_url[$k-1] = $value;
          $k--;
        }
      }
    ?>
  </head>
  
  <body>
    <?php
      
      //  Récupération des variables posté par GET et stockage dans
      //  '$part_of_url' et '$value_of_url', ainsi que la partie de l'url
      //  contenant les variables dans '$actuel_url'
      //  Initialise '$erreur_url' si il y a eu une erreur lors de la 
      //  récupération des noms et valeurs des variables
      $part_of_url= array();
      $value_of_url = array();
      $actual_url = get_end_url_nopage();
      $erreur_url = get_part_url($actual_url,$part_of_url,$value_of_url);
    ?>
    
    <div class="head">
      <i><h1>LaBonneCoin</h1></i>
    </div>
    
    <a href="./page/inscription.php">
      <div  class="icone_inscription">
        Inscription
      </div>
    </a>
    
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
                    <form action="./page/page_utilisateur.php" method="post">
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
                    <form action="../part_site/page_principale.php" method="post">
                      <p class="validation_connexion">
                        <input type="submit" name="deconnexion" value="Se déconnecter">
                      </p>
                    </form>
                </div>';
        } else {
          echo '<div class="connexion">
                  <form action="../part_site/page_principale.php" method="post">
                    
                    <p class="icone_connexion">
                      <label>Identifiant : <input type="text" name="identifiant"></label><br>
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
      
    <div class="liste_annonce">
      <?php
        
        //  Connexion au serveur local
        $connexion = mysqli_connect("localhost","root","","localhost");
        $requete = "SELECT * FROM annonce";
        
        //  Si '$actual_url' est différent de false, et si '$erreur_url' n'exite
        //  pas, effectue le code suivant
        if (!(isset($erreur_url) || !($actual_url))){
          reset($part_of_url);
          reset($value_of_url);
          $tmp_array = array();
          $erreur = false;
          
          //  Parcours toute les parties du tableau '$part_of_url' et stocke
          //  une a une les valeurs dans '$part' et la clé correspondante dans
          //  '$key'
          foreach($part_of_url as $key => $part){
            
            //  Si la valeur de la variable vaut "on", la variable correpond a
            //  une catégorie, donc push le nom de la variable dans '$tmp_array'
            if ($value_of_url[$key] == "on"){
              array_push($tmp_array,$part_of_url[$key]);
              $erreur = false;
              
            //  Sinon si le nom de la variable vaut "prix', concatène "prix " 
            //  ainsi que le symbole correspondant a la valeur dans '$tmp_valeur'
            } else if ($part_of_url[$key] == "prix"){
              if ($value_of_url[$key] == "sup"){
                $tmp_valeur = "prix >= ".$tmp_valeur;
                $erreur = false;
              } else if ($value_of_url[$key] == "egal"){
                $tmp_valeur = "prix = ".$tmp_valeur;
                $erreur = false;
              } else if ($value_of_url[$key] == "inf"){
                $tmp_valeur = "prix <= '".$tmp_valeur. "'";
                $erreur = false;
              }
              
            //  Sinon si le nom de la variable vaut "valeur", concatène 
            //  '$tmp_valeur' avec la valeur correspondante
            } else if ($part_of_url[$key] == "valeur"){
              
              //  Si '$tmp_value' existe déjà, concatène la valeur a la variable
              //  Sinon affecte la valeur a '$tmp_valeur
              if (isset($tmp_valeur)){
                $tmp_valeur = $tmp_valeur.$value_of_url[$key];
              } else {
                $tmp_valeur = $value_of_url[$key];
              }
              
            //  Sinon si le nom de la variable vaut "localisation", effectue le
            //  code suivant
            } else if ($part_of_url[$key] == "localisation"){
              
              //  Vérifie si une localisation a été sélectionné. Si la valeur
              //  vaut "null", aucune localisation à été choisi
              if ($value_of_url[$key] != "null"){
                
                //  Si la localisation n'est pas une valeur numérique, affecte
                //  true a '$false', sinon affecte "localisation = (valeur)" à
                //  '$tmp_localisation'
                if (is_numeric($value_of_url[$key])){
                  $tmp_localisation = "localisation = ".$value_of_url[$key];
                } else {
                  $erreur = true;
                }
              }
              
            //  Sinon si le nom de la variable vaut "recherche", affecte a
            //  '$tmp_recherche' la valeur de la variable
            } else if ($part_of_url[$key] == "recherche"){
              $tmp_recherche = $value_of_url[$key];
              
            //  Si aucune correspondance a eu lieu, affecte a '$erreur' la
            //  la valeur de true
            } else {
              $erreur = true;
            }
          }
          
          //  Si erreur vaut true supprime les variables 'tmp', sinon effectue
          //  le code pour la recherche des annonces correspondate aux
          //  demandes de l'utilisateur
          if ($erreur){
            unset($tmp_array);
            unset($tmp_valeur);
            unset($tmp_recherche);
            unset($tmp_localisation);
          } else {
            
            //  Stocke dans une variable sous la forme ( '...', '..', .... )
            //  les noms des valeurs correspondantes a une categorie dans la
            //  variable '$liste_part'
            $liste_part = "( '".implode('\' , \'', $tmp_array)."' )";
            
            //  Si il n'y a aucune catégorie sélectionné, supprime '$liste_part'
            //  , sinon change les noms des variables par leur identifiant
            //  correspondant (valeur numérique) et les stockes sous la même
            //  forme dans une variable '$tmp_liste'
            if ($liste_part == "( '' )"){
              unset ($liste_part);
            } else {
              reset($tmp_array);
              $tmp_liste = "( ";
              foreach ($tmp_array as $tmp_cate){
                
                //  Récupère l'identifiant de la catégorie dans la bdd
                $tmp_requete = "SELECT * FROM categorie WHERE nom = '".$tmp_cate. "'";
                $res = mysqli_query($connexion,$tmp_requete);
                $val = mysqli_fetch_array($res);
                $tmp_liste .= "'".$val['id']."' ";
              }
              $tmp_liste = str_replace("' '","', '",$tmp_liste);
              $tmp_liste .= ")";
            }
          }
          
          //  Crée la fin de la requête sous la forme
          //  " WHERE ... AND ... IN ... LIKE" avec la liste des recherches
          //  si les variables temporaires existent dans une variable
          //  '$end_requete'
          if (isset($tmp_valeur)){
            $end_requete = " WHERE ".$tmp_valeur;
          }
          if (isset($liste_part)){
            
            //  Permet de récupérer toutes les annonces ayant sa catégorie dans
            //  la liste des catégories sélectionnées
            //
            //  Rappel : la requête "IN" fonctionne avec une forme :
            //  "WHERE categorie IN ( 'nom_1', 'nom_2', ... )"
            //  et équivaut a : 
            //  "WHERE (categorie='nom_1' OR categorie='nom_2' ...)"
            if (isset($end_requete)){
              $end_requete .= " AND categorie IN ".$tmp_liste;
            } else {
              $end_requete = " WHERE categorie IN ".$tmp_liste;
            }
          }
          if (isset($tmp_localisation)){
            if (isset($end_requete)){
              $end_requete .= " AND ".$tmp_localisation;
            } else {
              $end_requete = " WHERE ".$tmp_localisation;
            }
          }
          if (isset($tmp_recherche)){
            
            //  Récupère toutes les annonces ayant le mot '$tmp_recherche' dans
            //  leur titre ou dans leur description
            if (isset($end_requete)){
              $end_requete .= " AND titre LIKE '%" .$tmp_recherche ."%' OR description LIKE '%" .$tmp_recherche ."%'";
            } else {
              $end_requete = " WHERE titre LIKE '%" .$tmp_recherche ."%' OR description LIKE '%" .$tmp_recherche ."%'";
            }
          }
        }
        
        //  Initialise '$erreur' à false et récupère le nombre d'annonce
        //  qui correspondent à la recherche et le stocke dans '$provi[0]'
        $erreur = false;
        if (isset($end_requete)){
          $requete_count = "SELECT COUNT(*) FROM annonce ".$end_requete;
        } else {
          $requete_count = "SELECT COUNT(*) FROM annonce ";
        }
        $res = mysqli_query($connexion,$requete_count);
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
        
        //  Si '$erreur' est égal à true, ou aucune annonce trouvé avec les
        //  les recherches actuelles affiche un message d'erreur, sinon
        //  initialise '$requete' avec les informations précédentes
        if ($erreur || $nb_total_annonce == 0){
          $erreur = "Aucune annonce trouvé";
          echo '<ul><li>'.$erreur.'</li></ul>';
        } else {
          if (isset($end_requete)){
            $requete .= $end_requete." ORDER BY num DESC LIMIT 5 OFFSET " .$offset;
          } else {
            $requete .= " ORDER BY num DESC LIMIT 5 OFFSET " .$offset;
          }
          
          $res = mysqli_query($connexion,$requete);
          
          //  Affiche au maximum 5 annonce grâce a "LIMIT 5" dans '$requete'
          echo "<ul>";
          while ($annonce = mysqli_fetch_array($res)){
            echo "<li>";
            $href = "./page/information_annonce.php?num=".$annonce['num']."&menu=on";
            
            //  Permet de cliquer sur l'annonce afin d'accéder a une page ayant
            //  plus d'information sur l'annonce
            echo "<a href='".$href."' class='ref_annonce'>";
            echo "  <span class='titre'>{$annonce['titre']}</span><br>";
                    if ($annonce['id'] != "null"){
                      echo "<span class='auteur'><strong>auteur :</strong><br>
                                {$annonce['id']}
                            </span><br>";
                    } else {
                      echo "<span class='auteur'><strong>auteur :</strong><br>
                                anonyme
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
      ?>
    </div>
    
    <div class="page_annonce">
      
      <?php
        //  Toutes les href des îcones suivante ont les variables GET déjà
        //  existantes ajoutées afin de garder la recherche des annonces d'une
        //  page à l'autre
        
        //  Affiche les îcones permettant d'accéder aux pages précédentes. Si
        //  on déborde des bornes (tant que page > 1) affiche les îcones sans
        //  pouvoir accéder aux pages précédentes
        if (isset($_GET['page'])){
          if ($_GET['page'] > 1){
            $next_page = $_GET['page'] - 1;
            if ($actual_url){
              $href = "./page_principale.php?page=1".$actual_url;
            } else {
              $href = "./page_principale.php?page=1";
            }
            echo '<a class="submit_left_all" href="'.$href.'"> << </a>';
            if ($actual_url){
              $href = "./page_principale.php?page=".$next_page.$actual_url;
            } else {
              $href = "./page_principale.php?page=".$next_page;
            }
            echo '<a class="submit_left" href="'.$href.'"> < </a>';
          } else {
            echo '<span class="submit_left_all" href=""> << </span>';
            echo '<span class="submit_left"><</span>';
          }
        } else {
          echo '<span class="submit_left_all" href=""> << </span>';
          echo '<span class="submit_left"><</span>';
        }
        
        //  Affiche les îcones permettant d'accéder aux pages suivantes. Si on
        //  déborde des bornes (tant que page < $nb_total_page) affiche les 
        //  îcones sans pouvoir accéder aux pages suivantes
        if (isset($_GET['page'])){
          if ($_GET['page'] < $nb_total_page){
            $next_page = $_GET['page'] + 1;
            if ($actual_url){
              $href = "./page_principale.php?page=".$next_page.$actual_url;
            } else {
              $href = "./page_principale.php?page=".$next_page;
            }
            echo '<a class="submit_right" href="'.$href.'"> > </a>';
            if ($actual_url){
              $href = "./page_principale.php?page=".$nb_total_page.$actual_url;
            } else {
              $href = "./page_principale.php?page=".$nb_total_page;
            }
            echo '<a class="submit_right_all" href="'.$href.'"> >> </a>';
          } else {
            echo '<span class="submit_right"> > </span>';
            echo '<a class="submit_right_all"> >> </a>';
          }
        } else {
          if ($nb_total_page <= 1){
            echo '<span class="submit_right"> > </span>';
            echo '<a class="submit_right_all"> >> </a>';
          } else {
            if ($actual_url){
              $href = "./page_principale.php?page=2".$actual_url;
            } else {
              $href = "./page_principale.php?page=2";
            }
            echo '<a class="submit_right" href="'.$href.'"> > </a>';
            if ($actual_url){
              $href = "./page_principale.php?page=".$nb_total_page.$actual_url;
            } else {
              $href = "./page_principale.php?page=".$nb_total_page;
            }
            echo '<a class="submit_right_all" href="'.$href.'"> >> </a>';
          }
        }
      ?>
    </div>
    
    <div class="num_page">
      <?php
        
        //  Affiche tout les numéros des pages afin d'accéder à une page sans
        //  passer par toutes les autres
        $k = 1;
        while ($k <= $nb_total_page){
          if ($actual_url){
            $href = "./page_principale.php?page=".$k.$actual_url;
          } else {
            $href = "./page_principale.php?page=".$k;
          }
          echo "<a href={$href}>{$k}</a> ";
          $k++;
        }
      ?>
    </div>
    
    <a href="./page/annonce.php">
      <div class="poster_annonce">
        <p>Poster une annonce</p>
      </div>
    </a>
    
    <div class="recherche_classe">
      
      <!--
      Affiche un formulaire permettant a l'utilisateur de choisir des
      catégorie et/ou un département, un prix
      -->
      <h1>Choix de la recherche</h1>
      <form method="get" action="">
        <p>
          Catégories :<br/>
            <?php
              //  Connexion au serveur local et codage en utf8 par sécurité
              $connexion = mysqli_connect("localhost","root","","localhost");
              $res = mysqli_query($connexion,"SET NAMES UTF8");
              $res = mysqli_query($connexion,"SET CHARACTER UTF8");
              
              //  Récupération des catégories
              $requete = "SELECT * FROM categorie";
              $res = mysqli_query($connexion,$requete);
              while($fetch = mysqli_fetch_array($res)){
                $fetch_id = $fetch['id'];
                $fetch_nom = $fetch['nom'];
                
                //  Affichage des catégories dans des 'inputs'
                echo '<label class="categorie"><input type="checkbox" name="' .$fetch_nom. '"/>' .$fetch_nom. '</label><br/>';
              }
              
              //  Fermeture de la connexion au serveur local
              mysqli_close($connexion);
            ?>
        </p>
        
        <p>
          Département :
        
          <?php
            //  Connexion au serveur local et codage en utf8 par sécurité
            $connexion = mysqli_connect("localhost","root","","localhost");
            $res = mysqli_query($connexion,"SET NAMES UTF8");
            $res = mysqli_query($connexion,"SET CHARACTER UTF8");
            
            //  Récupération des départements
            $requete = "SELECT * FROM departement";
            $res = mysqli_query($connexion,$requete);
          ?>
          <select type="text" name="localisation" id="localisation" >
            <?php
            
            //  Affichage des départements dans un 'select'
            echo "<option value='null' style='background-color:#C6C6C6;' >- Sélectionner un département -</option>";
            while($fetch = mysqli_fetch_array($res)){
              $fetch_id = $fetch['id'];
              $fetch_dep = $fetch['departement'];
              
              echo '<option value="' .$fetch_id. '">' .$fetch_id. ' - ' .$fetch_dep. '</option>';
              echo $fetch_id;
            }
            
            //  Fermeture de la connexion au serveur local
            mysqli_close($connexion);
            ?>
          </select>
        </p>
        
        <p>Prix 
        <select type="text" name="prix" id="valeur">
          <option value="sup">Supérieur à </option>
          <option value="egal">Egal à</option>
          <option value="inf">Inférieure à</option>
        </select>
      
        <input type="text" name="valeur" value="0" style="text-align:center;height=50;">
      </p>    
        <input class="push_button_research" type="submit" value="Rechercher" />
        
      </form>
      
    </div>
    
    <div id="recherche_generale">
      <?php
        $href = "http://localhost" .$_SERVER['REQUEST_URI'];
      ?>
      <form action="" method="get">
        <p>
          <label class="recherche_generale">Recherche : <input class="recherche" type="text" name="recherche" placeholder="Que recherchez-vous?"></label>
        </p>
        <p class="push_button_generale">
          <input type="submit" value="Rechercher">
        </p>
      </form>
    </div>
    
    <div class="footer">
      <a href="./page/contact.php">Nous contacter</a>
    </div>
    
  </body>
  
</html>
