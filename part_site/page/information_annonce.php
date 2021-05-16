<?php session_start();
unset($_SESSION['access']);
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title>Information annonce</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../../css/css_page_information.css">
  </head>

  <body>
    
    <div class="head">
       <i><h1>LaBonneCoin</h1></i>
    </div>
    
    <?php
    
      //  Connexion au serveur local et codage en utf8 par sécurité
      $connexion = mysqli_connect("localhost","root","","localhost");
      
      //  Si il y a la sélection d'une annonce par une variable GET, effectue
      //  le code suivant
      if (isset($_GET['num'])){
        
        //  Récupère les informations de l'annonce correspondante
        $requete = "SELECT * FROM annonce WHERE num='".$_GET['num']."'";
        
        $res = mysqli_query($connexion,$requete);
        $nb_annonce = mysqli_num_rows($res);
        
        //  Si aucune annonce correspondante, affiche erreur
        if ($nb_annonce <= 0){
          
          echo "<span class='erreur'>aucune annonce correspondante trouvée
                  <img src='../../image/erreur_connexion.png' alt='image indisponible'>
                  <br><br>Ceci est une image, non un jeu :)
                </span>";
        
        //  Sinon met les informations dans la variable '$annonce'
        } else {
          
          $annonce = mysqli_fetch_array($res);
          
          //  Si l'annonce a été créé par une personne inscrite, récupère
          //  son mail
          if ($annonce['id'] != 'null'){
            $requete = "SELECT email FROM inscription WHERE id='".$annonce['id']."'";
            $res = mysqli_query($connexion,$requete);
            $mail_inscri = mysqli_fetch_array($res);
          }
          
          //  Récupère le département mis en information dans l'annonce
          if (isset($annonce['localisation'])){
            $requete = "SELECT departement FROM departement WHERE id='".$annonce['localisation']."'";
            $res = mysqli_query($connexion,"SET NAMES UTF8");
            $res = mysqli_query($connexion,"SET CHARACTER UTF8");
            $res = mysqli_query($connexion,$requete);
            $departement_annonce = mysqli_fetch_array($res);
          }
        }
      }
      
      //  Si l'annonce existe, exécute le code entre le 'if' et le 'endif' 
      //  pour afficher les informations de l'annonce
      if (isset($_GET['num']) && $nb_annonce > 0):
    ?>
    
    <div class="affichage_annonce">
      <table>
        <thead>
          <tr>
            <td colspan=3 height=10 style="background-color:purple;border-color:purple;">
            </td>
          </tr>
        </thead>
        
        <tbody>
          <tr>
            <td colspan=2  style="height:10%;">
              <?php echo "<strong>Titre annonce :</strong> {$annonce['titre']}"; ?>
            </td>
            <td colspan=1>
              <?php 
                if ($annonce['id'] != 'null'){
                  echo " Auteur : {$annonce['id']}";
                } else {
                  echo "Auteur anonyme";
                }
              ?>
            </td>
          </tr>
          
          <tr>
            <td rowspan=1 height=200>
              <?php
                $href = "http://localhost/projet/image/".$annonce['image'];
                echo "<img class='image' src='{$href}' alt='Image indisponible'>";
              ?>
            </td>
            
            <td rowspan=2 colspan=2 height="50" style="float:top;">
              <span style="position:absolute;top:15%;">
                <h3>Description</h3>
                <p><?php echo $annonce['description']; ?></p>
                <p>&nbsp </p>
              </span>
              <hr>
              <br>
              <span style="position:absolute;top:45%;">
                <h3>localisation</h3>
                <p><?php echo "{$annonce['localisation']} - {$departement_annonce['departement']}"; ?></p>
              </span>
            </td>
          </tr>
          
          <tr>
            <td rowspan=2>
              <strong>Comment me contacter ?</strong>
              <?php
                echo "<ul>";
                
                  echo "<li><u>Téléphone</u> :<br> {$annonce['telephone']}</li>";
                  echo "<br>";
                  if ($annonce['id'] != 'null'){
                    echo "<li><u>Email de l'auteur</u> :<br> {$mail_inscri['email']}</li>";
                  }
                echo "</ul>";
              ?>
            </td>
          </tr>
          <tr>
            <td colspan=2 style="border-top:none;">
              <span class="prix">
                Prix : <?php echo $annonce['prix']; ?> €
              </span>
            </td>
          </tr>
        </tbody>
        
        <tfoot>
          <tr>
            <td colspan=3 height=10 style="background-color:purple;border-color:purple;">
            </td>
          </tr>
        </tfoot>
        
      </table>
      
    </div>
    
    <?php endif; 
      
      if (isset($_GET['menu']) && $_GET['menu'] == 'on'){
        echo "<div id='page_acceuil'>
                <p class='page_accueil'>
                  <a href='../page_principale.php'>Page d'acceuil</a>
                </p>
              </div>";
      } else if (isset($_GET['info']) && $_GET['info'] == 'on'){
        echo "<div id='page_acceuil'>
                <p class='page_accueil'>
                  <a href='./page_utilisateur.php'>Page d'utilisateur</a>
                </p>
              </div>";
      } else {
        echo "<div id='page_acceuil'>
                <p class='page_accueil'>
                  <a href='../page_principale.php'>Erreur dans l'url, retournez au menu</a>
                </p>
              </div>";
      }
      
    ?>
    
    <div class="footer">
      <a href="./contact.php">Nous contacter</a>
    </div>
    
  </body>
  
</html>

