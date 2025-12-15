
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = '';
$errorMsg = '';

// Traitement de l'import Excel
if(isset($_POST['importExcel'])){
    if(isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == 0){
        $file = $_FILES['excelFile']['tmp_name'];
        $fileName = $_FILES['excelFile']['name'];
        
        // Vérifier l'extension
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        if($ext != 'xls' && $ext != 'xlsx' && $ext != 'csv'){
            $errorMsg = "Format de fichier non supporté. Formats acceptés: XLS, XLSX, CSV";
        } else {
            // Lire le fichier CSV (simplifié - pour Excel complet, utiliser PhpSpreadsheet)
            if($ext == 'csv'){
                $handle = fopen($file, 'r');
                $line = 0;
                $errors = [];
                $success = 0;
                
                while(($data = fgetcsv($handle, 1000, ',')) !== FALSE){
                    $line++;
                    if($line == 1) continue; // Ignorer l'en-tête
                    
                    if(count($data) >= 6){
                        $firstName = trim($data[0]);
                        $lastName = trim($data[1]);
                        $otherName = isset($data[2]) ? trim($data[2]) : '';
                        $admissionNumber = trim($data[3]);
                        $classId = trim($data[4]);
                        $classArmId = trim($data[5]);
                        $emailAddress = isset($data[6]) ? trim($data[6]) : '';
                        
                        // Vérifier les doublons
                        $checkQuery = "SELECT Id FROM tblstudents WHERE admissionNumber = '$admissionNumber'";
                        $checkRs = $conn->query($checkQuery);
                        
                        if($checkRs->num_rows > 0){
                            $errors[] = "Ligne $line: Numéro d'admission $admissionNumber existe déjà";
                        } else {
                            $password = md5('12345'); // Mot de passe par défaut
                            $dateCreated = date('Y-m-d');
                            
                            $insertQuery = "INSERT INTO tblstudents (firstName, lastName, otherName, admissionNumber, emailAddress, password, classId, classArmId, dateCreated) 
                                           VALUES ('$firstName', '$lastName', '$otherName', '$admissionNumber', '$emailAddress', '$password', '$classId', '$classArmId', '$dateCreated')";
                            
                            if(mysqli_query($conn, $insertQuery)){
                                $success++;
                            } else {
                                $errors[] = "Ligne $line: Erreur - " . mysqli_error($conn);
                            }
                        }
                    } else {
                        $errors[] = "Ligne $line: Données incomplètes";
                    }
                }
                fclose($handle);
                
                if($success > 0){
                    $statusMsg = "<div class='alert alert-success'>$success étudiant(s) importé(s) avec succès!</div>";
                }
                if(count($errors) > 0){
                    $errorMsg = "<div class='alert alert-warning'><strong>Erreurs détectées:</strong><ul>";
                    foreach($errors as $error){
                        $errorMsg .= "<li>$error</li>";
                    }
                    $errorMsg .= "</ul></div>";
                }
            } else {
                $errorMsg = "Pour les fichiers Excel (XLS/XLSX), veuillez d'abord les convertir en CSV ou installer PhpSpreadsheet.";
            }
        }
    } else {
        $errorMsg = "Erreur lors de l'upload du fichier.";
    }
}

// Export Excel
if(isset($_GET['export'])){
    $type = $_GET['export'];
    $filename = '';
    $query = '';
    
    if($type == 'students'){
        $filename = "etudiants_" . date('Y-m-d') . ".xls";
        $query = "SELECT s.*, c.className, ca.classArmName 
                  FROM tblstudents s
                  LEFT JOIN tblclass c ON c.Id = s.classId
                  LEFT JOIN tblclassarms ca ON ca.Id = s.classArmId
                  ORDER BY s.firstName ASC";
    } elseif($type == 'attendance'){
        $filename = "presences_" . date('Y-m-d') . ".xls";
        $query = "SELECT a.*, s.firstName, s.lastName, s.admissionNumber, c.className, ca.classArmName
                  FROM tblattendance a
                  INNER JOIN tblstudents s ON s.admissionNumber = a.admissionNo
                  INNER JOIN tblclass c ON c.Id = a.classId
                  INNER JOIN tblclassarms ca ON ca.Id = a.classArmId
                  ORDER BY a.dateTimeTaken DESC";
    } elseif($type == 'statistics'){
        $filename = "statistiques_" . date('Y-m-d') . ".xls";
        $query = "SELECT s.admissionNumber, s.firstName, s.lastName,
                  COUNT(a.Id) as totalSessions,
                  SUM(CASE WHEN a.statusType = 'present' THEN 1 ELSE 0 END) as presentCount,
                  SUM(CASE WHEN a.statusType = 'absent' THEN 1 ELSE 0 END) as absentCount,
                  SUM(CASE WHEN a.statusType = 'late' THEN 1 ELSE 0 END) as lateCount
                  FROM tblstudents s
                  LEFT JOIN tblattendance a ON a.admissionNo = s.admissionNumber
                  GROUP BY s.Id
                  ORDER BY s.firstName ASC";
    }
    
    if($query){
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        
        $rs = $conn->query($query);
        
        if($type == 'students'){
            echo "Nom\tPrénom\tAutre Nom\tN° Admission\tEmail\tClasse\tGroupe\tDate Création\n";
            while($row = $rs->fetch_assoc()){
                echo $row['lastName'] . "\t" . $row['firstName'] . "\t" . $row['otherName'] . "\t" . 
                     $row['admissionNumber'] . "\t" . ($row['emailAddress'] ? $row['emailAddress'] : '') . "\t" .
                     ($row['className'] ? $row['className'] : '') . "\t" . ($row['classArmName'] ? $row['classArmName'] : '') . "\t" .
                     $row['dateCreated'] . "\n";
            }
        } elseif($type == 'attendance'){
            echo "Date\tNom\tPrénom\tN° Admission\tClasse\tGroupe\tStatut\tCommentaire\n";
            while($row = $rs->fetch_assoc()){
                $statusText = '';
                switch($row['statusType']){
                    case 'present': $statusText = 'Présent'; break;
                    case 'absent': $statusText = 'Absent'; break;
                    case 'late': $statusText = 'Retard'; break;
                }
                echo date('d/m/Y', strtotime($row['dateTimeTaken'])) . "\t" . 
                     $row['lastName'] . "\t" . $row['firstName'] . "\t" . $row['admissionNumber'] . "\t" .
                     ($row['className'] ? $row['className'] : '') . "\t" . ($row['classArmName'] ? $row['classArmName'] : '') . "\t" .
                     $statusText . "\t" . ($row['comment'] ? $row['comment'] : '') . "\n";
            }
        } elseif($type == 'statistics'){
            echo "N° Admission\tNom\tPrénom\tSéances\tPrésences\tAbsences\tRetards\tTaux\n";
            while($row = $rs->fetch_assoc()){
                $rate = $row['totalSessions'] > 0 ? round((($row['presentCount'] + $row['lateCount']) / $row['totalSessions']) * 100, 2) : 0;
                echo $row['admissionNumber'] . "\t" . $row['lastName'] . "\t" . $row['firstName'] . "\t" .
                     $row['totalSessions'] . "\t" . $row['presentCount'] . "\t" . $row['absentCount'] . "\t" .
                     $row['lateCount'] . "\t" . $rate . "%\n";
            }
        }
        
        exit();
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
  <title>Import/Export</title>
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
            <h1 class="h3 mb-0 text-gray-800">Import/Export</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Import/Export</li>
            </ol>
          </div>

          <?php echo $statusMsg; ?>
          <?php echo $errorMsg; ?>

          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Importer des Étudiants (CSV)</h6>
                </div>
                <div class="card-body">
                  <p><strong>Format CSV requis:</strong></p>
                  <p>Nom, Prénom, Autre Nom, N° Admission, Classe ID, Groupe ID, Email (optionnel)</p>
                  <p><small class="text-muted">La première ligne doit contenir les en-têtes. Le mot de passe par défaut sera: 12345</small></p>
                  
                  <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label>Sélectionner le fichier CSV</label>
                      <input type="file" name="excelFile" class="form-control" accept=".csv,.xls,.xlsx" required>
                    </div>
                    <button type="submit" name="importExcel" class="btn btn-primary">
                      <i class="fas fa-upload"></i> Importer
                    </button>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Exporter</h6>
                </div>
                <div class="card-body">
                  <p>Sélectionnez le type de données à exporter:</p>
                  <div class="list-group">
                    <a href="?export=students" class="list-group-item list-group-item-action">
                      <i class="fas fa-file-excel"></i> Exporter la Liste des Étudiants
                    </a>
                    <a href="?export=attendance" class="list-group-item list-group-item-action">
                      <i class="fas fa-file-excel"></i> Exporter les Présences
                    </a>
                    <a href="?export=statistics" class="list-group-item list-group-item-action">
                      <i class="fas fa-file-excel"></i> Exporter les Statistiques
                    </a>
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
</body>

</html>

