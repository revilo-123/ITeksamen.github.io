<?php
$serverName = "OLIVERSIN";
$connectionOptions = array(
    "Database" => "Valg 2",
    "Uid" => "katine",
    "PWD" => "katine"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>
