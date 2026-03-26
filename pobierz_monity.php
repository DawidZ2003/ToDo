<?php
session_start();

if (!isset($_SESSION['idp'])) {
    die(json_encode([]));
}

$idp = (int)$_SESSION['idp'];

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");

$sql_monit = "
SELECT m.idm, m.tresc, m.data_wyslania, z.nazwa_zadania, p.nazwa_podzadania, u.login
FROM monit m
JOIN zadanie z ON m.idz = z.idz
JOIN podzadanie p ON m.idpz = p.idpz
JOIN pracownik u ON m.id_od = u.idp
WHERE m.id_do = ?
ORDER BY m.data_wyslania DESC
";

$stmt = mysqli_prepare($link, $sql_monit);
mysqli_stmt_bind_param($stmt, "i", $idp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$monity = [];
while ($m = mysqli_fetch_assoc($result)) {
    $monity[] = $m;
}

echo json_encode($monity);

?>