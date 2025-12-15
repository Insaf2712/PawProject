
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

if(!isset($_SESSION['userType']) || $_SESSION['userType'] != 'Student'){
    header("Location: ../index.php");
    exit();
}

$admissionNo = $_SESSION['admissionNumber'];

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../img/logo/attnlg.jpg" rel="icon">
  <title>Mes Justificatifs</title>
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
            <h1 class="h3 mb-0 text-gray-800">Mes Justificatifs</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Mes Justificatifs</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Liste des Justificatifs</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Date d'absence</th>
                        <th>Motif</th>
                        <th>Fichier</th>
                        <th>Statut</th>
                        <th>Commentaire du professeur</th>
                        <th>Date de soumission</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT j.*, a.dateTimeTaken, c.courseName, c.courseCode
                               FROM tbljustifications j
                               LEFT JOIN tblattendance a ON a.Id = j.attendanceId
                               LEFT JOIN tblteachercourses tc ON tc.classId = j.classId AND tc.classArmId = j.classArmId
                               LEFT JOIN tblcourses c ON c.Id = tc.courseId
                               WHERE j.admissionNo = '$admissionNo'
                               ORDER BY j.dateCreated DESC";
                      $rs = $conn->query($query);
                      $sn = 0;
                      
                      if($rs->num_rows > 0):
                        while($row = $rs->fetch_assoc()):
                          $sn++;
                          $statusClass = '';
                          $statusText = '';
                          switch($row['status']){
                            case 'pending':
                              $statusClass = 'warning';
                              $statusText = 'En attente';
                              break;
                            case 'accepted':
                              $statusClass = 'success';
                              $statusText = 'Accepté';
                              break;
                            case 'refused':
                              $statusClass = 'danger';
                              $statusText = 'Refusé';
                              break;
                          }
                      ?>
                      <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['justificationDate'])); ?></td>
                        <td><?php echo $row['motif']; ?></td>
                        <td>
                          <?php if($row['filePath']): ?>
                            <a href="../<?php echo $row['filePath']; ?>" target="_blank" class="btn btn-sm btn-info">
                              <i class="fas fa-download"></i> Télécharger
                            </a>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </td>
                        <td><?php echo $row['reviewComment'] ? $row['reviewComment'] : '-'; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['dateCreated'])); ?></td>
                      </tr>
                      <?php
                        endwhile;
                      else:
                      ?>
                      <tr>
                        <td colspan="7" class="text-center">Aucun justificatif soumis</td>
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

