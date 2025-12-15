
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

if(!isset($_SESSION['userType']) || $_SESSION['userType'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$statusMsg = '';
$errorMsg = '';

if(isset($_POST['changePassword'])){
    $currentPassword = md5($_POST['currentPassword']);
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    $userId = $_SESSION['userId'];
    $admissionNo = $_SESSION['admissionNumber'];
    
    // Vérifier le mot de passe actuel
    $checkQuery = "SELECT * FROM tblstudents WHERE Id = '$userId' AND password = '$currentPassword'";
    $checkRs = $conn->query($checkQuery);
    
    if($checkRs->num_rows > 0){
        if($newPassword == $confirmPassword){
            if(strlen($newPassword) >= 6){
                $newPasswordMd5 = md5($newPassword);
                $updateQuery = "UPDATE tblstudents SET password = '$newPasswordMd5' WHERE Id = '$userId'";
                
                if(mysqli_query($conn, $updateQuery)){
                    // Log
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    mysqli_query($conn, "INSERT INTO tbllogs (userId, userType, action, details, ipAddress, userAgent, dateCreated) VALUES ('$userId', 'student', 'Changement mot de passe', 'Mot de passe modifié', '$ip', '$userAgent', NOW())");
                    
                    $statusMsg = "<div class='alert alert-success'>Mot de passe modifié avec succès!</div>";
                } else {
                    $errorMsg = "Erreur lors de la modification du mot de passe.";
                }
            } else {
                $errorMsg = "Le mot de passe doit contenir au moins 6 caractères.";
            }
        } else {
            $errorMsg = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $errorMsg = "Mot de passe actuel incorrect.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../img/logo/attnlg.jpg" rel="icon">
  <title>Modifier le Mot de Passe</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php";?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Modifier le Mot de Passe</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Modifier le Mot de Passe</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Changement de Mot de Passe</h6>
                </div>
                <div class="card-body">
                  <?php echo $statusMsg; ?>
                  <?php if($errorMsg): ?>
                  <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                  <?php endif; ?>
                  
                  <form method="post">
                    <div class="form-group">
                      <label>Mot de passe actuel<span class="text-danger ml-2">*</span></label>
                      <input type="password" name="currentPassword" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                      <label>Nouveau mot de passe<span class="text-danger ml-2">*</span></label>
                      <input type="password" name="newPassword" class="form-control" required minlength="6">
                      <small class="form-text text-muted">Minimum 6 caractères</small>
                    </div>
                    
                    <div class="form-group">
                      <label>Confirmer le nouveau mot de passe<span class="text-danger ml-2">*</span></label>
                      <input type="password" name="confirmPassword" class="form-control" required minlength="6">
                    </div>
                    
                    <button type="submit" name="changePassword" class="btn btn-primary">
                      <i class="fas fa-save"></i> Modifier le Mot de Passe
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php include "Includes/footer.php";?>
    </div>
  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>

