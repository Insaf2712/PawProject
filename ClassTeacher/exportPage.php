
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Exporter les Présences</title>
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
            <h1 class="h3 mb-0 text-gray-800">Exporter les Présences</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Exporter</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Options d'Export</h6>
                </div>
                <div class="card-body">
                  <form method="get" action="exportAttendance.php">
                    <div class="form-group row">
                      <label class="col-sm-3 col-form-label">Date de début</label>
                      <div class="col-sm-9">
                        <input type="date" name="dateFrom" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                      </div>
                    </div>
                    
                    <div class="form-group row">
                      <label class="col-sm-3 col-form-label">Date de fin</label>
                      <div class="col-sm-9">
                        <input type="date" name="dateTo" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                    </div>
                    
                    <div class="form-group row">
                      <label class="col-sm-3 col-form-label">Format d'export</label>
                      <div class="col-sm-9">
                        <select name="type" class="form-control" required>
                          <option value="excel">Excel (XLS)</option>
                          <option value="csv">CSV</option>
                          <option value="pdf">PDF</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="form-group row">
                      <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">
                          <i class="fas fa-download"></i> Exporter
                        </button>
                        <a href="downloadRecord.php" class="btn btn-secondary">
                          <i class="fas fa-file-excel"></i> Rapport du Jour (XLS)
                        </a>
                      </div>
                    </div>
                  </form>
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

