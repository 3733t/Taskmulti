<?php include 'db_connect.php'; ?>
<div class="col-md-12">
	<div class="card card-outline card-success">
		<div class="card-header">
			<b>Project Progress</b>
			<div class="card-tools">
				<button class="btn btn-flat btn-sm bg-gradient-success btn-success" id="print"><i class="fa fa-print"></i> Print</button>
			</div>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive" id="printable">
				<table class="table m-0 table-bordered">
					<thead>
						<th>#</th>
						<th>Project</th>
						<th>Task</th>
						<th>Completed Task</th>
						<th>Work Duration</th>
						<th>Progress</th>
						<th>Status</th>
					</thead>
					<tbody>
						<?php
						$i = 1;
						$stat = array("Pending", "Started", "On-Progress", "On-Hold", "Over Due", "Done");
						$where = "";
						if ($_SESSION['login_type'] == 2) {
							$where = " WHERE manager_id = :manager_id ";
						} elseif ($_SESSION['login_type'] == 3) {
							$where = " WHERE concat('[',REPLACE(user_ids,',','],['),']') LIKE :user_id ";
						}
						$query = "SELECT * FROM project_list $where ORDER BY name ASC";
						$stmt = $conn->prepare($query);
						if ($_SESSION['login_type'] == 2) {
							$stmt->bindParam(':manager_id', $_SESSION['login_id']);
						} elseif ($_SESSION['login_type'] == 3) {
							$user_id = "%[{$_SESSION['login_id']}]%";
							$stmt->bindParam(':user_id', $user_id);
						}
						$stmt->execute();
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
							$tprogStmt = $conn->prepare("SELECT * FROM task_list WHERE project_id = :project_id");
							$tprogStmt->bindParam(':project_id', $row['id']);
							$tprogStmt->execute();
							$tprog = $tprogStmt->rowCount();
							$cprogStmt = $conn->prepare("SELECT * FROM task_list WHERE project_id = :project_id AND status = 3");
							$cprogStmt->bindParam(':project_id', $row['id']);
							$cprogStmt->execute();
							$cprog = $cprogStmt->rowCount();
							$prog = $tprog > 0 ? ($cprog / $tprog) * 100 : 0;
							$prog = $prog > 0 ? number_format($prog, 2) : $prog;
							$prodStmt = $conn->prepare("SELECT * FROM user_productivity WHERE project_id = :project_id");
							$prodStmt->bindParam(':project_id', $row['id']);
							$prodStmt->execute();
							$prod = $prodStmt->rowCount();
							$durStmt = $conn->prepare("SELECT sum(time_rendered) as duration FROM user_productivity WHERE project_id = :project_id");
							$durStmt->bindParam(':project_id', $row['id']);
							$durStmt->execute();
							$dur = $durStmt->fetch(PDO::FETCH_ASSOC)['duration'];
							$dur = $dur ? $dur : 0;
							if ($row['status'] == 0 && strtotime(date('Y-m-d')) >= strtotime($row['start_date'])) {
								if ($prod > 0 || $cprog > 0)
									$row['status'] = 2;
								else
									$row['status'] = 1;
							} elseif ($row['status'] == 0 && strtotime(date('Y-m-d')) > strtotime($row['end_date'])) {
								$row['status'] = 4;
							}
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
										Due: <?php echo date("Y-m-d", strtotime($row['end_date'])) ?>
									</small>
								</td>
								<td class="text-center">
									<?php echo number_format($tprog) ?>
								</td>
								<td class="text-center">
									<?php echo number_format($cprog) ?>
								</td>
								<td class="text-center">
									<?php echo number_format($dur) . ' Hr/s.' ?>
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
									if ($stat[$row['status']] == 'Pending') {
										echo "<span class='badge badge-secondary'>{$stat[$row['status']]}</span>";
									} elseif ($stat[$row['status']] == 'Started') {
										echo "<span class='badge badge-primary'>{$stat[$row['status']]}</span>";
									} elseif ($stat[$row['status']] == 'On-Progress') {
										echo "<span class='badge badge-info'>{$stat[$row['status']]}</span>";
									} elseif ($stat[$row['status']] == 'On-Hold') {
										echo "<span class='badge badge-warning'>{$stat[$row['status']]}</span>";
									} elseif ($stat[$row['status']] == 'Over Due') {
										echo "<span class='badge badge-danger'>{$stat[$row['status']]}</span>";
									} elseif ($stat[$row['status']] == 'Done') {
										echo "<span class='badge badge-success'>{$stat[$row['status']]}</span>";
									}
									?>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
	$('#print').click(function () {
		start_load()
		var _h = $('head').clone()
		var _p = $('#printable').clone()
		var _d = "<p class='text-center'><b>Project Progress Report as of (<?php echo date("F d, Y") ?>)</b></p>"
		_p.prepend(_d)
		_p.prepend(_h)
		var nw = window.open("", "", "width=900,height=600")
		nw.document.write(_p.html())
		nw.document.close()
		nw.print()
		setTimeout(function () {
			nw.close()
			end_load()
		}, 750)
	})
</script>

