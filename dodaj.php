<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</HEAD>
<BODY>
<?php
session_start();
$user = htmlentities ($_POST['user'], ENT_QUOTES, "UTF-8"); // rozbrojenie potencjalnej bomby w zmiennej $user
$pass = htmlentities ($_POST['pass'], ENT_QUOTES, "UTF-8"); // rozbrojenie potencjalnej bomby w zmiennej $pass
$pass2 = htmlentities ($_POST['pass2'], ENT_QUOTES, "UTF-8"); // rozbrojenie potencjalnej bomby w zmiennej $pass
$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13"); // połączenie z BD – wpisać swoje dane
if(!$link) { echo"Błąd: ". mysqli_connect_errno()." ".mysqli_connect_error(); } // obsługa błędu połączenia z BD
mysqli_query($link, "SET NAMES 'utf8'"); // ustawienie polskich znaków
// prosta walidacja
if ($user === '' || $pass === '' || $pass2 === '') {
    echo "Wypełnij wszystkie pola formularza.";
    mysqli_close($link);
    exit();
}
$result = mysqli_query($link, "SELECT * FROM pracownik WHERE login='$user'");
if (mysqli_num_rows($result) > 0) 
{
    echo "Użytkownik o podanym loginie już istnieje";
    mysqli_close($link);
    exit();
}
if($pass!==$pass2)
{
    echo "Hasła nie są identyczne. Spróbuj ponownie";
    mysqli_close($link);
    exit();
}
else
{
    mysqli_query($link, "INSERT INTO pracownik (login, password) VALUES ('$user', '$pass'); ");
    header('Location: logowanie.php');
    exit();
}
?>
</BODY>
</HTML>