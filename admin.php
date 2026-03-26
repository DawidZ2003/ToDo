<?php
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit();
}

$link = mysqli_connect("127.0.0.1","dm81079_z13","Dawidek7003#","dm81079_z13");
mysqli_set_charset($link, "utf8");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Panel Administratora</title>
</head>
<body class="d-flex flex-column min-vh-100">
    
<?php include "navbar.php"; ?>

<main class="flex-grow-1 container mt-4">

<h2>Panel Administratora</h2>
<hr>

<h4>Logowania wszystkich pracowników</h4>

<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">

<?php
$res = mysqli_query($link, "
    SELECT l.idl, l.idp, u.login, l.datetime, l.state
    FROM logowanie l
    LEFT JOIN pracownik u ON l.idp = u.idp
    ORDER BY l.datetime DESC
    LIMIT 50
");

while($row = mysqli_fetch_assoc($res)){
    $login = $row['login'] ?? '(nieznany użytkownik)';
    $kolor = ($row['state'] == 0) ? 'green' : (($row['state'] > 0) ? 'orange' : 'red');

    echo "<div style='color:$kolor'>";
    echo "Login: $login | Data: {$row['datetime']} | State: {$row['state']}";
    echo "</div>";
}
?>

</div>

<hr>
<h4>Zadania wszystkich pracowników</h4>
<?php
$sql = "
SELECT z.nazwa_zadania, pm.login AS pm, p.nazwa_podzadania, p.stan, w.login AS wykonawca
FROM zadanie z
JOIN pracownik pm ON z.idp = pm.idp
JOIN podzadanie p ON z.idz = p.idz
JOIN pracownik w ON p.idp = w.idp
ORDER BY z.nazwa_zadania
";

$res = mysqli_query($link, $sql);

while($row = mysqli_fetch_assoc($res)){
    $kolor = "black";
    if($row['stan'] == 0) $kolor = "red";
    elseif($row['stan'] == 100) $kolor = "green";

    echo "<div style='color:$kolor'>";
    echo $row['nazwa_zadania'] . " (PM: " . $row['pm'] . ")";
    echo " -> " . $row['nazwa_podzadania'];
    echo " (" . $row['wykonawca'] . ")";
    echo " " . $row['stan'] . "%";
    echo "</div>";
}
?>

<hr>

<hr>
<h4>Średni stopień realizacji wszystkich zadań przez pracowników</h4>

<?php
$sql_avg = "
SELECT 
    w.login AS pracownik,
    AVG(p.stan) AS srednia
FROM podzadanie p
JOIN pracownik w ON p.idp = w.idp
GROUP BY p.idp
ORDER BY w.login
";

$res_avg = mysqli_query($link, $sql_avg);

while($row = mysqli_fetch_assoc($res_avg)){

    $avg = round((float)($row['srednia'] ?? 0));

    // kolor zależny od średniego %: 0% -> czerwony, 100% -> zielony, reszta czarna
    $kolor = "black";
    if($avg == 0) $kolor = "red";
    elseif($avg == 100) $kolor = "green";

    echo "<div style='color:$kolor'>";
    echo $row['pracownik'] . " → " . $avg . "%";
    echo "</div>";
}
?>

<hr>

</main>

<?php include "footer.php"; ?>
</body>
</html>