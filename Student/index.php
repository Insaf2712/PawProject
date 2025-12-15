
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

// Récupérer les cours de l'étudiant
$coursesQuery = "SELECT DISTINCT c.Id, c.courseName, c.courseCode 
                  FROM tblcourses c
                  INNER JOIN tblstudentcourses sc ON sc.courseId = c.Id
                  WHERE sc.admissionNo = '$admissionNo' AND sc.classId = '$classId' AND sc.classArmId = '$classArmId'";
$coursesRs = $conn->query($coursesQuery);

// Calculer les statistiques globales
// Taux d'assiduité
$totalSessionsQuery = "SELECT COUNT(*) as total FROM tblattendance WHERE admissionNo = '$admissionNo' AND classId = '$classId' AND classArmId = '$classArmId'";
$totalSessionsRs = $conn->query($totalSessionsQuery);
$totalSessions = $totalSessionsRs->fetch_assoc()['total'];

$presentQuery = "SELECT COUNT(*) as total FROM tblattendance WHERE admissionNo = '$admissionNo' AND classId = '$classId' AND classArmId = '$classArmId' AND statusType = 'present'";
$presentRs = $conn->query($presentQuery);
$presentCount = $presentRs->fetch_assoc()['total'];

$attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 2) : 0;

// Taux de retard
$lateQuery = "SELECT COUNT(*) as total FROM tblattendance WHERE admissionNo = '$admissionNo' AND classId = '$classId' AND classArmId = '$classArmId' AND statusType = 'late'";
$lateRs = $conn->query($lateQuery);
$lateCount = $lateRs->fetch_assoc()['total'];
$lateRate = $totalSessions > 0 ? round(($lateCount / $totalSessions) * 100, 2) : 0;

// Taux de participation (si activé)
$participationQuery = "SELECT AVG(participationScore) as avgScore, COUNT(*) as total 
                       FROM tblattendance 
                       WHERE admissionNo = '$admissionNo' AND classId = '$classId' AND classArmId = '$classArmId' 
                       AND participation IS NOT NULL";
$participationRs = $conn->query($participationQuery);
$participationData = $participationRs->fetch_assoc();
$participationRate = $participationData['avgScore'] ? round($participationData['avgScore'], 2) : 0;
$participationTotal = $participationData['total'];

// Vérifier si la participation est activée
$participationEnabled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT settingValue FROM tblsystemsettings WHERE settingKey = 'participation_enabled'"))['settingValue'];

// Absences non justifiées
$unjustifiedQuery = "SELECT COUNT(*) as total FROM tblattendance 
                     WHERE admissionNo = '$admissionNo' AND classId = '$classId' AND classArmId = '$classArmId' 
                     AND statusType = 'absent' AND isJustified = 0";
$unjustifiedRs = $conn->query($unjustifiedQuery);
$unjustifiedCount = $unjustifiedRs->fetch_assoc()['total'];

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
  <title>Accueil Étudiant</title>
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
            <h1 class="h3 mb-0 text-gray-800">Accueil Étudiant</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
            </ol>
          </div>

          <div class="row mb-3">
            <!-- Taux d'Assiduité Card -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Taux d'Assiduité</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $attendanceRate; ?>%</div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <span><?php echo $presentCount; ?> présences sur <?php echo $totalSessions; ?> séances</span>
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Taux de Retard Card -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Taux de Retard</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $lateRate; ?>%</div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <span><?php echo $lateCount; ?> retards</span>
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php if($participationEnabled == '1'): ?>
            <!-- Taux de Participation Card -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Participation Moyenne</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $participationRate; ?>/2</div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <span><?php echo $participationTotal; ?> évaluations</span>
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-star fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Absences Non Justifiées Card -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Absences Non Justifiées</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $unjustifiedCount; ?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <?php if($unjustifiedCount > 0): ?>
                        <span class="text-danger">Action requise</span>
                        <?php else: ?>
                        <span class="text-success">À jour</span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mes Cours -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Mes Cours</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Nom du Cours</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if($coursesRs->num_rows > 0):
                        $sn = 0;
                        while($course = $coursesRs->fetch_assoc()):
                          $sn++;
                      ?>
                      <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo $course['courseCode']; ?></td>
                        <td><?php echo $course['courseName']; ?></td>
                        <td>
                          <a href="viewAttendance.php?courseId=<?php echo $course['Id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Voir les présences
                          </a>
                        </td>
                      </tr>
                      <?php
                        endwhile;
                      else:
                      ?>
                      <tr>
                        <td colspan="4" class="text-center">Aucun cours trouvé</td>
                      </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
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
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTable').DataTable();
    });
  </script>
</body>

</html>

