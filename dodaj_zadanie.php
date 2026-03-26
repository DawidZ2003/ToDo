<?php
declare(strict_types=1);
session_start();

// Włączanie wyświetlania błędów dla debugowania
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}

// Połączenie z bazą
$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");
if(!$link){
    die("Błąd połączenia z bazą: ". mysqli_connect_error());
}

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idp_pm = $_SESSION['idp']; // Project Manager
    $nazwa_zadania = trim($_POST['nazwa_zadania'] ?? '');
    $podzadania = $_POST['nazwa_podzadanie'] ?? [];
    $wykonawcy = $_POST['idp_wykonawca'] ?? [];
    $stany = $_POST['stan'] ?? [];

    if (empty($nazwa_zadania)) {
        die("Nazwa zadania nie może być pusta!");
    }

    // Dodanie zadania
    $stmt = mysqli_prepare($link, "INSERT INTO zadanie (idp, nazwa_zadania) VALUES (?, ?)");
    if (!$stmt) {
        die("Błąd przygotowania zapytania zadania: " . mysqli_error($link));
    }
    mysqli_stmt_bind_param($stmt, "is", $idp_pm, $nazwa_zadania);
    mysqli_stmt_execute($stmt);
    $idz = mysqli_insert_id($link);

    // Dodanie podzadań
    $stmt2 = mysqli_prepare($link, "INSERT INTO podzadanie (idz, idp, nazwa_podzadania, stan) VALUES (?, ?, ?, ?)");
    if (!$stmt2) {
        die("Błąd przygotowania zapytania podzadań: " . mysqli_error($link));
    }

    for ($i = 0; $i < count($podzadania); $i++) {
        $nazwa = trim($podzadania[$i] ?? '');
        $wykonawca = $wykonawcy[$i] ?? null;
        $stan = (int)($stany[$i] ?? 0);

        // Pomijamy puste podzadania lub brak wykonawcy
        if ($nazwa === '' || $wykonawca === null) continue;

        mysqli_stmt_bind_param($stmt2, "iisi", $idz, $wykonawca, $nazwa, $stan);
        mysqli_stmt_execute($stmt2);
    }

    // Po zapisaniu przekierowanie do listy zadań
    header("Location: index.php");
    exit();
} else {
    die("Formularz nie został wysłany metodą POST.");
}
?>