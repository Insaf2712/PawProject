
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

if(!isset($_SESSION['userType']) || $_SESSION['userType'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$admissionNo = $_SESSION['admissionNumber'];
$classId = $_SESSION['classId'];
$classArmId = $_SESSION['classArmId'];
$attendanceId = isset($_GET['attendanceId']) ? $_GET['attendanceId'] : null;

$statusMsg = '';
$errorMsg = '';

// Traitement du formulaire
if(isset($_POST['submit'])){
    $attendanceId = $_POST['attendanceId'];
    $justificationDate = $_POST['justificationDate'];
    $motif = mysqli_real_escape_string($conn, $_POST['motif']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Récupérer les infos de la présence
    $attendanceQuery = "SELECT * FROM tblattendance WHERE Id = '$attendanceId' AND admissionNo = '$admissionNo'";
    $attendanceRs = $conn->query($attendanceQuery);
    
    if($attendanceRs->num_rows > 0){
        $attendance = $attendanceRs->fetch_assoc();
        
        // Gestion de l'upload du fichier
        $fileName = '';
        $filePath = '';
        $fileType = '';
        
        if(isset($_FILES['justificationFile']) && $_FILES['justificationFile']['error'] == 0){
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            $fileType = $_FILES['justificationFile']['type'];
            
            if(in_array($fileType, $allowedTypes)){
                $extension = '';
                if($fileType == 'application/pdf'){
                    $extension = 'pdf';
                } elseif($fileType == 'image/jpeg' || $fileType == 'image/jpg'){
                    $extension = 'jpg';
                } elseif($fileType == 'image/png'){
                    $extension = 'png';
                }
                
                $fileName = 'justification_' . $admissionNo . '_' . time() . '.' . $extension;
                $uploadDir = '../uploads/justifications/';
                
                // Créer le dossier s'il n'existe pas
                if(!file_exists($uploadDir)){
                    mkdir($uploadDir, 0777, true);
                }
                
                $filePath = $uploadDir . $fileName;
                
                if(move_uploaded_file($_FILES['justificationFile']['tmp_name'], $filePath)){
                    $filePath = 'uploads/justifications/' . $fileName;
                } else {
                    $errorMsg = "Erreur lors de l'upload du fichier.";
                }
            } else {
                $errorMsg = "Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG.";
            }
        }
        
        if(empty($errorMsg)){
            // Insérer le justificatif
            $insertQuery = "INSERT INTO tbljustifications 
                           (admissionNo, sessionId, attendanceId, classId, classArmId, justificationDate, 
                            filePath, fileName, fileType, motif, comment, status, dateCreated) 
                           VALUES 
                           ('$admissionNo', '".$attendance['sessionId']."', '$attendanceId', '$classId', '$classArmId', 
                            '$justificationDate', '$filePath', '$fileName', '$extension', '$motif', '$comment', 
                            'pending', NOW())";
            
            if(mysqli_query($conn, $insertQuery)){
                // Mettre à jour la présence
                mysqli_query($conn, "UPDATE tblattendance SET isJustified = 1, justificationId = LAST_INSERT_ID() WHERE Id = '$attendanceId'");
                
                // Créer une notification pour le professeur
                $teacherQuery = "SELECT teacherId FROM tblsessions WHERE Id = '".$attendance['sessionId']."'";
                $teacherRs = $conn->query($teacherQuery);
                if($teacherRs->num_rows > 0){
                    $teacher = $teacherRs->fetch_assoc();
                    $studentName = $_SESSION['firstName'] . ' ' . $_SESSION['lastName'];
                    mysqli_query($conn, "INSERT INTO tblnotifications 
                                       (userId, userType, type, title, message, isRead, dateCreated, link) 
                                       VALUES 
                                       ('".$teacher['teacherId']."', 'teacher', 'justification', 
                                        'Nouveau justificatif', 
                                        'Un justificatif a été soumis par $studentName pour la séance du ".date('d/m/Y', strtotime($attendance['dateTimeTaken']))."', 
                                        0, NOW(), 'ClassTeacher/viewJustifications.php')");
                }
                
                $statusMsg = "<div class='alert alert-success'>Justificatif soumis avec succès! Il sera examiné par votre professeur.</div>";
                
                // Réinitialiser les variables
                $attendanceId = null;
            } else {
                $errorMsg = "Erreur lors de la soumission du justificatif: " . mysqli_error($conn);
            }
        }
    } else {
        $errorMsg = "Présence non trouvée.";
    }
}

// Récupérer les informations de la présence si attendanceId est fourni
$attendanceInfo = null;
if($attendanceId){
    $attendanceQuery = "SELECT a.*, s.sessionName, s.sessionDate, s.sessionTime, c.courseName, c.courseCode
                       FROM tblattendance a
                       LEFT JOIN tblsessions s ON s.Id = a.sessionId
                       LEFT JOIN tblteachercourses tc ON tc.classId = a.classId AND tc.classArmId = a.classArmId
                       LEFT JOIN tblcourses c ON c.Id = tc.courseId
                       WHERE a.Id = '$attendanceId' AND a.admissionNo = '$admissionNo'";
    $attendanceRs = $conn->query($attendanceQuery);
    if($attendanceRs->num_rows > 0){
        $attendanceInfo = $attendanceRs->fetch_assoc();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="../img/logo/attnlg.jpg" rel="icon">
  <title>Soumettre un Justificatif</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
      <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
       <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Soumettre un Justificatif</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Soumettre un Justificatif</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Formulaire de Justificatif</h6>
                </div>
                <div class="card-body">
                  <?php echo $statusMsg; ?>
                  <?php if($errorMsg): ?>
                  <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                  <?php endif; ?>
                  
                  <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="attendanceId" value="<?php echo $attendanceId; ?>">
                    
                    <?php if($attendanceInfo): ?>
                    <div class="form-group row mb-3">
                      <label class="col-sm-3 col-form-label">Séance concernée</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?php echo $attendanceInfo['courseCode'] . ' - ' . $attendanceInfo['courseName'] . ' - ' . date('d/m/Y', strtotime($attendanceInfo['dateTimeTaken'])); ?>" readonly>
                      </div>
                    </div>
                    <?php else: ?>
                    <div class="form-group row mb-3">
                      <label class="col-sm-3 col-form-label">Sélectionner une absence</label>
                      <div class="col-sm-9">
                        <select name="attendanceId" class="form-control" required>
                          <option value="">--Sélectionner--</option>
                          <?php
                          $absencesQuery = "SELECT a.*, s.sessionDate, s.sessionTime, c.courseName, c.courseCode
                                           FROM tblattendance a
                                           LEFT JOIN tblsessions s ON s.Id = a.sessionId
                                           LEFT JOIN tblteachercourses tc ON tc.classId = a.classId AND tc.classArmId = a.classArmId
                                           LEFT JOIN tblcourses c ON c.Id = tc.courseId
                                           WHERE a.admissionNo = '$admissionNo' AND a.classId = '$classId' AND a.classArmId = '$classArmId'
                                           AND a.statusType = 'absent' AND (a.isJustified = 0 OR a.isJustified IS NULL)
                                           ORDER BY a.dateTimeTaken DESC";
                          $absencesRs = $conn->query($absencesQuery);
                          while($absence = $absencesRs->fetch_assoc()):
                          ?>
                          <option value="<?php echo $absence['Id']; ?>">
                            <?php echo ($absence['courseCode'] ? $absence['courseCode'] . ' - ' : '') . 
                                      ($absence['courseName'] ? $absence['courseName'] . ' - ' : '') . 
                                      date('d/m/Y', strtotime($absence['dateTimeTaken'])); ?>
                          </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group row mb-3">
                      <label class="col-sm-3 col-form-label">Date de l'absence<span class="text-danger ml-2">*</span></label>
                      <div class="col-sm-9">
                        <input type="date" name="justificationDate" class="form-control" 
                               value="<?php echo $attendanceInfo ? date('Y-m-d', strtotime($attendanceInfo['dateTimeTaken'])) : date('Y-m-d'); ?>" required>
                      </div>
                    </div>
                    
                    <div class="form-group row mb-3">
                      <label class="col-sm-3 col-form-label">Motif<span class="text-danger ml-2">*</span></label>
                      <div class="col-sm-9">
                        <textarea name="motif" class="form-control" rows="3" required placeholder="Décrivez le motif de votre absence..."></textarea>
                      </div>
                    </div>
                    
                    <div class="form-group row mb-3">
                      <label class="col-sm-3 col-form-label">Commentaire</label>
                      <div class="col-sm-9">
                        <textarea name="comment" class="form-control" rows="3" placeholder="Commentaire supplémentaire (optionnel)..."></textarea>
                      </div>
                    </div>
                    
                    <div class="form-group row mb-3">
                      <label class="col-sm-3 col-form-label">Pièce jointe</label>
                      <div class="col-sm-9">
                        <input type="file" name="justificationFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Formats acceptés: PDF, JPG, PNG (Max 5MB)</small>
                      </div>
                    </div>
                    
                    <div class="form-group row mb-3">
                      <div class="col-sm-9 offset-sm-3">
                        <button type="submit" name="submit" class="btn btn-primary">
                          <i class="fas fa-paper-plane"></i> Soumettre le Justificatif
                        </button>
                        <a href="viewJustifications.php" class="btn btn-secondary">
                          <i class="fas fa-list"></i> Voir mes justificatifs
                        </a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
       <?php include "Includes/footer.php";?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>

