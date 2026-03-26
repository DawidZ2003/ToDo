<?php
session_start();

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");

if (!isset($_POST['stan'])) {
    header("Location: index.php");
    exit();
}

// Każdy klucz POST to idpz (unikalny ID podzadania)
foreach ($_POST['stan'] as $idpz => $stan) {
    $idpz = (int)$idpz;
    $stan = (int)$stan;

    $stmt = mysqli_prepare($link, "UPDATE podzadanie SET stan=? WHERE idpz=?");
    mysqli_stmt_bind_param($stmt, "ii", $stan, $idpz);
    $stmt->execute();
}

header("Location: index.php");

?>