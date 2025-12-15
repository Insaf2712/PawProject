
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$classId = $_SESSION['classId'];
$classArmId = $_SESSION['classArmId'];

// Vérifier si la participation est activée
$participationEnabled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT settingValue FROM tblsystemsettings WHERE settingKey = 'participation_enabled'"))['settingValue'];

// Statistiques globales de la classe
$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblstudents WHERE classId = '$classId' AND classArmId = '$classArmId'"))['total'];

$totalSessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT dateTimeTaken) as total FROM tblattendance WHERE classId = '$classId' AND classArmId = '$classArmId'"))['total'];

$totalPresent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblattendance WHERE classId = '$classId' AND classArmId = '$classArmId' AND statusType = 'present'"))['total'];
$totalAbsent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblattendance WHERE classId = '$classId' AND classArmId = '$classArmId' AND statusType = 'absent'"))['total'];
$totalLate = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tblattendance WHERE classId = '$classId' AND classArmId = '$classArmId' AND statusType = 'late'"))['total'];

$attendanceRate = ($totalPresent + $totalLate) > 0 ? round((($totalPresent + $totalLate) / ($totalPresent + $totalAbsent + $totalLate)) * 100, 2) : 0;

// Statistiques par étudiant
$studentsStatsQuery = "SELECT s.admissionNumber, s.firstName, s.lastName,
                       COUNT(a.Id) as totalSessions,
                       SUM(CASE WHEN a.statusType = 'present' THEN 1 ELSE 0 END) as presentCount,
                       SUM(CASE WHEN a.statusType = 'absent' THEN 1 ELSE 0 END) as absentCount,
                       SUM(CASE WHEN a.statusType = 'late' THEN 1 ELSE 0 END) as lateCount,
                       AVG(CASE WHEN a.participationScore IS NOT NULL THEN a.participationScore ELSE NULL END) as avgParticipation
                       FROM tblstudents s
                       LEFT JOIN tblattendance a ON a.admissionNo = s.admissionNumber AND a.classId = s.classId AND a.classArmId = s.classArmId
                       WHERE s.classId = '$classId' AND s.classArmId = '$classArmId'
                       GROUP BY s.Id, s.admissionNumber, s.firstName, s.lastName
                       ORDER BY s.firstName ASC";
$studentsStatsRs = $conn->query($studentsStatsQuery);

// Statistiques par date (pour graphique)
$dailyStatsQuery = "SELECT dateTimeTaken,
                    COUNT(*) as total,
                    SUM(CASE WHEN statusType = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN statusType = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN statusType = 'late' THEN 1 ELSE 0 END) as late
                    FROM tblattendance
                    WHERE classId = '$classId' AND classArmId = '$classArmId'
                    GROUP BY dateTimeTaken
                    ORDER BY dateTimeTaken DESC
                    LIMIT 30";
$dailyStatsRs = $conn->query($dailyStatsQuery);

$dailyLabels = [];
$dailyPresent = [];
$dailyAbsent = [];
$dailyLate = [];

while($row = $dailyStatsRs->fetch_assoc()){
    $dailyLabels[] = date('d/m', strtotime($row['dateTimeTaken']));
    $dailyPresent[] = $row['present'];
    $dailyAbsent[] = $row['absent'];
    $dailyLate[] = $row['late'];
}

$dailyLabels = array_reverse($dailyLabels);
$dailyPresent = array_reverse($dailyPresent);
$dailyAbsent = array_reverse($dailyAbsent);
$dailyLate = array_reverse($dailyLate);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Statistiques</title>
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
            <h1 class="h3 mb-0 text-gray-800">Statistiques</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Statistiques</li>
            </ol>
          </div>

          <!-- Statistiques Globales -->
          <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Taux d'Assiduité Global</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $attendanceRate; ?>%</div>
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
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Présences</div>
                      <div class="h5 mb-0 font-weight-bold text-success"><?php echo $totalPresent; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-check-circle fa-2x text-success"></i>
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
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Absences</div>
                      <div class="h5 mb-0 font-weight-bold text-danger"><?php echo $totalAbsent; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-times-circle fa-2x text-danger"></i>
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
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Retards</div>
                      <div class="h5 mb-0 font-weight-bold text-warning"><?php echo $totalLate; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Graphiques -->
          <div class="row">
            <div class="col-lg-8">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Évolution des Présences (30 derniers jours)</h6>
                </div>
                <div class="card-body">
                  <canvas id="attendanceChart" height="100"></canvas>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Répartition Globale</h6>
                </div>
                <div class="card-body">
                  <canvas id="pieChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistiques par Étudiant -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Statistiques par Étudiant</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Étudiant</th>
                        <th>N° Admission</th>
                        <th>Séances</th>
                        <th>Présences</th>
                        <th>Absences</th>
                        <th>Retards</th>
                        <th>Taux</th>
                        <?php if($participationEnabled == '1'): ?>
                        <th>Participation Moy.</th>
                        <?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $sn = 0;
                      if($studentsStatsRs->num_rows > 0):
                        while($student = $studentsStatsRs->fetch_assoc()):
                          $sn++;
                          $studentRate = $student['totalSessions'] > 0 ? round((($student['presentCount'] + $student['lateCount']) / $student['totalSessions']) * 100, 2) : 0;
                      ?>
                      <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo $student['firstName'] . ' ' . $student['lastName']; ?></td>
                        <td><?php echo $student['admissionNumber']; ?></td>
                        <td><?php echo $student['totalSessions']; ?></td>
                        <td class="text-success"><?php echo $student['presentCount']; ?></td>
                        <td class="text-danger"><?php echo $student['absentCount']; ?></td>
                        <td class="text-warning"><?php echo $student['lateCount']; ?></td>
                        <td>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar <?php echo $studentRate >= 80 ? 'bg-success' : ($studentRate >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                 role="progressbar" style="width: <?php echo $studentRate; ?>%">
                              <?php echo $studentRate; ?>%
                            </div>
                          </div>
                        </td>
                        <?php if($participationEnabled == '1'): ?>
                        <td><?php echo $student['avgParticipation'] ? number_format($student['avgParticipation'], 2) . '/2' : '-'; ?></td>
                        <?php endif; ?>
                      </tr>
                      <?php
                        endwhile;
                      endif;
                      ?>
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

    // Graphique d'évolution
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($dailyLabels); ?>,
        datasets: [{
          label: 'Présents',
          data: <?php echo json_encode($dailyPresent); ?>,
          borderColor: 'rgb(40, 167, 69)',
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          tension: 0.1
        }, {
          label: 'Absents',
          data: <?php echo json_encode($dailyAbsent); ?>,
          borderColor: 'rgb(220, 53, 69)',
          backgroundColor: 'rgba(220, 53, 69, 0.1)',
          tension: 0.1
        }, {
          label: 'Retards',
          data: <?php echo json_encode($dailyLate); ?>,
          borderColor: 'rgb(255, 193, 7)',
          backgroundColor: 'rgba(255, 193, 7, 0.1)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Graphique circulaire
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
      type: 'doughnut',
      data: {
        labels: ['Présents', 'Absents', 'Retards'],
        datasets: [{
          data: [<?php echo $totalPresent; ?>, <?php echo $totalAbsent; ?>, <?php echo $totalLate; ?>],
          backgroundColor: [
            'rgb(40, 167, 69)',
            'rgb(220, 53, 69)',
            'rgb(255, 193, 7)'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  </script>
</body>

</html>

