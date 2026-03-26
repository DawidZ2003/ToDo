<?php 
declare(strict_types=1);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sprawdzenie logowania
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}

// Połączenie z bazą
$link = mysqli_connect("127.0.0.1","dm81079_z13", "Dawidek7003#", "dm81079_z13");
if(!$link){
    die("Błąd połączenia z bazą: ". mysqli_connect_error());
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Dodaj zadanie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        main {
            padding: 20px;
        }
        .podzadanie {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include "navbar.php"; ?>

<main class="flex-grow-1 container mt-4">
    <h2>Formularz dodawania zadania</h2>

    <form action="dodaj_zadanie.php" method="post">
        <!-- Nazwa zadania -->
        <div class="mb-3">
            <label for="nazwa_zadania" class="form-label">Nazwa zadania:</label>
            <input type="text" class="form-control" id="nazwa_zadania" name="nazwa_zadania" required>
        </div>

        <!-- Podzadania -->
        <div id="podzadania-container">
            <h4>Podzadania</h4>
            <div class="podzadanie mb-3">
                <input type="text" class="form-control mb-2" name="nazwa_podzadanie[]" placeholder="Nazwa podzadania">

                <label>Wykonawca:</label>
                <select class="form-select mb-2" name="idp_wykonawca[]">
                    <?php
                    $result = mysqli_query($link, "SELECT idp, login FROM pracownik");
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['idp']}'>{$row['login']}</option>";
                    }
                    ?>
                </select>

                <label>Stopień realizacji (0-100%)</label>
                <input type="range" name="stan[]" min="0" max="100" value="0" class="form-range">
            </div>
        </div>

        <button type="button" id="dodaj-podzadanie" class="btn btn-secondary mb-3">Dodaj kolejne podzadanie</button>
        <button type="submit" class="btn btn-primary">Zapisz zadanie</button>
    </form>
</main>

<?php include "footer.php"; ?>

<script>
// Dynamiczne dodawanie podzadań
document.getElementById('dodaj-podzadanie').addEventListener('click', function() {
    const container = document.getElementById('podzadania-container');
    const template = container.querySelector('.podzadanie');
    const newPodzadanie = template.cloneNode(true);

    // Wyczyść pola tekstowe w nowym podzadaniu
    newPodzadanie.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
    // Reset suwaka do 0
    newPodzadanie.querySelector('input[type="range"]').value = 0;
    // Reset select do pierwszej opcji
    newPodzadanie.querySelector('select').selectedIndex = 0;

    container.appendChild(newPodzadanie);
});
</script>

</body>
</html>