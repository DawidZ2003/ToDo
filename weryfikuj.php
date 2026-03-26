<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</HEAD>
<BODY>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Połączenie z bazą
$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");

if(!$link){
    die("Błąd: ". mysqli_connect_error());
}

mysqli_set_charset($link, "utf8");

// Dane z formularza
$user = $_POST['user'];
$pass = $_POST['pass'];

// =====================
// SZUKANIE UŻYTKOWNIKA
// =====================
$stmt = mysqli_prepare($link, "SELECT idp, password FROM pracownik WHERE login=?");
mysqli_stmt_bind_param($stmt, "s", $user);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt) > 0){

    mysqli_stmt_bind_result($stmt, $idp, $dbpass);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // =====================
    // SPRAWDZENIE BLOKADY
    // =====================
    $stmt2 = mysqli_prepare($link, "
        SELECT COUNT(*) 
        FROM logowanie 
        WHERE idp=? 
        AND state > 0 
        AND datetime > (NOW() - INTERVAL 1 MINUTE)
    ");
    mysqli_stmt_bind_param($stmt2, "i", $idp);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_bind_result($stmt2, $proby);
    mysqli_stmt_fetch($stmt2);
    mysqli_stmt_close($stmt2);

    if($proby >= 3){
        echo "Konto zablokowane na 1 minutę!";
        exit();
    }

    // =====================
    // SPRAWDZENIE HASŁA
    // =====================
    if($user === 'admin' && $pass === 'admin') {
    $_SESSION['loggedin'] = true;
    $_SESSION['user'] = 'admin';
    $_SESSION['idp'] = $idp;          // admin nie ma idp w tabeli pracownik
    $_SESSION['is_admin'] = true;

    // Możesz opcjonalnie logować admina
    $stmtAdmin = mysqli_prepare($link, "INSERT INTO logowanie (idp, state) VALUES (?, 0)");
    mysqli_stmt_bind_param($stmtAdmin, "i", $_SESSION['idp']);
    mysqli_stmt_execute($stmtAdmin);
    mysqli_stmt_close($stmtAdmin);

    header("Location: admin.php");
    exit();
    
    } else if($dbpass == $pass){

        $stmt3 = mysqli_prepare($link, "
            INSERT INTO logowanie (idp, state) 
            VALUES (?, 0)
        ");
        mysqli_stmt_bind_param($stmt3, "i", $idp);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);

        $_SESSION['loggedin'] = true;
        $_SESSION['user'] = $user;
        $_SESSION['idp'] = $idp;

        header("Location: index.php");
        exit();

    } else {

        // =====================
        // BŁĘDNE HASŁO
        // =====================
        $stmt4 = mysqli_prepare($link, "
            SELECT COUNT(*) 
            FROM logowanie 
            WHERE idp=? 
            AND state > 0
        ");
        mysqli_stmt_bind_param($stmt4, "i", $idp);
        mysqli_stmt_execute($stmt4);
        mysqli_stmt_bind_result($stmt4, $ile);
        mysqli_stmt_fetch($stmt4);
        mysqli_stmt_close($stmt4);

        $nowa = $ile + 1;

        $stmt5 = mysqli_prepare($link, "
            INSERT INTO logowanie (idp, state) 
            VALUES (?, ?)
        ");
        mysqli_stmt_bind_param($stmt5, "ii", $idp, $nowa);
        mysqli_stmt_execute($stmt5);
        mysqli_stmt_close($stmt5);

        echo "Niepoprawny login lub hasło !";
    }

} else {

    mysqli_stmt_close($stmt);

    // =====================
    // UŻYTKOWNIK NIE ISTNIEJE
    // =====================
    $stmt6 = mysqli_prepare($link, "
        INSERT INTO logowanie (idp, state) 
        VALUES (NULL, -1)
    ");
    mysqli_stmt_execute($stmt6);
    mysqli_stmt_close($stmt6);

    echo "Niepoprawny login lub hasło !";
}

// Zamknięcie połączenia
mysqli_close($link);
?>
</BODY>
</HTML>