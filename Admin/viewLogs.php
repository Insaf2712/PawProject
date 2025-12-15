
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$userTypeFilter = isset($_GET['userType']) ? $_GET['userType'] : '';
$actionFilter = isset($_GET['action']) ? $_GET['action'] : '';

// Construire la requête
$query = "SELECT l.*, 
          CASE 
            WHEN l.userType = 'admin' THEN (SELECT CONCAT(firstName, ' ', lastName) FROM tbladmin WHERE Id = l.userId)
            WHEN l.userType = 'teacher' THEN (SELECT CONCAT(firstName, ' ', lastName) FROM tblclassteacher WHERE Id = l.userId)
            WHEN l.userType = 'student' THEN (SELECT CONCAT(firstName, ' ', lastName) FROM tblstudents WHERE Id = l.userId)
          END as userName
          FROM tbllogs l
          WHERE 1=1";

if($userTypeFilter){
    $query .= " AND l.userType = '$userTypeFilter'";
}

if($actionFilter){
    $query .= " AND l.action LIKE '%$actionFilter%'";
}

$query .= " ORDER BY l.dateCreated DESC LIMIT 1000";

$logsRs = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Logs et Audit</title>
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
            <h1 class="h3 mb-0 text-gray-800">Logs et Audit</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Logs et Audit</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Historique des Actions</h6>
                </div>
                <div class="card-body">
                  <form method="get" class="mb-3">
                    <div class="row">
                      <div class="col-md-4">
                        <select name="userType" class="form-control">
                          <option value="">Tous les types d'utilisateurs</option>
                          <option value="admin" <?php echo $userTypeFilter == 'admin' ? 'selected' : ''; ?>>Administrateurs</option>
                          <option value="teacher" <?php echo $userTypeFilter == 'teacher' ? 'selected' : ''; ?>>Professeurs</option>
                          <option value="student" <?php echo $userTypeFilter == 'student' ? 'selected' : ''; ?>>Étudiants</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <input type="text" name="action" class="form-control" placeholder="Rechercher une action..." value="<?php echo $actionFilter; ?>">
                      </div>
                      <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="viewLogs.php" class="btn btn-secondary">Réinitialiser</a>
                      </div>
                    </div>
                  </form>
                  
                  <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover" id="dataTable">
                      <thead class="thead-light">
                        <tr>
                          <th>#</th>
                          <th>Date/Heure</th>
                          <th>Utilisateur</th>
                          <th>Type</th>
                          <th>Action</th>
                          <th>Détails</th>
                          <th>Adresse IP</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sn = 0;
                        if($logsRs->num_rows > 0):
                          while($log = $logsRs->fetch_assoc()):
                            $sn++;
                            $userTypeLabel = '';
                            $userTypeClass = '';
                            switch($log['userType']){
                              case 'admin':
                                $userTypeLabel = 'Admin';
                                $userTypeClass = 'danger';
                                break;
                              case 'teacher':
                                $userTypeLabel = 'Professeur';
                                $userTypeClass = 'primary';
                                break;
                              case 'student':
                                $userTypeLabel = 'Étudiant';
                                $userTypeClass = 'info';
                                break;
                            }
                        ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td><?php echo date('d/m/Y H:i:s', strtotime($log['dateCreated'])); ?></td>
                          <td><?php echo $log['userName'] ? $log['userName'] : 'ID: ' . $log['userId']; ?></td>
                          <td><span class="badge badge-<?php echo $userTypeClass; ?>"><?php echo $userTypeLabel; ?></span></td>
                          <td><?php echo $log['action']; ?></td>
                          <td><?php echo $log['details'] ? $log['details'] : '-'; ?></td>
                          <td><?php echo $log['ipAddress']; ?></td>
                        </tr>
                        <?php
                          endwhile;
                        else:
                        ?>
                        <tr>
                          <td colspan="7" class="text-center">Aucun log trouvé</td>
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
      $('#dataTable').DataTable({
        order: [[0, 'desc']]
      });
    });
  </script>
</body>

</html>

