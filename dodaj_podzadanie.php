<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idz = (int)($_POST['idz'] ?? 0);
    $nazwa = trim($_POST['nazwa_podzadania'] ?? '');
    $wykonawca = (int)($_POST['idp_wykonawca'] ?? 0);
    $stan = (int)($_POST['stan'] ?? 0);

    if ($idz <= 0 || $nazwa === '' || $wykonawca <= 0) {
        die("Błędne dane!");
    }

    $stmt = mysqli_prepare($link, "
        INSERT INTO podzadanie (idz, idp, nazwa_podzadania, stan)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Błąd zapytania: " . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt, "iisi", $idz, $wykonawca, $nazwa, $stan);
    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit();

} else {
    die("Błąd żądania!");
}