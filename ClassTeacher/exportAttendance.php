
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

$classId = $_SESSION['classId'];
$classArmId = $_SESSION['classArmId'];
$exportType = isset($_GET['type']) ? $_GET['type'] : 'excel';
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : date('Y-m-01');
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : date('Y-m-d');

// Récupérer les données
$query = "SELECT a.*, s.firstName, s.lastName, s.admissionNumber,
          c.className, ca.classArmName, st.sessionName, t.termName,
          ses.sessionName as sessionName2, ses.sessionDate, ses.sessionTime
          FROM tblattendance a
          INNER JOIN tblstudents s ON s.admissionNumber = a.admissionNo
          INNER JOIN tblclass c ON c.Id = a.classId
          INNER JOIN tblclassarms ca ON ca.Id = a.classArmId
          INNER JOIN tblsessionterm st ON st.Id = a.sessionTermId
          INNER JOIN tblterm t ON t.Id = st.termId
          LEFT JOIN tblsessions ses ON ses.Id = a.sessionId
          WHERE a.classId = '$classId' AND a.classArmId = '$classArmId'
          AND a.dateTimeTaken BETWEEN '$dateFrom' AND '$dateTo'
          ORDER BY a.dateTimeTaken DESC, s.firstName ASC";
$rs = $conn->query($query);

if($exportType == 'excel' || $exportType == 'csv'){
    $filename = "presences_" . date('Y-m-d') . "." . ($exportType == 'excel' ? 'xls' : 'csv');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    if($exportType == 'excel'){
        echo "\xEF\xBB\xBF"; // UTF-8 BOM pour Excel
    }
    
    echo "Feuille d'Assiduité\n";
    echo "Période: " . date('d/m/Y', strtotime($dateFrom)) . " - " . date('d/m/Y', strtotime($dateTo)) . "\n\n";
    
    $delimiter = $exportType == 'csv' ? ',' : "\t";
    
    echo "Date" . $delimiter;
    echo "Heure" . $delimiter;
    echo "Nom" . $delimiter;
    echo "Prénom" . $delimiter;
    echo "N° Admission" . $delimiter;
    echo "Classe" . $delimiter;
    echo "Groupe" . $delimiter;
    echo "Statut" . $delimiter;
    echo "Commentaire" . $delimiter;
    echo "Participation" . $delimiter;
    echo "Commentaire Participation" . "\n";
    
    while($row = $rs->fetch_assoc()){
        $statusText = '';
        switch($row['statusType']){
            case 'present': $statusText = 'Présent'; break;
            case 'absent': $statusText = 'Absent'; break;
            case 'late': $statusText = 'Retard'; break;
            default: $statusText = 'Absent';
        }
        
        if($row['isJustified'] == 1){
            $statusText .= ' (Justifié)';
        }
        
        $participation = '';
        if($row['participation'] !== NULL){
            if($row['participationScore'] !== NULL){
                $participation = $row['participationScore'] . '/2';
            } else {
                $participation = $row['participation'] == 1 ? 'Oui' : 'Non';
            }
        }
        
        echo date('d/m/Y', strtotime($row['dateTimeTaken'])) . $delimiter;
        echo ($row['sessionTime'] ? date('H:i', strtotime($row['sessionTime'])) : '-') . $delimiter;
        echo $row['lastName'] . $delimiter;
        echo $row['firstName'] . $delimiter;
        echo $row['admissionNumber'] . $delimiter;
        echo $row['className'] . $delimiter;
        echo $row['classArmName'] . $delimiter;
        echo $statusText . $delimiter;
        echo ($row['comment'] ? $row['comment'] : '-') . $delimiter;
        echo $participation . $delimiter;
        echo ($row['participationComment'] ? $row['participationComment'] : '-') . "\n";
    }
    
    exit();
} elseif($exportType == 'pdf'){
    // Pour PDF, on va créer une page HTML qui peut être convertie en PDF
    // ou utiliser une bibliothèque comme TCPDF/FPDF
    header('Content-Type: text/html; charset=utf-8');
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Feuille d'Assiduité</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #000; padding: 5px; text-align: left; }
            th { background-color: #f0f0f0; font-weight: bold; }
            .header { text-align: center; margin-bottom: 20px; }
            .footer { margin-top: 20px; font-size: 8px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>Feuille d'Assiduité</h2>
            <p>Période: <?php echo date('d/m/Y', strtotime($dateFrom)) . " - " . date('d/m/Y', strtotime($dateTo)); ?></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>N° Admission</th>
                    <th>Statut</th>
                    <th>Commentaire</th>
                    <th>Participation</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($row = $rs->fetch_assoc()):
                    $statusText = '';
                    switch($row['statusType']){
                        case 'present': $statusText = 'Présent'; break;
                        case 'absent': $statusText = 'Absent'; break;
                        case 'late': $statusText = 'Retard'; break;
                        default: $statusText = 'Absent';
                    }
                    
                    if($row['isJustified'] == 1){
                        $statusText .= ' (Justifié)';
                    }
                    
                    $participation = '';
                    if($row['participation'] !== NULL){
                        if($row['participationScore'] !== NULL){
                            $participation = $row['participationScore'] . '/2';
                        } else {
                            $participation = $row['participation'] == 1 ? 'Oui' : 'Non';
                        }
                    }
                ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($row['dateTimeTaken'])); ?></td>
                    <td><?php echo $row['sessionTime'] ? date('H:i', strtotime($row['sessionTime'])) : '-'; ?></td>
                    <td><?php echo $row['lastName']; ?></td>
                    <td><?php echo $row['firstName']; ?></td>
                    <td><?php echo $row['admissionNumber']; ?></td>
                    <td><?php echo $statusText; ?></td>
                    <td><?php echo $row['comment'] ? $row['comment'] : '-'; ?></td>
                    <td><?php echo $participation; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Généré le <?php echo date('d/m/Y à H:i'); ?></p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
    </html>
    <?php
    exit();
}

?>

