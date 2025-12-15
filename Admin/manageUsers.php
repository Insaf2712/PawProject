
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = '';
$errorMsg = '';
$userType = isset($_GET['type']) ? $_GET['type'] : 'students';

// Réinitialiser un mot de passe
if(isset($_POST['resetPassword'])){
    $userId = $_POST['userId'];
    $userType = $_POST['userType'];
    $newPassword = md5('12345'); // Mot de passe par défaut
    
    $table = '';
    if($userType == 'student'){
        $table = 'tblstudents';
    } elseif($userType == 'teacher'){
        $table = 'tblclassteacher';
    } elseif($userType == 'admin'){
        $table = 'tbladmin';
    }
    
    if($table){
        $updateQuery = "UPDATE $table SET password = '$newPassword' WHERE Id = '$userId'";
        if(mysqli_query($conn, $updateQuery)){
            // Log
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_query($conn, "INSERT INTO tbllogs (userId, userType, action, details, ipAddress, userAgent, dateCreated) VALUES ('".$_SESSION['userId']."', 'admin', 'Réinitialisation mot de passe', 'Réinitialisation pour $userType ID: $userId', '$ip', '$userAgent', NOW())");
            
            $statusMsg = "<div class='alert alert-success'>Mot de passe réinitialisé avec succès (mot de passe par défaut: 12345)</div>";
        } else {
            $errorMsg = "Erreur lors de la réinitialisation.";
        }
    }
}

// Supprimer un utilisateur
if(isset($_GET['delete'])){
    $userId = $_GET['delete'];
    $userType = $_GET['type'];
    
    $table = '';
    if($userType == 'student'){
        $table = 'tblstudents';
    } elseif($userType == 'teacher'){
        $table = 'tblclassteacher';
    } elseif($userType == 'admin'){
        $table = 'tbladmin';
    }
    
    if($table){
        $deleteQuery = "DELETE FROM $table WHERE Id = '$userId'";
        if(mysqli_query($conn, $deleteQuery)){
            // Log
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_query($conn, "INSERT INTO tbllogs (userId, userType, action, details, ipAddress, userAgent, dateCreated) VALUES ('".$_SESSION['userId']."', 'admin', 'Suppression utilisateur', 'Suppression $userType ID: $userId', '$ip', '$userAgent', NOW())");
            
            $statusMsg = "<div class='alert alert-success'>Utilisateur supprimé avec succès!</div>";
        } else {
            $errorMsg = "Erreur lors de la suppression.";
        }
    }
}

// Récupérer les utilisateurs selon le type
$users = [];
if($userType == 'students'){
    $query = "SELECT s.*, c.className, ca.classArmName 
              FROM tblstudents s
              LEFT JOIN tblclass c ON c.Id = s.classId
              LEFT JOIN tblclassarms ca ON ca.Id = s.classArmId
              ORDER BY s.firstName ASC";
    $rs = $conn->query($query);
    while($row = $rs->fetch_assoc()){
        $users[] = $row;
    }
} elseif($userType == 'teachers'){
    $query = "SELECT t.*, c.className, ca.classArmName 
              FROM tblclassteacher t
              LEFT JOIN tblclass c ON c.Id = t.classId
              LEFT JOIN tblclassarms ca ON ca.Id = t.classArmId
              ORDER BY t.firstName ASC";
    $rs = $conn->query($query);
    while($row = $rs->fetch_assoc()){
        $users[] = $row;
    }
} elseif($userType == 'admins'){
    $query = "SELECT * FROM tbladmin ORDER BY firstName ASC";
    $rs = $conn->query($query);
    while($row = $rs->fetch_assoc()){
        $users[] = $row;
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
  <title>Gestion des Utilisateurs</title>
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
            <h1 class="h3 mb-0 text-gray-800">Gestion des Utilisateurs</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Gestion des Utilisateurs</li>
            </ol>
          </div>

          <?php echo $statusMsg; ?>
          <?php if($errorMsg): ?>
          <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
          <?php endif; ?>

          <div class="row mb-3">
            <div class="col-lg-12">
              <div class="card">
                <div class="card-header py-3">
                  <ul class="nav nav-tabs">
                    <li class="nav-item">
                      <a class="nav-link <?php echo $userType == 'students' ? 'active' : ''; ?>" href="?type=students">Étudiants</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link <?php echo $userType == 'teachers' ? 'active' : ''; ?>" href="?type=teachers">Professeurs</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link <?php echo $userType == 'admins' ? 'active' : ''; ?>" href="?type=admins">Administrateurs</a>
                    </li>
                  </ul>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <?php if($userType == 'students'): ?>
                      <a href="createStudents.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un Étudiant</a>
                    <?php elseif($userType == 'teachers'): ?>
                      <a href="createClassTeacher.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un Professeur</a>
                    <?php elseif($userType == 'admins'): ?>
                      <a href="createUsers.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un Administrateur</a>
                    <?php endif; ?>
                  </div>
                  
                  <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover" id="dataTable">
                      <thead class="thead-light">
                        <tr>
                          <th>#</th>
                          <th>Nom</th>
                          <th>Prénom</th>
                          <?php if($userType == 'students'): ?>
                          <th>N° Admission</th>
                          <th>Email</th>
                          <th>Classe</th>
                          <th>Groupe</th>
                          <?php elseif($userType == 'teachers'): ?>
                          <th>Email</th>
                          <th>Téléphone</th>
                          <th>Classe</th>
                          <th>Groupe</th>
                          <?php elseif($userType == 'admins'): ?>
                          <th>Email</th>
                          <?php endif; ?>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sn = 0;
                        foreach($users as $user):
                          $sn++;
                        ?>
                        <tr>
                          <td><?php echo $sn; ?></td>
                          <td><?php echo $user['lastName']; ?></td>
                          <td><?php echo $user['firstName']; ?></td>
                          <?php if($userType == 'students'): ?>
                          <td><?php echo $user['admissionNumber']; ?></td>
                          <td><?php echo $user['emailAddress'] ? $user['emailAddress'] : '-'; ?></td>
                          <td><?php echo $user['className'] ? $user['className'] : '-'; ?></td>
                          <td><?php echo $user['classArmName'] ? $user['classArmName'] : '-'; ?></td>
                          <?php elseif($userType == 'teachers'): ?>
                          <td><?php echo $user['emailAddress']; ?></td>
                          <td><?php echo $user['phoneNo']; ?></td>
                          <td><?php echo $user['className'] ? $user['className'] : '-'; ?></td>
                          <td><?php echo $user['classArmName'] ? $user['classArmName'] : '-'; ?></td>
                          <?php elseif($userType == 'admins'): ?>
                          <td><?php echo $user['emailAddress']; ?></td>
                          <?php endif; ?>
                          <td>
                            <div class="btn-group">
                              <?php if($userType == 'students'): ?>
                                <a href="createStudents.php?edit=<?php echo $user['Id']; ?>" class="btn btn-sm btn-info">
                                  <i class="fas fa-edit"></i> Modifier
                                </a>
                              <?php elseif($userType == 'teachers'): ?>
                                <a href="createClassTeacher.php?edit=<?php echo $user['Id']; ?>" class="btn btn-sm btn-info">
                                  <i class="fas fa-edit"></i> Modifier
                                </a>
                              <?php elseif($userType == 'admins'): ?>
                                <a href="createUsers.php?edit=<?php echo $user['Id']; ?>" class="btn btn-sm btn-info">
                                  <i class="fas fa-edit"></i> Modifier
                                </a>
                              <?php endif; ?>
                              
                              <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#resetModal<?php echo $user['Id']; ?>">
                                <i class="fas fa-key"></i> Réinitialiser
                              </button>
                              
                              <a href="?delete=<?php echo $user['Id']; ?>&type=<?php echo $userType; ?>" 
                                 class="btn btn-sm btn-danger" 
                                 onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?');">
                                <i class="fas fa-trash"></i> Supprimer
                              </a>
                            </div>
                            
                            <!-- Modal Réinitialisation -->
                            <div class="modal fade" id="resetModal<?php echo $user['Id']; ?>" tabindex="-1">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title">Réinitialiser le Mot de Passe</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                      <span>&times;</span>
                                    </button>
                                  </div>
                                  <form method="post">
                                    <div class="modal-body">
                                      <input type="hidden" name="userId" value="<?php echo $user['Id']; ?>">
                                      <input type="hidden" name="userType" value="<?php echo $userType == 'students' ? 'student' : ($userType == 'teachers' ? 'teacher' : 'admin'); ?>">
                                      <p>Le mot de passe sera réinitialisé à: <strong>12345</strong></p>
                                      <p>Utilisateur: <?php echo $user['firstName'] . ' ' . $user['lastName']; ?></p>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                      <button type="submit" name="resetPassword" class="btn btn-warning">Réinitialiser</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </td>
                        </tr>
                        <?php endforeach; ?>
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
      $('#dataTable').DataTable();
    });
  </script>
</body>

</html>

