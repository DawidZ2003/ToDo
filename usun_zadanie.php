<?php
session_start();

if (empty($_SESSION['loggedin']) || empty($_SESSION['idp'])) {
    header("Location: logowanie.php");
    exit();
}

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");

if (!isset($_POST['usun_zadanie'])) {
    header("Location: index.php");
    exit();
}


$idz = (int)$_POST['usun_zadanie'];
$moje_idp = $_SESSION['idp'];

mysqli_begin_transaction($link);

try {

    // 🔐 sprawdzenie czy to Twoje zadanie
    $check = mysqli_prepare($link, "SELECT idz FROM zadanie WHERE idz=? AND idp=?");
    mysqli_stmt_bind_param($check, "ii", $idz, $moje_idp);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) === 0) {
        throw new Exception("Brak uprawnień");
    }

    // 🧹 usuń podzadania
    $stmt1 = mysqli_prepare($link, "DELETE FROM podzadanie WHERE idz=?");
    mysqli_stmt_bind_param($stmt1, "i", $idz);
    mysqli_stmt_execute($stmt1);

    // 🧹 usuń zadanie
    $stmt2 = mysqli_prepare($link, "DELETE FROM zadanie WHERE idz=?");
    mysqli_stmt_bind_param($stmt2, "i", $idz);
    mysqli_stmt_execute($stmt2);

    mysqli_commit($link);

} catch (Exception $e) {
    mysqli_rollback($link);
    die("Błąd: " . $e->getMessage());
}

header("Location: index.php");
exit();

?>