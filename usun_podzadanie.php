<?php
session_start();

if (empty($_SESSION['loggedin']) || empty($_SESSION['idp'])) {
    header("Location: logowanie.php");
    exit();
}

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");
if (!$link) die("Błąd połączenia: " . mysqli_connect_error());

if (!isset($_POST['idpz'])) {
    header("Location: index.php");
    exit();
}

$idpz = (int)$_POST['idpz'];

// Usuwamy podzadanie po jego unikalnym ID
$sql = "DELETE FROM podzadanie WHERE idpz=?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $idpz);
$stmt->execute();

header("Location: index.php");
exit();

?>