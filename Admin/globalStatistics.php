
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Filtres
$yearFilter = isset($_GET['year']) ? $_GET['year'] : '';
$semesterFilter = isset($_GET['semester']) ? $_GET['semester'] : '';
$classFilter = isset($_GET['classId']) ? $_GET['classId'] : '';
$groupFilter = isset($_GET['classArmId']) ? $_GET['classArmId'] : '';
$teacherFilter = isset($_GET['teacherId']) ? $_GET['teacherId'] : '';

// Construire la requête de base
$whereClause = "WHERE 1=1";

if($classFilter){
    $whereClause .= " AND a.classId = '$classFilter'";
}

if($groupFilter){
    $whereClause .= " AND a.classArmId = '$groupFilter'";
}

// Statistiques globales
$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblstudents"))['total'];
$totalTeachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblclassteacher"))['total'];
$totalClasses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblclass"))['total'];

$totalAttendanceQuery = "SELECT COUNT(*) as total FROM tblattendance $whereClause";
$totalAttendance = mysqli_fetch_assoc(mysqli_query($conn, $totalAttendanceQuery))['total'];

$presentQuery = "SELECT COUNT(*) as total FROM tblattendance $whereClause AND statusType = 'present'";
$totalPresent = mysqli_fetch_assoc(mysqli_query($conn, $presentQuery))['total'];

$absentQuery = "SELECT COUNT(*) as total FROM tblattendance $whereClause AND statusType = 'absent'";
$totalAbsent = mysqli_fetch_assoc(mysqli_query($conn, $absentQuery))['total'];

$lateQuery = "SELECT COUNT(*) as total FROM tblattendance $whereClause AND statusType = 'late'";
$totalLate = mysqli_fetch_assoc(mysqli_query($conn, $lateQuery))['total'];

$globalRate = ($totalPresent + $totalLate) > 0 ? round((($totalPresent + $totalLate) / ($totalPresent + $totalAbsent + $totalLate)) * 100, 2) : 0;

// Statistiques par classe
$classStatsQuery = "SELECT c.className, ca.classArmName,
                    COUNT(DISTINCT a.admissionNo) as students,
                    COUNT(a.Id) as totalSessions,
                    SUM(CASE WHEN a.statusType = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN a.statusType = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN a.statusType = 'late' THEN 1 ELSE 0 END) as late
                    FROM tblclass c
                    INNER JOIN tblclassarms ca ON ca.classId = c.Id
                    LEFT JOIN tblattendance a ON a.classId = c.Id AND a.classArmId = ca.Id
                    GROUP BY c.Id, ca.Id
                    ORDER BY c.className, ca.classArmName";
$classStatsRs = $conn->query($classStatsQuery);

// Récupérer les listes pour les filtres
$classesRs = $conn->query("SELECT * FROM tblclass ORDER BY className");
$teachersRs = $conn->query("SELECT * FROM tblclassteacher ORDER BY firstName");

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Statistiques Globales</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php";?>
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Statistiques Globales</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Statistiques Globales</li>
            </ol>
          </div>

          <!-- Filtres -->
          <div class="row mb-3">
            <div class="col-lg-12">
              <div class="card">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
                </div>
                <div class="card-body">
                  <form method="get" class="row">
                    <div class="col-md-3">
                      <label>Classe</label>
                      <select name="classId" class="form-control">
                        <option value="">Toutes les classes</option>
                        <?php while($class = $classesRs->fetch_assoc()): ?>
                        <option value="<?php echo $class['Id']; ?>" <?php echo $classFilter == $class['Id'] ? 'selected' : ''; ?>>
                          <?php echo $class['className']; ?>
                        </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Professeur</label>
                      <select name="teacherId" class="form-control">
                        <option value="">Tous les professeurs</option>
                        <?php while($teacher = $teachersRs->fetch_assoc()): ?>
                        <option value="<?php echo $teacher['Id']; ?>" <?php echo $teacherFilter == $teacher['Id'] ? 'selected' : ''; ?>>
                          <?php echo $teacher['firstName'] . ' ' . $teacher['lastName']; ?>
                        </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>&nbsp;</label><br>
                      <button type="submit" class="btn btn-primary">Appliquer les Filtres</button>
                      <a href="globalStatistics.php" class="btn btn-secondary">Réinitialiser</a>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistiques Globales -->
          <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Taux d'Assiduité Global</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $globalRate; ?>%</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chart-line fa-2x text-primary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Étudiants</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalStudents; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Professeurs</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalTeachers; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Présences</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPresent; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistiques par Classe -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Statistiques par Classe/Groupe</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>Classe</th>
                        <th>Groupe</th>
                        <th>Étudiants</th>
                        <th>Séances</th>
                        <th>Présences</th>
                        <th>Absences</th>
                        <th>Retards</th>
                        <th>Taux</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      while($stat = $classStatsRs->fetch_assoc()):
                        $rate = $stat['totalSessions'] > 0 ? round((($stat['present'] + $stat['late']) / $stat['totalSessions']) * 100, 2) : 0;
                      ?>
                      <tr>
                        <td><?php echo $stat['className']; ?></td>
                        <td><?php echo $stat['classArmName']; ?></td>
                        <td><?php echo $stat['students']; ?></td>
                        <td><?php echo $stat['totalSessions']; ?></td>
                        <td class="text-success"><?php echo $stat['present']; ?></td>
                        <td class="text-danger"><?php echo $stat['absent']; ?></td>
                        <td class="text-warning"><?php echo $stat['late']; ?></td>
                        <td>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?php echo $rate >= 80 ? 'bg-success' : ($rate >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 role="progressbar" style="width: <?php echo $rate; ?>%">
                              <?php echo $rate; ?>%
                            </div>
                          </div>
                        </td>
                      </tr>
                      <?php endwhile; ?>
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

