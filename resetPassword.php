
<?php 
include 'Includes/dbcon.php';
session_start();

$token = isset($_GET['token']) ? $_GET['token'] : '';
$statusMsg = '';
$errorMsg = '';
$validToken = false;

if($token){
    // Vérifier le token
    $tokenQuery = "SELECT * FROM tblpasswordreset WHERE token = '$token' AND isUsed = 0 AND expires > NOW()";
    $tokenRs = $conn->query($tokenQuery);
    
    if($tokenRs->num_rows > 0){
        $tokenData = $tokenRs->fetch_assoc();
        $validToken = true;
        $email = $tokenData['email'];
        $userType = $tokenData['userType'];
        
        // Traitement du formulaire
        if(isset($_POST['resetPassword'])){
            $newPassword = $_POST['newPassword'];
            $confirmPassword = $_POST['confirmPassword'];
            
            if($newPassword == $confirmPassword){
                if(strlen($newPassword) >= 6){
                    $newPasswordMd5 = md5($newPassword);
                    
                    // Déterminer la table
                    $table = '';
                    if($userType == 'Administrator'){
                        $table = 'tbladmin';
                    } elseif($userType == 'ClassTeacher'){
                        $table = 'tblclassteacher';
                    } elseif($userType == 'Student'){
                        $table = 'tblstudents';
                    }
                    
                    if($table){
                        $updateQuery = "UPDATE $table SET password = '$newPasswordMd5' WHERE emailAddress = '$email'";
                        
                        if(mysqli_query($conn, $updateQuery)){
                            // Marquer le token comme utilisé
                            mysqli_query($conn, "UPDATE tblpasswordreset SET isUsed = 1 WHERE token = '$token'");
                            
                            $statusMsg = "<div class='alert alert-success'>Mot de passe réinitialisé avec succès! <a href='index.php'>Cliquez ici pour vous connecter</a></div>";
                            $validToken = false; // Désactiver le formulaire
                        } else {
                            $errorMsg = "Erreur lors de la réinitialisation du mot de passe.";
                        }
                    }
                } else {
                    $errorMsg = "Le mot de passe doit contenir au moins 6 caractères.";
                }
            } else {
                $errorMsg = "Les mots de passe ne correspondent pas.";
            }
        }
    } else {
        $errorMsg = "Lien de réinitialisation invalide ou expiré.";
    }
} else {
    $errorMsg = "Token manquant.";
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Réinitialiser le Mot de Passe</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-login">
  <div class="container-login">
    <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-8 col-md-9">
        <div class="card shadow-sm my-5">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form">
                  <div class="text-center">
                    <img src="img/logo/attnlg.jpg" style="width:100px;height:100px">
                    <br><br>
                    <h1 class="h4 text-gray-900 mb-4">Réinitialiser le Mot de Passe</h1>
                  </div>
                  
                  <?php echo $statusMsg; ?>
                  <?php if($errorMsg): ?>
                  <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                  <?php endif; ?>
                  
                  <?php if($validToken): ?>
                  <form class="user" method="post" action="">
                    <div class="form-group">
                      <input type="password" class="form-control" required name="newPassword" placeholder="Nouveau mot de passe" minlength="6">
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control" required name="confirmPassword" placeholder="Confirmer le mot de passe" minlength="6">
                    </div>
                    <div class="form-group">
                      <input type="submit" class="btn btn-primary btn-block" value="Réinitialiser" name="resetPassword" />
                    </div>
                  </form>
                  <?php else: ?>
                  <div class="text-center">
                    <a href="index.php" class="btn btn-primary">Retour à la connexion</a>
                  </div>
                  <?php endif; ?>
                  
                  <hr>
                  <div class="text-center">
                    <a class="font-weight-bold small" href="index.php">Retour à la connexion</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>

