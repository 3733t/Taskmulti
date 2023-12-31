<?php include 'db_connect.php' ?>
<?php
$twhere ="";
if($_SESSION['login_type'] != 1)
  $twhere = "  ";
?>
<!-- Info boxes -->
 <div class="col-12">
          <div class="card">
            <div class="card-body">
              Welcome <?php echo $_SESSION['login_name'] ?>!
            </div>
          </div>
  </div>
  <hr>
  <?php 

    $where = "";
    if($_SESSION['login_type'] == 2){
      $where = " where manager_id = :login_id ";
    }elseif($_SESSION['login_type'] == 3){
      $where = " where concat('[',REPLACE(user_ids,',','],['),']') LIKE :login_id ";
    }
    $where2 = "";
    if($_SESSION['login_type'] == 2){
      $where2 = " where p.manager_id = :login_id ";
    }elseif($_SESSION['login_type'] == 3){
      $where2 = " where concat('[',REPLACE(p.user_ids,',','],['),']') LIKE :login_id ";
    }

    $stmt = $conn->prepare("SELECT * FROM project_list $where order by name asc");
    if($_SESSION['login_type'] == 2 || $_SESSION['login_type'] == 3){
      $stmt->bindValue(':login_id', $_SESSION['login_id']);
    }
    $stmt->execute();
    $qry = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $i = 1;
    $stat = array("Pending","Started","On-Progress","On-Hold","Over Due","Done");
?>

<div class="row">
  <div class="col-md-8">
    <div class="card card-outline card-success">
      <div class="card-header">
        <b>Project Progress</b>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table m-0 table-hover">
            <colgroup>
              <col width="5%">
              <col width="30%">
              <col width="35%">
              <col width="15%">
              <col width="15%">
            </colgroup>
            <thead>
              <tr>
                <th>#</th>
                <th>Project</th>
                <th>Progress</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($qry as $row): ?>
              <?php
              $prog= 0;
              $stmt = $conn->prepare("SELECT * FROM task_list where project_id = :project_id");
              $stmt->bindValue(':project_id', $row['id']);
              $stmt->execute();
              $tprog = $stmt->rowCount();
              $stmt = $conn->prepare("SELECT * FROM task_list where project_id = :project_id and status = 3");
              $stmt->bindValue(':project_id', $row['id']);
              $stmt->execute();
              $cprog = $stmt->rowCount();
              $prog = $tprog > 0 ? ($cprog/$tprog) * 100 : 0;
              $prog = $prog > 0 ?  number_format($prog,2) : $prog;
              $stmt = $conn->prepare("SELECT * FROM user_productivity where project_id = :project_id");
              $stmt->bindValue(':project_id', $row['id']);
              $stmt->execute();
              $prod = $stmt->rowCount();
              if($row['status'] == 0 && strtotime(date('Y-m-d')) >= strtotime($row['start_date'])):
                if($prod  > 0  || $cprog > 0)
                  $row['status'] = 2;
                else
                  $row['status'] = 1;
              elseif($row['status'] == 0 && strtotime(date('Y-m-d')) > strtotime($row['end_date'])):
                $row['status'] = 4;
              endif;
              ?>
              <tr>
                <td>
                  <?php echo $i++ ?>
                </td>
                <td>
                  <a>
                    <?php echo ucwords($row['name']) ?>
                  </a>
                  <br>
                  <small>
                    Due: <?php echo date("Y-m-d",strtotime($row['end_date'])) ?>
                  </small>
                </td>
                <td class="project_progress">
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-green" role="progressbar" aria-valuenow="57" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $prog ?>%">
                    </div>
                  </div>
                  <small>
                    <?php echo $prog ?>% Complete
                  </small>
                </td>
                <td class="project-state">
                  <?php
                    $status = $stat[$row['status']];
                    $badge_class = 'badge-secondary';
                    if ($status == 'Started') {
                      $badge_class = 'badge-primary';
                    } elseif ($status == 'On-Progress') {
                      $badge_class = 'badge-info';
                    } elseif ($status == 'On-Hold') {
                      $badge_class = 'badge-warning';
                    } elseif ($status == 'Over Due') {
                      $badge_class = 'badge-danger';
                    } elseif ($status == 'Done') {
                      $badge_class = 'badge-success';
                    }
                  ?>
                  <span class="badge <?php echo $badge_class ?>"><?php echo $status ?></span>
                </td>
                <td>
                  <a class="btn btn-primary btn-sm" href="./index.php?page=view_project&id=<?php echo $row['id'] ?>">
                    <i class="fas fa-folder"></i>
                    View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>  
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="row">
      <div class="col-12 col-sm-6 col-md-12">
        <div class="small-box bg-light shadow-sm border">
          <div class="inner">
            <?php
            $stmt = $conn->prepare("SELECT * FROM project_list $where");
            if($_SESSION['login_type'] == 2 || $_SESSION['login_type'] == 3){
              $stmt->bindValue(':login_id', $_SESSION['login_id']);
            }
            $stmt->execute();
            $total_projects = $stmt->rowCount();
            ?>
            <h3><?php echo $total_projects; ?></h3>
            <p>Total Projects</p>
          </div>
          <div class="icon">
            <i class="fa fa-layer-group"></i>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-12">
        <div class="small-box bg-light shadow-sm border">
          <div class="inner">
            <?php
            $stmt = $conn->prepare("SELECT t.*,p.name as pname,p.start_date,p.status as pstatus, p.end_date,p.id as pid FROM task_list t inner join project_list p on p.id = t.project_id $where2");
            if($_SESSION['login_type'] == 2 || $_SESSION['login_type'] == 3){
              $stmt->bindValue(':login_id', $_SESSION['login_id']);
            }
            $stmt->execute();
            $total_tasks = $stmt->rowCount();
            ?>
            <h3><?php echo $total_tasks; ?></h3>
            <p>Total Tasks</p>
          </div>
          <div class="icon">
            <i class="fa fa-tasks"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

