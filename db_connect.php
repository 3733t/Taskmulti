<?php
// PHP Data Objects(PDO) Sample Code:
/*try {
    $conn = new PDO("sqlsrv:server = tcp:task-db.database.windows.net,1433; Database = tms_db", "task", "sunspot7301#");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Error connecting to SQL Server.");
    die(print_r($e));
}

// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "task", "pwd" => "sunspot7301#", "Database" => "tms_db", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:task-db.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);

*/
$serverName = "tcp:task-db.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "tms_db",
    "Uid" => "task",
    "PWD" => "sunspot7301#",
    "LoginTimeout" => 30
);

try {
    $conn = new PDO("sqlsrv:server = $serverName", $connectionOptions);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
