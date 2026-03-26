<?php
declare(strict_types=1);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (empty($_SESSION['loggedin']) || empty($_SESSION['idp'])) {
    header('Location: logowanie.php');
    exit();
}

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");
if(!$link){
    die("Błąd połączenia: " . mysqli_connect_error());
}

$idp = $_SESSION['idp'];

// --------------------
// Funkcje pomocnicze
// --------------------
function kolor($stan) {
    if ($stan == 0) return "text-danger";
    if ($stan == 100) return "text-success";
    return "text-dark";
}

function wydajnosc_symbol($avg, $team_avg) {
    if ($avg < 0.5 * $team_avg) return "🐌";   // bardzo wolny
    if ($avg < 0.8 * $team_avg) return "🐢";   // wolny
    if ($avg <= 1.2 * $team_avg) return "🧍";  // przeciętny
    return "🐆";                                // szybki
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<?php include "navbar.php"; ?>

<main class="flex-grow-1 container mt-4">

<h2><strong>Witaj, <?= htmlspecialchars($_SESSION['user']) ?>!</strong></h2>

<!-- ================= MONITY ================= -->
<h4>🔔 Monity</h4>
<div id="monity-container">
    <p>Ładowanie...</p>
</div>

<hr>

<!-- ================= PODZADANIA PRACOWNIKA ================= -->
<h4>Twoje podzadania</h4>

<?php
$sql = "
SELECT p.idpz, p.nazwa_podzadania, p.stan, z.nazwa_zadania, z.idz, u.login AS pm
FROM podzadanie p
JOIN zadanie z ON p.idz = z.idz
JOIN pracownik u ON z.idp = u.idp
WHERE p.idp = ?
";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $idp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "<p>Brak podzadań.</p>";
}

while ($row = mysqli_fetch_assoc($result)):
    $stmt_avg = mysqli_prepare($link, "SELECT AVG(stan) FROM podzadanie WHERE idz = ?");
    mysqli_stmt_bind_param($stmt_avg, "i", $row['idz']);
    mysqli_stmt_execute($stmt_avg);
    mysqli_stmt_bind_result($stmt_avg, $avg);
    mysqli_stmt_fetch($stmt_avg);
    mysqli_stmt_close($stmt_avg);
    $avg = round((float)($avg ?? 0));
?>

<div class="<?= kolor($row['stan']) ?>">
    <?= htmlspecialchars($row['nazwa_zadania']) ?>
    [<?= $avg ?>%]
    (<?= htmlspecialchars($row['pm']) ?>)
    → <?= htmlspecialchars($row['nazwa_podzadania']) ?>
    <?= $row['stan'] ?>%
</div>

<?php endwhile; ?>

<br>

<!-- ================= ZADANIA PM ================= -->
<h4>Twoje zadania (Project Manager)</h4>

<form method="POST" action="update_stan.php">

<?php
$sql2 = "SELECT z.idz, z.nazwa_zadania FROM zadanie z WHERE z.idp = ?";
$stmt2 = mysqli_prepare($link, $sql2);
mysqli_stmt_bind_param($stmt2, "i", $idp);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

// -------------------------
// Pobranie wydajności teamu
// -------------------------
$stmt_team = mysqli_prepare($link, "
    SELECT p.idp, u.login, AVG(p.stan) as avg_stan
    FROM podzadanie p
    JOIN pracownik u ON p.idp = u.idp
    JOIN zadanie z ON p.idz = z.idz
    WHERE z.idp = ?
    GROUP BY p.idp
");
mysqli_stmt_bind_param($stmt_team, "i", $idp);
mysqli_stmt_execute($stmt_team);
$result_team = mysqli_stmt_get_result($stmt_team);

$team = [];
$all_avg = [];
while ($row_team = mysqli_fetch_assoc($result_team)) {
    $team[] = $row_team;
    $all_avg[] = (float)$row_team['avg_stan'];
}

$team_avg = count($all_avg) ? array_sum($all_avg)/count($all_avg) : 0;
?>

<!-- ================= WIZUALIZACJA WYDAJNOŚCI ================= -->
<h4>📊 Wydajność pracowników</h4>
<ul>
<?php foreach ($team as $pracownik): 
    $symbol = wydajnosc_symbol((float)$pracownik['avg_stan'], $team_avg);
?>
    <li>
        <?= htmlspecialchars($pracownik['login']) ?>:
        <?php $avg_float = isset($pracownik['avg_stan']) ? floatval($pracownik['avg_stan']) : 0; ?>
<?= $symbol ?> (<?= round($avg_float, 1) ?>%)
    </li>
<?php endforeach; ?>
</ul>

<?php while ($zadanie = mysqli_fetch_assoc($result2)):
    $idz = $zadanie['idz'];

    $stmt_avg = mysqli_prepare($link, "SELECT AVG(stan) FROM podzadanie WHERE idz = ?");
    mysqli_stmt_bind_param($stmt_avg, "i", $idz);
    mysqli_stmt_execute($stmt_avg);
    mysqli_stmt_bind_result($stmt_avg, $avg);
    mysqli_stmt_fetch($stmt_avg);
    mysqli_stmt_close($stmt_avg);
    $avg = round((float)($avg ?? 0));

    $sql3 = "
    SELECT p.idpz, p.idp, p.nazwa_podzadania, p.stan, u.login
    FROM podzadanie p
    JOIN pracownik u ON p.idp = u.idp
    WHERE p.idz = ?
    ";
    $stmt3 = mysqli_prepare($link, $sql3);
    mysqli_stmt_bind_param($stmt3, "i", $idz);
    mysqli_stmt_execute($stmt3);
    $pod = mysqli_stmt_get_result($stmt3);
?>

<div class="d-flex align-items-center gap-2 mt-3">
    <strong>
        <?= htmlspecialchars($zadanie['nazwa_zadania']) ?> (Ty) [<?= $avg ?>%]
    </strong>

    <button type="submit"
            formaction="usun_zadanie.php"
            formmethod="POST"
            name="usun_zadanie"
            value="<?= $idz ?>"
            class="btn btn-sm btn-danger"
            onclick="return confirm('Usunąć zadanie?')">
        🗑 Usuń zadanie
    </button>
</div>

<?php while ($p = mysqli_fetch_assoc($pod)): ?>

<div class="<?= kolor($p['stan']) ?> d-flex align-items-center gap-2">

    → <?= htmlspecialchars($p['nazwa_podzadania']) ?>
    (<?= htmlspecialchars($p['login']) ?>)

    <input type="range"
           name="stan[<?= $p['idpz'] ?>]"
           min="0" max="100"
           value="<?= $p['stan'] ?>"
           oninput="this.nextElementSibling.value = this.value">
    <output><?= $p['stan'] ?></output>%

    <button type="submit"
            formaction="usun_podzadanie.php"
            formmethod="POST"
            name="idpz"
            value="<?= $p['idpz'] ?>"
            class="btn btn-sm btn-outline-danger">
        🗑Usuń podzadanie
    </button>

    <!-- 🔔 MONIT -->
    <button type="button"
            class="btn btn-sm btn-warning"
            onclick="wyslijMonit(<?= $p['idpz'] ?>)">
        🔔 Wyślij monit
    </button>

</div>

<?php endwhile; ?>
<?php endwhile; ?>

<button type="submit" class="btn btn-primary mt-3">Zapisz zmiany</button>

</form>

</main>

<?php include "footer.php"; ?>

<!-- ================= SKRYPTY ================= -->
<script>
function wyslijMonit(idpz) {
    const tresc = prompt("Treść monitu:");

    if (!tresc) return;

    fetch("wyslij_monit.php", {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "idpz=" + idpz + "&tresc=" + encodeURIComponent(tresc)
    })
    .then(res => res.text())
    .then(data => alert(data));
}

// -----------------
// Odświeżanie monitów
// -----------------
function renderMonity(monity) {
    const container = document.getElementById("monity-container");
    if (monity.length === 0) {
        container.innerHTML = "<p>Brak monitów.</p>";
        return;
    }

    container.innerHTML = "";
    monity.forEach(m => {
        const div = document.createElement("div");
        div.className = "alert alert-warning";
        div.innerHTML = `<strong>Monit od: ${m.login}</strong><br>
                         ${m.tresc}<br>
                         <small>${m.nazwa_zadania} → ${m.nazwa_podzadania} | ${m.data_wyslania}</small>`;
        container.appendChild(div);
    });
}

function pobierzMonity() {
    fetch("pobierz_monity.php", { credentials: "same-origin" })
        .then(res => res.json())
        .then(data => renderMonity(data))
        .catch(err => console.error("Błąd pobierania monitów:", err));
}

// pierwsze pobranie i cykliczne co 5s
pobierzMonity();
setInterval(pobierzMonity, 5000);
</script>

</body>
</html>
