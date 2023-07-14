<?php include 'db_connect.php';
if(isset($_GET['id'])){
  $stmt = $conn->prepare("SELECT * FROM task_list where id = :id");
  $stmt->bindValue(':id', $_GET['id']);
  $stmt->execute();
  $qry = $stmt->fetch(PDO::FETCH_ASSOC);
  foreach($qry as $k => $v){
    $$k = $v;
  }
}
?>
<div class="container-fluid">
  <dl>
    <dt><b class="border-bottom border-primary">Task</b></dt>
    <dd><?php echo ucwords($task) ?></dd>
  </dl>
  <dl>
    <dt><b class="border-bottom border-primary">Status</b></dt>
    <dd>
      <?php 
      if($status == 1){
        echo "<span class='badge badge-secondary'>Pending</span>";
      }elseif($status == 2){
        echo "<span class='badge badge-primary'>On-Progress</span>";
      }elseif($status == 3){
        echo "<span class='badge badge-success'>Done</span>";
      }
      ?>
    </dd>
  </dl>
  <dl>
    <dt><b class="border-bottom border-primary">Description</b></dt>
    <dd><?php echo html_entity_decode($description) ?></dd>
  </dl>
</div>

