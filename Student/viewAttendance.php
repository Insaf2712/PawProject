
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
$courseId = isset($_GET['courseId']) ? $_GET['courseId'] : null;

// Vérifier si la participation est activée
$participationEnabled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT settingValue FROM tblsystemsettings WHERE settingKey = 'participation_enabled'"))['settingValue'];

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
  <title>Mes Présences</title>
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
            <h1 class="h3 mb-0 text-gray-800">Mes Présences</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Mes Présences</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Liste des Séances</h6>
                </div>
                <div class="card-body">
                  <form method="get" action="">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Filtrer par cours</label>
                        <select name="courseId" class="form-control mb-3" onchange="this.form.submit()">
                          <option value="">Tous les cours</option>
                          <?php
                          $coursesQuery = "SELECT DISTINCT c.Id, c.courseName, c.courseCode 
                                          FROM tblcourses c
                                          INNER JOIN tblstudentcourses sc ON sc.courseId = c.Id
                                          WHERE sc.admissionNo = '$admissionNo' AND sc.classId = '$classId' AND sc.classArmId = '$classArmId'";
                          $coursesRs = $conn->query($coursesQuery);
                          while($course = $coursesRs->fetch_assoc()):
                          ?>
                          <option value="<?php echo $course['Id']; ?>" <?php echo ($courseId == $course['Id']) ? 'selected' : ''; ?>>
                            <?php echo $course['courseCode'] . ' - ' . $course['courseName']; ?>
                          </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Cours</th>
                        <th>Statut</th>
                        <?php if($participationEnabled == '1'): ?>
                        <th>Participation</th>
                        <?php endif; ?>
                        <th>Commentaire</th>
                        <th>Justificatif</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT a.*, s.sessionName, s.sessionDate, s.sessionTime, s.duration, s.mode,
                               c.courseName, c.courseCode, t.termName,
                               j.status as justificationStatus, j.motif as justificationMotif
                               FROM tblattendance a
                               LEFT JOIN tblsessions s ON s.Id = a.sessionId
                               LEFT JOIN tblsessionterm st ON st.Id = a.sessionTermId
                               LEFT JOIN tblterm t ON t.Id = st.termId
                               LEFT JOIN tblcourses c ON c.Id = (SELECT courseId FROM tblteachercourses WHERE classId = a.classId AND classArmId = a.classArmId LIMIT 1)
                               LEFT JOIN tbljustifications j ON j.attendanceId = a.Id
                               WHERE a.admissionNo = '$admissionNo' AND a.classId = '$classId' AND a.classArmId = '$classArmId'";
                      
                      if($courseId){
                        $query .= " AND c.Id = '$courseId'";
                      }
                      
                      $query .= " ORDER BY a.dateTimeTaken DESC, s.sessionTime DESC";
                      
                      $rs = $conn->query($query);
                      $sn = 0;
                      
                      if($rs->num_rows > 0):
                        while($row = $rs->fetch_assoc()):
                          $sn++;
                          $statusType = $row['statusType'];
                          $statusIcon = '';
                          $statusText = '';
                          $statusClass = '';
                          
                          switch($statusType){
                            case 'present':
                              $statusIcon = '✔';
                              $statusText = 'Présent';
                              $statusClass = 'text-success';
                              break;
                            case 'absent':
                              $statusIcon = '✖';
                              $statusText = 'Absent';
                              $statusClass = 'text-danger';
                              break;
                            case 'late':
                              $statusIcon = '⏰';
                              $statusText = 'Retard';
                              $statusClass = 'text-warning';
                              break;
                            default:
                              $statusIcon = '✖';
                              $statusText = 'Absent';
                              $statusClass = 'text-danger';
                          }
                          
                          if($row['isJustified'] == 1 || $row['justificationStatus'] == 'accepted'){
                            $statusText .= ' 📄 Justifié';
                          }
                      ?>
                      <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['dateTimeTaken'])); ?></td>
                        <td><?php echo $row['sessionTime'] ? date('H:i', strtotime($row['sessionTime'])) : '-'; ?></td>
                        <td><?php echo $row['courseCode'] ? $row['courseCode'] . ' - ' . $row['courseName'] : '-'; ?></td>
                        <td class="<?php echo $statusClass; ?>">
                          <strong><?php echo $statusIcon . ' ' . $statusText; ?></strong>
                        </td>
                        <?php if($participationEnabled == '1'): ?>
                        <td>
                          <?php 
                          if($row['participation'] !== NULL):
                            if($row['participationScore'] !== NULL):
                              echo $row['participationScore'] . '/2';
                            else:
                              echo $row['participation'] == 1 ? '⭐ Oui' : 'Non';
                            endif;
                            if($row['participationComment']):
                              echo '<br><small class="text-muted">' . $row['participationComment'] . '</small>';
                            endif;
                          else:
                            echo '-';
                          endif;
                          ?>
                        </td>
                        <?php endif; ?>
                        <td><?php echo $row['comment'] ? $row['comment'] : '-'; ?></td>
                        <td>
                          <?php if($row['justificationStatus']): ?>
                            <?php if($row['justificationStatus'] == 'pending'): ?>
                              <span class="badge badge-warning">En attente</span>
                            <?php elseif($row['justificationStatus'] == 'accepted'): ?>
                              <span class="badge badge-success">Accepté</span>
                            <?php elseif($row['justificationStatus'] == 'refused'): ?>
                              <span class="badge badge-danger">Refusé</span>
                            <?php endif; ?>
                          <?php elseif($statusType == 'absent' && !$row['isJustified']): ?>
                            <a href="submitJustification.php?attendanceId=<?php echo $row['Id']; ?>" class="btn btn-sm btn-primary">
                              <i class="fas fa-file-upload"></i> Justifier
                            </a>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>
                      </tr>
                      <?php
                        endwhile;
                      else:
                      ?>
                      <tr>
                        <td colspan="<?php echo $participationEnabled == '1' ? '8' : '7'; ?>" class="text-center">Aucune séance trouvée</td>
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

