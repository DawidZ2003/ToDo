<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}

$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");
mysqli_set_charset($link, "utf8");

$idp = $_SESSION['idp'];

// Pobieramy tylko zadania gdzie user jest PM
$zadania = mysqli_query($link, "
    SELECT idz, nazwa_zadania 
    FROM zadanie 
    WHERE idp = $idp
");

// Pobieramy pracowników
$pracownicy = mysqli_query($link, "
    SELECT idp, login 
    FROM pracownik
");
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

<h3>Dodaj podzadanie</h3>

<form method="POST" action="dodaj_podzadanie.php">

<div class="mb-3">
    <label class="form-label">Wybierz zadanie</label>
    <select name="idz" class="form-control" required>
        <?php while($z = mysqli_fetch_assoc($zadania)): ?>
            <option value="<?= $z['idz'] ?>">
                <?= htmlspecialchars($z['nazwa_zadania']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Nazwa podzadania</label>
    <input type="text" name="nazwa_podzadania" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Wykonawca</label>
    <select name="idp_wykonawca" class="form-control" required>
        <?php while($p = mysqli_fetch_assoc($pracownicy)): ?>
            <option value="<?= $p['idp'] ?>">
                <?= htmlspecialchars($p['login']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Stan (%)</label>
    <input type="range" name="stan" min="0" max="100" value="0"
           oninput="this.nextElementSibling.value = this.value">
    <output>0</output>%
</div>

<button type="submit" class="btn btn-primary">Dodaj podzadanie</button>

</form>

<br>
<a href="index.php">Powrót</a>

</main>

<?php include "footer.php"; ?>
</body>
</html>