
<?php 
include 'Includes/dbcon.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>RuangAdmin - Login</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-login">
  <!-- Login Content -->
  <div class="container-login">
    <div class="row justify-content-center">
      <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card shadow-sm my-5">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form">
                  <div class="text-center">
                    <img src="img/logo/attnlg.jpg" style="width:100px;height:100px">
                    <br><br>
                    <h1 class="h4 text-gray-900 mb-4">Mot de Passe Oublié</h1>
                  </div>
                  <form class="user" method="Post" action="">
                    <div class="form-group">
                      <select required name="userType" class="form-control mb-3">
                        <option value="">--Sélectionner le rôle--</option>
                        <option value="Administrator">Administrateur</option>
                        <option value="ClassTeacher">Professeur</option>
                        <option value="Student">Étudiant</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <input type="email" class="form-control" required name="email" id="exampleInputEmail" placeholder="Entrer l'adresse email">
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small" style="line-height: 1.5rem;">
                        <input type="checkbox" class="custom-control-input" id="customCheck">
                        <!-- <label class="custom-control-label" for="customCheck">Remember
                          Me</label> -->
                      </div>
                    </div>
                    <div class="form-group">
                        <input type="submit"  class="btn btn-primary btn-block" value="Envoyer" name="submit" />
                    </div>
                     </form>

                    <?php

              if(isset($_POST['submit'])){
                $email = $_POST['email'];
                $userType = isset($_POST['userType']) ? $_POST['userType'] : '';
                
                if(empty($userType)){
                  echo "<div class='alert alert-danger'>Veuillez sélectionner un type d'utilisateur.</div>";
                } else {
                  // Vérifier si l'email existe
                  $table = '';
                  $idField = 'Id';
                  
                  if($userType == 'Administrator'){
                    $table = 'tbladmin';
                  } elseif($userType == 'ClassTeacher'){
                    $table = 'tblclassteacher';
                  } elseif($userType == 'Student'){
                    $table = 'tblstudents';
                    $idField = 'admissionNumber';
                  }
                  
                  if($table){
                    $checkQuery = "SELECT * FROM $table WHERE emailAddress = '$email'";
                    $checkRs = $conn->query($checkQuery);
                    
                    if($checkRs->num_rows > 0){
                      $user = $checkRs->fetch_assoc();
                      
                      // Générer un token
                      $token = bin2hex(random_bytes(32));
                      $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                      
                      // Insérer dans la table password reset
                      $insertQuery = "INSERT INTO tblpasswordreset (email, userType, token, expires, isUsed, dateCreated) 
                                     VALUES ('$email', '$userType', '$token', '$expires', 0, NOW())";
                      
                      if(mysqli_query($conn, $insertQuery)){
                        // En production, envoyer un email avec le lien de réinitialisation
                        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/resetPassword.php?token=$token";
                        
                        echo "<div class='alert alert-success'>
                                Un email de réinitialisation a été envoyé à $email.<br>
                                <small>Lien de réinitialisation: <a href='$resetLink'>$resetLink</a></small>
                              </div>";
                      } else {
                        echo "<div class='alert alert-danger'>Erreur lors de la génération du lien de réinitialisation.</div>";
                      }
                    } else {
                      echo "<div class='alert alert-danger'>Aucun compte trouvé avec cet email.</div>";
                    }
                  }
                }
              }
			?>

                    <!-- <hr>
                    <a href="index.html" class="btn btn-google btn-block">
                      <i class="fab fa-google fa-fw"></i> Login with Google
                    </a>
                    <a href="index.html" class="btn btn-facebook btn-block">
                      <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
                    </a> -->
                  <hr>
                  <div class="text-center">
                    <a class="font-weight-bold small" href="index.php">Retour à la connexion</a>
                  </div>
                  <div class="text-center">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Login Content -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>