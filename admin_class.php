<?php
session_start();
ini_set('display_errors', 1);

class Action {
	private $db;

	public function __construct() {
		ob_start();
		include 'db_connect.php';
		$this->db = $conn;
	}

	function __destruct() {
		$this->db = null;
		ob_end_flush();
	}

	function login() {
		extract($_POST);
		$query = "SELECT *, CONCAT(firstname,' ',lastname) AS name FROM users WHERE email = :email AND password = :password";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':password', md5($password));
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			foreach ($row as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			return 1;
		} else {
			return 2;
		}
	}

	function logout() {
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}

	function login2() {
		extract($_POST);
		$query = "SELECT *, CONCAT(lastname,', ',firstname,' ',middlename) AS name FROM students WHERE student_code = :student_code";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':student_code', $student_code);
		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			foreach ($row as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['rs_'.$key] = $value;
			}
			return 1;
		} else {
			return 3;
		}
	}

	function save_user() {
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id','cpass','password')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		if (!empty($password)) {
			$data .= ", password=MD5(:password) ";
		}
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':email', $email);
		if (!empty($id)) {
			$query .= " AND id != :id";
			$stmt->bindParam(':id', $id);
		}
		$stmt->execute();
		$check = $stmt->rowCount();
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/'.$fname);
			$data .= ", avatar = :fname ";
		}
		if (empty($id)) {
			$query = "INSERT INTO users SET $data";
		} else {
			$query = "UPDATE users SET $data WHERE id = :id";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!in_array($k, array('id','cpass','password')) && !is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		if (!empty($id)) {
			$stmt->bindParam(':id', $id);
		}
		$save = $stmt->execute();

		if ($save) {
			return 1;
		}
	}

	function signup() {
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id','cpass')) && !is_numeric($k)) {
				if ($k == 'password') {
					if (empty($v))
						continue;
					$v = md5($v);
				}
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':email', $email);
		if (!empty($id)) {
			$query .= " AND id != :id";
			$stmt->bindParam(':id', $id);
		}
		$stmt->execute();
		$check = $stmt->rowCount();
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/'.$fname);
			$data .= ", avatar = :fname ";
		}
		if (empty($id)) {
			$query = "INSERT INTO users SET $data";
		} else {
			$query = "UPDATE users SET $data WHERE id = :id";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!in_array($k, array('id','cpass','password')) && !is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		if (!empty($id)) {
			$stmt->bindParam(':id', $id);
		}
		$save = $stmt->execute();

		if ($save) {
			if (empty($id))
				$id = $this->db->lastInsertId();
			foreach ($_POST as $key => $value) {
				if (!in_array($key, array('id','cpass','password')) && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			$_SESSION['login_id'] = $id;
			if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
				$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}

	function update_user() {
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id','cpass','table','password')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':email', $email);
		if (!empty($id)) {
			$query .= " AND id != :id";
			$stmt->bindParam(':id', $id);
		}
		$stmt->execute();
		$check = $stmt->rowCount();
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/'.$fname);
			$data .= ", avatar = :fname ";
		}
		if (!empty($password))
			$data .= " ,password=MD5(:password) ";
		if (empty($id)) {
			$query = "INSERT INTO users SET $data";
		} else {
			$query = "UPDATE users SET $data WHERE id = :id";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!in_array($k, array('id','cpass','table','password')) && !is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		if (!empty($id)) {
			$stmt->bindParam(':id', $id);
		}
		$save = $stmt->execute();

		if ($save) {
			foreach ($_POST as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
				$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}

	function delete_user() {
		extract($_POST);
		$query = "DELETE FROM users WHERE id = :id";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id', $id);
		$delete = $stmt->execute();
		if ($delete) {
			return 1;
		}
	}

	function save_system_settings() {
		extract($_POST);
		$data = '';
		foreach ($_POST as $k => $v) {
			if (!is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		$query = "SELECT * FROM system_settings";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		$chk = $stmt->rowCount();
		if ($chk > 0) {
			$query = "UPDATE system_settings SET $data WHERE id = :id";
		} else {
			$query = "INSERT INTO system_settings SET $data";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		$save = $stmt->execute();
		if ($save) {
			foreach ($_POST as $k => $v) {
				if (!is_numeric($k)) {
					$_SESSION['system'][$k] = $v;
				}
			}
			if ($_FILES['cover']['tmp_name'] != '') {
				$_SESSION['system']['cover_img'] = $fname;
			}
			return 1;
		}
	}

	function save_image() {
		extract($_FILES['file']);
		if (!empty($tmp_name)) {
			$fname = strtotime(date("Y-m-d H:i"))."_".(str_replace(" ","-",$name));
			$move = move_uploaded_file($tmp_name, 'assets/uploads/'.$fname);
			$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
			$hostName = $_SERVER['HTTP_HOST'];
			$path = explode('/',$_SERVER['PHP_SELF']);
			$currentPath = '/'.$path[1];
			if ($move) {
				return $protocol.'://'.$hostName.$currentPath.'/assets/uploads/'.$fname;
			}
		}
	}

	function save_project() {
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id','user_ids')) && !is_numeric($k)) {
				if ($k == 'description')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		if (isset($user_ids)) {
			$data .= ", user_ids=:user_ids ";
		}
		if (empty($id)) {
			$query = "INSERT INTO project_list SET $data";
		} else {
			$query = "UPDATE project_list SET $data WHERE id = :id";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!in_array($k, array('id','user_ids')) && !is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		if (!empty($id)) {
			$stmt->bindParam(':id', $id);
		}
		$save = $stmt->execute();
		if ($save) {
			return 1;
		}
	}

	function delete_project() {
		extract($_POST);
		$query = "DELETE FROM project_list WHERE id = :id";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id', $id);
		$delete = $stmt->execute();
		if ($delete) {
			return 1;
		}
	}

	function save_task() {
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if ($k == 'description')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		if (empty($id)) {
			$query = "INSERT INTO task_list SET $data";
		} else {
			$query = "UPDATE task_list SET $data WHERE id = :id";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		if (!empty($id)) {
			$stmt->bindParam(':id', $id);
		}
		$save = $stmt->execute();
		if ($save) {
			return 1;
		}
	}

	function delete_task() {
		extract($_POST);
		$query = "DELETE FROM task_list WHERE id = :id";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id', $id);
		$delete = $stmt->execute();
		if ($delete) {
			return 1;
		}
	}

	function save_progress() {
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if ($k == 'comment')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if (empty($data)) {
					$data .= " $k=:{$k} ";
				} else {
					$data .= ", $k=:{$k} ";
				}
			}
		}
		$dur = abs(strtotime("2020-01-01 ".$end_time)) - abs(strtotime("2020-01-01 ".$start_time));
		$dur = $dur / (60 * 60);
		$data .= ", time_rendered=:dur ";
		if (empty($id)) {
			$data .= ", user_id=:user_id ";
			$query = "INSERT INTO user_productivity SET $data";
		} else {
			$query = "UPDATE user_productivity SET $data WHERE id = :id";
		}
		$stmt = $this->db->prepare($query);
		foreach ($_POST as $k => &$v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				$stmt->bindParam(':'.$k, $v);
			}
		}
		if (!empty($id)) {
			$stmt->bindParam(':id', $id);
		}
		$stmt->bindParam(':dur', $dur);
		$save = $stmt->execute();
		if ($save) {
			return 1;
		}
	}

	function delete_progress() {
		extract($_POST);
		$query = "DELETE FROM user_productivity WHERE id = :id";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id', $id);
		$delete = $stmt->execute();
		if ($delete) {
			return 1;
		}
	}

	function get_report() {
		extract($_POST);
		$data = array();
		$query = "SELECT t.*, p.name AS ticket_for FROM ticket_list t INNER JOIN pricing p ON p.id = t.pricing_id WHERE DATE(t.date_created) BETWEEN :date_from AND :date_to ORDER BY UNIX_TIMESTAMP(t.date_created) DESC";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':date_from', $date_from);
		$stmt->bindParam(':date_to', $date_to);
		$stmt->execute();

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$row['date_created'] = date("M d, Y", strtotime($row['date_created']));
			$row['name'] = ucwords($row['name']);
			$row['adult_price'] = number_format($row['adult_price'], 2);
			$row['child_price'] = number_format($row['child_price'], 2);
			$row['amount'] = number_format($row['amount'], 2);
			$data[] = $row;
		}
		return json_encode($data);
	}
}

