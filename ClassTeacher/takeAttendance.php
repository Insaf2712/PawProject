
<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$query = "SELECT tblclass.className,tblclassarms.classArmName 
FROM tblclassteacher
INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
Where tblclassteacher.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

$sessionId = isset($_GET['sessionId']) ? $_GET['sessionId'] : null;
$sessionTermId = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Id FROM tblsessionterm WHERE isActive ='1'"))['Id'];
$dateTaken = date("Y-m-d");
$statusMsg = '';

// Vérifier si la participation est activée
$participationEnabled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT settingValue FROM tblsystemsettings WHERE settingKey = 'participation_enabled'"))['settingValue'];
$participationScale = mysqli_fetch_assoc(mysqli_query($conn, "SELECT settingValue FROM tblsystemsettings WHERE settingKey = 'participation_scale'"))['settingValue'];

// Récupérer les informations de la séance si fournie
$sessionInfo = null;
if($sessionId){
    $sessionQuery = "SELECT * FROM tblsessions WHERE Id = '$sessionId' AND teacherId = '".$_SESSION['userId']."'";
    $sessionRs = $conn->query($sessionQuery);
    if($sessionRs->num_rows > 0){
        $sessionInfo = $sessionRs->fetch_assoc();
        $dateTaken = $sessionInfo['sessionDate'];
    }
}

// Traitement du formulaire
if(isset($_POST['save'])){
    $admissionNo = $_POST['admissionNo'];
    $statusType = $_POST['statusType'];
    $comments = $_POST['comment'];
    $participation = isset($_POST['participation']) ? $_POST['participation'] : [];
    $participationScore = isset($_POST['participationScore']) ? $_POST['participationScore'] : [];
    $participationComments = isset($_POST['participationComment']) ? $_POST['participationComment'] : [];
    
    $N = count($admissionNo);
    $successCount = 0;
    
    for($i = 0; $i < $N; $i++){
        $admNo = $admissionNo[$i];
        $statType = isset($statusType[$i]) ? $statusType[$i] : 'absent';
        $comment = isset($comments[$i]) ? mysqli_real_escape_string($conn, $comments[$i]) : '';
        
        // Déterminer le statut (0=absent, 1=present)
        $status = ($statType == 'present' || $statType == 'late') ? '1' : '0';
        
        // Mettre à jour ou insérer la présence
        $checkQuery = "SELECT Id FROM tblattendance WHERE admissionNo = '$admNo' AND classId = '$_SESSION[classId]' AND classArmId = '$_SESSION[classArmId]' AND dateTimeTaken = '$dateTaken'";
        if($sessionId){
            $checkQuery .= " AND sessionId = '$sessionId'";
        }
        $checkRs = $conn->query($checkQuery);
        
        if($checkRs->num_rows > 0){
            $attendanceId = $checkRs->fetch_assoc()['Id'];
            $updateQuery = "UPDATE tblattendance SET status = '$status', statusType = '$statType', comment = '$comment', sessionId = '$sessionId'";
            
            // Gestion de la participation
            if($participationEnabled == '1' && isset($participation[$i])){
                $partValue = $participation[$i];
                $partScore = isset($participationScore[$i]) && $participationScore[$i] !== '' ? $participationScore[$i] : null;
                $partComment = isset($participationComments[$i]) ? mysqli_real_escape_string($conn, $participationComments[$i]) : null;
                
                $updateQuery .= ", participation = '$partValue'";
                if($partScore !== null){
                    $updateQuery .= ", participationScore = '$partScore'";
                }
                if($partComment){
                    $updateQuery .= ", participationComment = '$partComment'";
                }
            }
            
            $updateQuery .= " WHERE Id = '$attendanceId'";
            
            if(mysqli_query($conn, $updateQuery)){
                $successCount++;
            }
        } else {
            // Insérer une nouvelle présence
            $insertQuery = "INSERT INTO tblattendance (admissionNo, classId, classArmId, sessionTermId, sessionId, status, statusType, comment, dateTimeTaken";
            
            if($participationEnabled == '1' && isset($participation[$i])){
                $partValue = $participation[$i];
                $partScore = isset($participationScore[$i]) && $participationScore[$i] !== '' ? $participationScore[$i] : null;
                $partComment = isset($participationComments[$i]) ? mysqli_real_escape_string($conn, $participationComments[$i]) : null;
                
                $insertQuery .= ", participation";
                if($partScore !== null) $insertQuery .= ", participationScore";
                if($partComment) $insertQuery .= ", participationComment";
                
                $insertQuery .= ") VALUES ('$admNo', '$_SESSION[classId]', '$_SESSION[classArmId]', '$sessionTermId', '$sessionId', '$status', '$statType', '$comment', '$dateTaken', '$partValue'";
                if($partScore !== null) $insertQuery .= ", '$partScore'";
                if($partComment) $insertQuery .= ", '$partComment'";
                $insertQuery .= ")";
            } else {
                $insertQuery .= ") VALUES ('$admNo', '$_SESSION[classId]', '$_SESSION[classArmId]', '$sessionTermId', '$sessionId', '$status', '$statType', '$comment', '$dateTaken')";
            }
            
            if(mysqli_query($conn, $insertQuery)){
                $successCount++;
            }
        }
    }
    
    if($successCount > 0){
        $statusMsg = "<div class='alert alert-success'>Présences enregistrées avec succès pour $successCount étudiant(s)!</div>";
    } else {
        $statusMsg = "<div class='alert alert-danger'>Erreur lors de l'enregistrement des présences.</div>";
    }
}

// Récupérer les étudiants
$studentsQuery = "SELECT tblstudents.Id, tblstudents.admissionNumber, tblclass.className, tblclass.Id As classId, 
                 tblclassarms.classArmName, tblclassarms.Id AS classArmId, tblstudents.firstName,
                 tblstudents.lastName, tblstudents.otherName
                 FROM tblstudents
                 INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                 INNER JOIN tblclassarms ON tblclassarms.Id = tblstudents.classArmId
                 WHERE tblstudents.classId = '$_SESSION[classId]' AND tblstudents.classArmId = '$_SESSION[classArmId]'
                 ORDER BY tblstudents.firstName ASC";
$studentsRs = $conn->query($studentsQuery);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Prendre les Présences</title>
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
            <h1 class="h3 mb-0 text-gray-800">Prendre les Présences</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Prendre les Présences</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">
                    Étudiants de <?php echo $rrw['className'].' - '.$rrw['classArmName']; ?>
                    <?php if($sessionInfo): ?>
                      - Séance: <?php echo $sessionInfo['sessionName']; ?> (<?php echo date('d/m/Y', strtotime($sessionInfo['sessionDate'])); ?>)
                    <?php else: ?>
                      - Date: <?php echo date('d/m/Y', strtotime($dateTaken)); ?>
                    <?php endif; ?>
                  </h6>
                </div>
                <div class="table-responsive p-3">
                  <?php echo $statusMsg; ?>
                  <form method="post">
                    <table class="table align-items-center table-flush table-hover">
                      <thead class="thead-light">
                        <tr>
                          <th>#</th>
                          <th>Nom</th>
                          <th>Prénom</th>
                          <th>N° Admission</th>
                          <th>Statut</th>
                          <th>Commentaire</th>
                          <?php if($participationEnabled == '1'): ?>
                          <th>Participation</th>
                          <?php endif; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sn = 0;
                        if($studentsRs->num_rows > 0):
                          while($student = $studentsRs->fetch_assoc()):
                            $sn++;
                        ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td><?php echo $student['lastName']; ?></td>
                          <td><?php echo $student['firstName']; ?></td>
                          <td><?php echo $student['admissionNumber']; ?></td>
                          <td>
                            <input type="hidden" name="admissionNo[]" value="<?php echo $student['admissionNumber']; ?>">
                            <select name="statusType[]" class="form-control form-control-sm">
                              <option value="absent">✖ Absent</option>
                              <option value="present" selected>✔ Présent</option>
                              <option value="late">⏰ Retard</option>
                            </select>
                          </td>
                          <td>
                            <input type="text" name="comment[]" class="form-control form-control-sm" placeholder="Commentaire...">
                          </td>
                          <?php if($participationEnabled == '1'): ?>
                          <td>
                            <?php if($participationScale == 'binary'): ?>
                              <select name="participation[]" class="form-control form-control-sm">
                                <option value="">-</option>
                                <option value="1">⭐ Oui</option>
                                <option value="0">Non</option>
                              </select>
                            <?php else: ?>
                              <input type="number" name="participationScore[]" class="form-control form-control-sm" min="0" max="2" step="0.5" placeholder="0-2">
                            <?php endif; ?>
                            <input type="text" name="participationComment[]" class="form-control form-control-sm mt-1" placeholder="Commentaire participation...">
                          </td>
                          <?php endif; ?>
                        </tr>
                        <?php
                          endwhile;
                        else:
                        ?>
                        <tr>
                          <td colspan="<?php echo $participationEnabled == '1' ? '7' : '6'; ?>" class="text-center">Aucun étudiant trouvé</td>
                        </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                    <button type="submit" name="save" class="btn btn-primary">
                      <i class="fas fa-save"></i> Enregistrer les Présences
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
