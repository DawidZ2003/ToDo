<?php
declare(strict_types=1);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------
// 1. Sprawdzenie sesji
// ----------------------
if (!isset($_SESSION['idp'])) {
    die("Nie jesteś zalogowany. Zaloguj się, aby wysyłać monity.");
}

$id_od = (int)$_SESSION['idp'];

// ----------------------
// 2. Połączenie z bazą
// ----------------------
$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");
if (!$link) {
    die("Błąd połączenia z bazą: " . mysqli_connect_error());
}

// ----------------------
// 3. Sprawdzenie danych POST
// ----------------------
if (!isset($_POST['idpz'], $_POST['tresc'])) {
    die("Brak danych do wysłania monitów");
}

$idpz = (int)$_POST['idpz'];
$tresc = trim($_POST['tresc']);
if ($tresc === "") die("Treść monitu nie może być pusta");

// ----------------------
// 4. Pobranie podzadania
// ----------------------
$stmt = mysqli_prepare($link, "SELECT idz, idp FROM podzadanie WHERE idpz = ?");
mysqli_stmt_bind_param($stmt, "i", $idpz);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $idz, $id_do);
$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$found) die("Podzadanie nie istnieje");

// ----------------------
// 5. Sprawdzenie czy jesteś PM
// ----------------------
$stmt2 = mysqli_prepare($link, "SELECT idp FROM zadanie WHERE idz = ?");
mysqli_stmt_bind_param($stmt2, "i", $idz);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $pm);
mysqli_stmt_fetch($stmt2);
mysqli_stmt_close($stmt2);

if ($pm != $id_od) {
    die("Nie jesteś Project Managerem tego zadania");
}

// ----------------------
// 6. Wstawienie rekordu do monit
// ----------------------
$stmt3 = mysqli_prepare($link, "INSERT INTO monit (idz, idpz, id_od, id_do, tresc) VALUES (?, ?, ?, ?, ?)");
if (!$stmt3) die("Błąd przygotowania zapytania INSERT: " . mysqli_error($link));

mysqli_stmt_bind_param($stmt3, "iiiis", $idz, $idpz, $id_od, $id_do, $tresc);
if (!mysqli_stmt_execute($stmt3)) {
    die("Błąd wykonania INSERT: " . mysqli_error($link));
}
mysqli_stmt_close($stmt3);

// ----------------------
// 7. Sukces
// ----------------------
echo "OK";

?>