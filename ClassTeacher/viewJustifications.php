
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = '';
$errorMsg = '';

// Traiter la validation/refus d'un justificatif
if(isset($_POST['reviewJustification'])){
    $justificationId = $_POST['justificationId'];
    $decision = $_POST['decision'];
    $reviewComment = mysqli_real_escape_string($conn, $_POST['reviewComment']);
    
    $updateQuery = "UPDATE tbljustifications 
                   SET status = '$decision', reviewedBy = '".$_SESSION['userId']."', 
                       reviewComment = '$reviewComment', reviewDate = NOW() 
                   WHERE Id = '$justificationId'";
    
    if(mysqli_query($conn, $updateQuery)){
        // Mettre à jour la présence si accepté
        if($decision == 'accepted'){
            $justQuery = "SELECT attendanceId FROM tbljustifications WHERE Id = '$justificationId'";
            $justRs = $conn->query($justQuery);
            if($justRs->num_rows > 0){
                $justData = $justRs->fetch_assoc();
                mysqli_query($conn, "UPDATE tblattendance SET isJustified = 1, justificationId = '$justificationId' WHERE Id = '".$justData['attendanceId']."'");
            }
        }
        
        // Créer une notification pour l'étudiant
        $justQuery = "SELECT admissionNo FROM tbljustifications WHERE Id = '$justificationId'";
        $justRs = $conn->query($justQuery);
        if($justRs->num_rows > 0){
            $justData = $justRs->fetch_assoc();
            $studentQuery = "SELECT Id FROM tblstudents WHERE admissionNumber = '".$justData['admissionNo']."'";
            $studentRs = $conn->query($studentQuery);
            if($studentRs->num_rows > 0){
                $studentId = $studentRs->fetch_assoc()['Id'];
                $decisionText = $decision == 'accepted' ? 'accepté' : 'refusé';
                mysqli_query($conn, "INSERT INTO tblnotifications 
                                   (userId, userType, type, title, message, isRead, dateCreated, link) 
                                   VALUES 
                                   ('$studentId', 'student', 'justification', 
                                    'Justificatif $decisionText', 
                                    'Votre justificatif a été $decisionText. ".($reviewComment ? "Commentaire: $reviewComment" : "")."', 
                                    0, NOW(), 'Student/viewJustifications.php')");
            }
        }
        
        $statusMsg = "<div class='alert alert-success'>Décision enregistrée avec succès!</div>";
    } else {
        $errorMsg = "Erreur lors de l'enregistrement de la décision.";
    }
}

// Récupérer les justificatifs
$justificationsQuery = "SELECT j.*, s.firstName, s.lastName, s.admissionNumber, 
                        a.dateTimeTaken, c.courseName, c.courseCode
                        FROM tbljustifications j
                        INNER JOIN tblstudents s ON s.admissionNumber = j.admissionNo
                        LEFT JOIN tblattendance a ON a.Id = j.attendanceId
                        LEFT JOIN tblteachercourses tc ON tc.classId = j.classId AND tc.classArmId = j.classArmId
                        LEFT JOIN tblcourses c ON c.Id = tc.courseId
                        WHERE j.classId = '".$_SESSION['classId']."' AND j.classArmId = '".$_SESSION['classArmId']."'
                        ORDER BY j.dateCreated DESC";
$justificationsRs = $conn->query($justificationsQuery);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Gestion des Justificatifs</title>
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
            <h1 class="h3 mb-0 text-gray-800">Gestion des Justificatifs</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Justificatifs</li>
            </ol>
          </div>

          <?php echo $statusMsg; ?>
          <?php if($errorMsg): ?>
          <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
          <?php endif; ?>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Justificatifs Reçus</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Étudiant</th>
                        <th>Date d'absence</th>
                        <th>Motif</th>
                        <th>Fichier</th>
                        <th>Statut</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if($justificationsRs->num_rows > 0):
                        $sn = 0;
                        while($just = $justificationsRs->fetch_assoc()):
                          $sn++;
                          $statusClass = '';
                          $statusText = '';
                          switch($just['status']){
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
                        <td><?php echo $just['firstName'] . ' ' . $just['lastName'] . ' (' . $just['admissionNumber'] . ')'; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($just['justificationDate'])); ?></td>
                        <td><?php echo substr($just['motif'], 0, 50) . (strlen($just['motif']) > 50 ? '...' : ''); ?></td>
                        <td>
                          <?php if($just['filePath']): ?>
                            <a href="../<?php echo $just['filePath']; ?>" target="_blank" class="btn btn-sm btn-info">
                              <i class="fas fa-download"></i> Voir
                            </a>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </td>
                        <td>
                          <?php if($just['status'] == 'pending'): ?>
                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#reviewModal<?php echo $just['Id']; ?>">
                              <i class="fas fa-check"></i> Examiner
                            </button>
                          <?php else: ?>
                            <small class="text-muted"><?php echo $just['reviewComment'] ? 'Commentaire: ' . $just['reviewComment'] : ''; ?></small>
                          <?php endif; ?>
                        </td>
                      </tr>
                      
                      <!-- Modal pour examiner le justificatif -->
                      <?php if($just['status'] == 'pending'): ?>
                      <div class="modal fade" id="reviewModal<?php echo $just['Id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Examiner le Justificatif</h5>
                              <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                              </button>
                            </div>
                            <form method="post">
                              <div class="modal-body">
                                <input type="hidden" name="justificationId" value="<?php echo $just['Id']; ?>">
                                
                                <div class="form-group">
                                  <label><strong>Étudiant:</strong></label>
                                  <p><?php echo $just['firstName'] . ' ' . $just['lastName'] . ' (' . $just['admissionNumber'] . ')'; ?></p>
                                </div>
                                
                                <div class="form-group">
                                  <label><strong>Date d'absence:</strong></label>
                                  <p><?php echo date('d/m/Y', strtotime($just['justificationDate'])); ?></p>
                                </div>
                                
                                <div class="form-group">
                                  <label><strong>Motif:</strong></label>
                                  <p><?php echo $just['motif']; ?></p>
                                </div>
                                
                                <?php if($just['comment']): ?>
                                <div class="form-group">
                                  <label><strong>Commentaire:</strong></label>
                                  <p><?php echo $just['comment']; ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                  <label>Décision<span class="text-danger ml-2">*</span></label>
                                  <select name="decision" class="form-control" required>
                                    <option value="accepted">Accepter</option>
                                    <option value="refused">Refuser</option>
                                  </select>
                                </div>
                                
                                <div class="form-group">
                                  <label>Commentaire de décision</label>
                                  <textarea name="reviewComment" class="form-control" rows="3" placeholder="Commentaire pour l'étudiant..."></textarea>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="submit" name="reviewJustification" class="btn btn-primary">Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                      <?php endif; ?>
                      <?php
                        endwhile;
                      else:
                      ?>
                      <tr>
                        <td colspan="7" class="text-center">Aucun justificatif reçu</td>
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

