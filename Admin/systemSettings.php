
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = '';
$errorMsg = '';

// Récupérer les paramètres actuels
$settings = [];
$settingsQuery = "SELECT * FROM tblsystemsettings";
$settingsRs = $conn->query($settingsQuery);
while($row = $settingsRs->fetch_assoc()){
    $settings[$row['settingKey']] = $row;
}

// Sauvegarder les paramètres
if(isset($_POST['saveSettings'])){
    foreach($_POST['settings'] as $key => $value){
        $value = mysqli_real_escape_string($conn, $value);
        $checkQuery = "SELECT Id FROM tblsystemsettings WHERE settingKey = '$key'";
        $checkRs = $conn->query($checkQuery);
        
        if($checkRs->num_rows > 0){
            $updateQuery = "UPDATE tblsystemsettings SET settingValue = '$value', dateModified = NOW() WHERE settingKey = '$key'";
            mysqli_query($conn, $updateQuery);
        } else {
            $insertQuery = "INSERT INTO tblsystemsettings (settingKey, settingValue, dateModified) VALUES ('$key', '$value', NOW())";
            mysqli_query($conn, $insertQuery);
        }
    }
    
    // Log
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    mysqli_query($conn, "INSERT INTO tbllogs (userId, userType, action, details, ipAddress, userAgent, dateCreated) VALUES ('".$_SESSION['userId']."', 'admin', 'Modification paramètres système', 'Paramètres modifiés', '$ip', '$userAgent', NOW())");
    
    $statusMsg = "<div class='alert alert-success'>Paramètres sauvegardés avec succès!</div>";
    
    // Recharger les paramètres
    $settings = [];
    $settingsRs = $conn->query($settingsQuery);
    while($row = $settingsRs->fetch_assoc()){
        $settings[$row['settingKey']] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Paramètres Système</title>
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
            <h1 class="h3 mb-0 text-gray-800">Paramètres Système</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Paramètres Système</li>
            </ol>
          </div>

          <?php echo $statusMsg; ?>
          <?php if($errorMsg): ?>
          <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-8">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Configuration</h6>
                </div>
                <div class="card-body">
                  <form method="post">
                    <div class="form-group">
                      <label>Seuil d'Absences (nombre d'absences avant alerte)</label>
                      <input type="number" name="settings[absence_threshold]" class="form-control" 
                             value="<?php echo isset($settings['absence_threshold']) ? $settings['absence_threshold']['settingValue'] : '5'; ?>" 
                             min="1" required>
                      <small class="form-text text-muted">Nombre d'absences non justifiées avant d'alerter l'étudiant</small>
                    </div>
                    
                    <div class="form-group">
                      <label>Auto-enregistrement</label>
                      <select name="settings[auto_enrollment]" class="form-control" required>
                        <option value="0" <?php echo (isset($settings['auto_enrollment']) && $settings['auto_enrollment']['settingValue'] == '0') ? 'selected' : ''; ?>>Désactivé</option>
                        <option value="1" <?php echo (isset($settings['auto_enrollment']) && $settings['auto_enrollment']['settingValue'] == '1') ? 'selected' : ''; ?>>Activé</option>
                      </select>
                      <small class="form-text text-muted">Permet aux étudiants de s'enregistrer automatiquement aux séances ouvertes</small>
                    </div>
                    
                    <div class="form-group">
                      <label>Participation Activée</label>
                      <select name="settings[participation_enabled]" class="form-control" required>
                        <option value="0" <?php echo (isset($settings['participation_enabled']) && $settings['participation_enabled']['settingValue'] == '0') ? 'selected' : ''; ?>>Désactivée</option>
                        <option value="1" <?php echo (isset($settings['participation_enabled']) && $settings['participation_enabled']['settingValue'] == '1') ? 'selected' : ''; ?>>Activée</option>
                      </select>
                      <small class="form-text text-muted">Active le système de notation de participation</small>
                    </div>
                    
                    <div class="form-group">
                      <label>Type de Barème Participation</label>
                      <select name="settings[participation_scale]" class="form-control" required>
                        <option value="binary" <?php echo (isset($settings['participation_scale']) && $settings['participation_scale']['settingValue'] == 'binary') ? 'selected' : ''; ?>>Binaire (Oui/Non)</option>
                        <option value="scale" <?php echo (isset($settings['participation_scale']) && $settings['participation_scale']['settingValue'] == 'scale') ? 'selected' : ''; ?>>Barème (0 à 2 points)</option>
                      </select>
                      <small class="form-text text-muted">Choisissez le type de notation pour la participation</small>
                    </div>
                    
                    <button type="submit" name="saveSettings" class="btn btn-primary">
                      <i class="fas fa-save"></i> Sauvegarder les Paramètres
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

