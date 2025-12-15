
<?php 
  $query = "SELECT * FROM tblstudents WHERE Id = ".$_SESSION['userId']."";
  $rs = $conn->query($query);
  $num = $rs->num_rows;
  $rows = $rs->fetch_assoc();
  $fullName = $rows['firstName']." ".$rows['lastName'];
  
  // Compter les notifications non lues
  $notifQuery = "SELECT COUNT(*) as count FROM tblnotifications WHERE userId = '".$_SESSION['userId']."' AND userType = 'student' AND isRead = 0";
  $notifRs = $conn->query($notifQuery);
  $notifRow = $notifRs->fetch_assoc();
  $notifCount = $notifRow['count'];
?>
<nav class="navbar navbar-expand navbar-light bg-gradient-primary topbar mb-4 static-top">
          <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
        <div class="text-white big" style="margin-left:100px;"></div>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php if($notifCount > 0): ?>
                <span class="badge badge-danger badge-counter"><?php echo $notifCount; ?></span>
                <?php endif; ?>
              </a>
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="notificationDropdown" style="max-height: 400px; overflow-y: auto;">
                <h6 class="dropdown-header">Notifications</h6>
                <?php
                  $notifListQuery = "SELECT * FROM tblnotifications WHERE userId = '".$_SESSION['userId']."' AND userType = 'student' ORDER BY dateCreated DESC LIMIT 10";
                  $notifListRs = $conn->query($notifListQuery);
                  if($notifListRs->num_rows > 0):
                    while($notif = $notifListRs->fetch_assoc()):
                ?>
                <a class="dropdown-item d-flex align-items-center" href="<?php echo $notif['link'] ? $notif['link'] : '#'; ?>">
                  <div class="mr-3">
                    <div class="icon-circle bg-primary">
                      <i class="fas fa-bell text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500"><?php echo date('d/m/Y H:i', strtotime($notif['dateCreated'])); ?></div>
                    <span class="<?php echo $notif['isRead'] == 0 ? 'font-weight-bold' : ''; ?>"><?php echo $notif['title']; ?></span>
                  </div>
                </a>
                <?php
                    endwhile;
                  else:
                ?>
                <div class="dropdown-item text-center text-gray-500">Aucune notification</div>
                <?php endif; ?>
                <a class="dropdown-item text-center small text-gray-500" href="notifications.php">Voir toutes les notifications</a>
              </div>
            </li>
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle" src="../img/user-icn.png" style="max-width: 60px">
                <span class="ml-2 d-none d-lg-inline text-white small"><b>Bienvenue <?php echo $fullName;?></b></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="changePassword.php">
                  <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                  Modifier le mot de passe
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php">
                <i class="fas fa-power-off fa-fw mr-2 text-danger"></i>
                  Déconnexion
                </a>
              </div>
            </li>
          </ul>
        </nav>

