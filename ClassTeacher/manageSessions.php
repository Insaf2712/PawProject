
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = '';
$errorMsg = '';

// Créer une séance
if(isset($_POST['createSession'])){
    $sessionName = mysqli_real_escape_string($conn, $_POST['sessionName']);
    $sessionDate = $_POST['sessionDate'];
    $sessionTime = $_POST['sessionTime'];
    $duration = $_POST['duration'];
    $mode = $_POST['mode'];
    $classId = $_SESSION['classId'];
    $classArmId = $_SESSION['classArmId'];
    $teacherId = $_SESSION['userId'];
    
    $insertQuery = "INSERT INTO tblsessions 
                   (sessionName, classId, classArmId, teacherId, sessionDate, sessionTime, duration, mode, isOpen, dateCreated) 
                   VALUES 
                   ('$sessionName', '$classId', '$classArmId', '$teacherId', '$sessionDate', '$sessionTime', '$duration', '$mode', 0, NOW())";
    
    if(mysqli_query($conn, $insertQuery)){
        $statusMsg = "<div class='alert alert-success'>Séance créée avec succès!</div>";
    } else {
        $errorMsg = "Erreur lors de la création de la séance: " . mysqli_error($conn);
    }
}

// Ouvrir/Fermer une séance
if(isset($_GET['toggleSession'])){
    $sessionId = $_GET['toggleSession'];
    $currentStatus = mysqli_fetch_assoc(mysqli_query($conn, "SELECT isOpen FROM tblsessions WHERE Id = '$sessionId' AND teacherId = '".$_SESSION['userId']."'"));
    
    if($currentStatus){
        $newStatus = $currentStatus['isOpen'] == 1 ? 0 : 1;
        mysqli_query($conn, "UPDATE tblsessions SET isOpen = '$newStatus', dateModified = NOW() WHERE Id = '$sessionId'");
        header("Location: manageSessions.php");
        exit();
    }
}

// Récupérer les séances
$sessionsQuery = "SELECT s.*, c.className, ca.classArmName 
                  FROM tblsessions s
                  INNER JOIN tblclass c ON c.Id = s.classId
                  INNER JOIN tblclassarms ca ON ca.Id = s.classArmId
                  WHERE s.teacherId = '".$_SESSION['userId']."'
                  ORDER BY s.sessionDate DESC, s.sessionTime DESC";
$sessionsRs = $conn->query($sessionsQuery);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Gestion des Séances</title>
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
            <h1 class="h3 mb-0 text-gray-800">Gestion des Séances</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Gestion des Séances</li>
            </ol>
          </div>

          <?php echo $statusMsg; ?>
          <?php if($errorMsg): ?>
          <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Créer une Nouvelle Séance</h6>
                </div>
                <div class="card-body">
                  <form method="post">
                    <div class="form-group">
                      <label>Nom de la séance<span class="text-danger ml-2">*</span></label>
                      <input type="text" name="sessionName" class="form-control" required placeholder="Ex: Cours de Mathématiques - Chapitre 5">
                    </div>
                    
                    <div class="form-group">
                      <label>Date<span class="text-danger ml-2">*</span></label>
                      <input type="date" name="sessionDate" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                      <label>Heure<span class="text-danger ml-2">*</span></label>
                      <input type="time" name="sessionTime" class="form-control" required value="<?php echo date('H:i'); ?>">
                    </div>
                    
                    <div class="form-group">
                      <label>Durée (minutes)</label>
                      <input type="number" name="duration" class="form-control" placeholder="90" min="15" step="15">
                    </div>
                    
                    <div class="form-group">
                      <label>Mode<span class="text-danger ml-2">*</span></label>
                      <select name="mode" class="form-control" required>
                        <option value="presentiel">Présentiel</option>
                        <option value="distanciel">Distanciel</option>
                      </select>
                    </div>
                    
                    <button type="submit" name="createSession" class="btn btn-primary">
                      <i class="fas fa-plus"></i> Créer la Séance
                    </button>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Mes Séances</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Durée</th>
                        <th>Mode</th>
                        <th>Statut</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if($sessionsRs->num_rows > 0):
                        $sn = 0;
                        while($session = $sessionsRs->fetch_assoc()):
                          $sn++;
                      ?>
                      <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo $session['sessionName']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($session['sessionDate'])); ?></td>
                        <td><?php echo date('H:i', strtotime($session['sessionTime'])); ?></td>
                        <td><?php echo $session['duration'] ? $session['duration'] . ' min' : '-'; ?></td>
                        <td><?php echo ucfirst($session['mode']); ?></td>
                        <td>
                          <?php if($session['isOpen'] == 1): ?>
                            <span class="badge badge-success">Ouverte</span>
                          <?php else: ?>
                            <span class="badge badge-secondary">Fermée</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <a href="?toggleSession=<?php echo $session['Id']; ?>" class="btn btn-sm btn-<?php echo $session['isOpen'] == 1 ? 'warning' : 'success'; ?>">
                            <?php echo $session['isOpen'] == 1 ? '<i class="fas fa-lock"></i> Fermer' : '<i class="fas fa-unlock"></i> Ouvrir'; ?>
                          </a>
                          <a href="takeAttendance.php?sessionId=<?php echo $session['Id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-clipboard-check"></i> Prendre les présences
                          </a>
                        </td>
                      </tr>
                      <?php
                        endwhile;
                      else:
                      ?>
                      <tr>
                        <td colspan="8" class="text-center">Aucune séance créée</td>
                      </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
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
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable();
    });
  </script>
</body>

</html>

