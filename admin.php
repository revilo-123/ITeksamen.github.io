 <?php
// Database-tilkobling
$serverName = "OLIVERSIN";
$connectionOptions = [
    "Database" => "Valg2",
    "Uid" => "kantine",
    "PWD" => "kantine",
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Databasefeil: " . print_r(sqlsrv_errors(), true));
}

// Hent landene
$land = [];
$stmt = sqlsrv_query($conn, "SELECT LandID, LandNavn FROM Land ORDER BY LandNavn");
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $land[] = $row;
}

// Sett valgt land basert på GET eller POST
$selectedLandID = null;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $selectedLandID = (int)$_POST['landID'];
} elseif (isset($_GET['land'])) {
    $selectedLandID = (int)$_GET['land'];
}

// Håndter oppdatering av stemmer
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $artistID = (int)$_POST['artistID'];
    $antallStemmer = (int)$_POST['antallStemmer'];

    $check = sqlsrv_query($conn, "SELECT 1 FROM Stemmegivning WHERE LandID = $selectedLandID AND ArtistID = $artistID");
    if (sqlsrv_fetch($check)) {
        $stmt = sqlsrv_prepare($conn, "UPDATE Stemmegivning SET AntallStemmer = ? WHERE LandID = ? AND ArtistID = ?", [$antallStemmer, $selectedLandID, $artistID]);
    } else {
        $stmt = sqlsrv_prepare($conn, "INSERT INTO Stemmegivning (AntallStemmer, LandID, ArtistID) VALUES (?, ?, ?)", [$antallStemmer, $selectedLandID, $artistID]);
    }
    sqlsrv_execute($stmt);

    // Refresh data etter oppdatering
    header("Location: " . $_SERVER['PHP_SELF'] . "?land=$selectedLandID");
    exit;
}

// Hent artister + stemmer for valgt land
$artister = [];
if ($selectedLandID) {
    $stmt = sqlsrv_prepare($conn, "
        SELECT a.ArtistID, a.ArtistNavn, ISNULL(s.AntallStemmer, 0) AS AntallStemmer
        FROM Artister a
        LEFT JOIN Stemmegivning s ON a.ArtistID = s.ArtistID AND s.LandID = ?
        ORDER BY a.ArtistNavn", [$selectedLandID]);
    sqlsrv_execute($stmt);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $artister[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Administrasjon – Endre stemmer</title>
    <style>
        body {
            font-family: sans-serif;
            background: #eef;
            padding: 30px;
        }
        .container {
            background: white;
            padding: 30px;
            max-width: 800px;
            margin: auto;
            border-radius: 10px;
        }
        h1, h2 {
            color: #6c3483;
        }
        select, input[type="number"], button {
            padding: 8px;
            font-size: 1em;
            margin: 5px 0;
        }
        button {
            background-color: #6c3483;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5b2c6f;
        }
        a.back {
            display: inline-block;
            margin-top: 20px;
            color: #6c3483;
            text-decoration: none;
        }
        .artist-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .artist-name {
            flex-basis: 40%;
            font-weight: bold;
        }
        form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-basis: 55%;
            margin: 0;
        }
        input[type="number"] {
            width: 80px;
            padding: 6px;
        }
            footer {
        margin-top: 60px;
        border-top: 2px solid #ddd;
        padding-top: 25px;
        color: #666;
        font-size: 0.95rem;
        line-height: 1.5;
        align-items: center;
        text-align: center;
    }
    footer h2 {
        color: #6c3483;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    </style>
</head>
<body>
<div class="container">
    <h1>Administrasjon – Endre stemmer</h1>

    <form method="get">
        <label for="land">Velg land:</label>
        <select name="land" onchange="this.form.submit()">
            <option value="">-- Velg land --</option>
            <?php foreach ($land as $l): ?>
                <option value="<?= $l['LandID'] ?>" <?= $selectedLandID == $l['LandID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($l['LandNavn']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selectedLandID): ?>
        <h2>Rediger stemmer for <?= htmlspecialchars($land[array_search($selectedLandID, array_column($land, 'LandID'))]['LandNavn']) ?></h2>

        <?php foreach ($artister as $a): ?>
            <div class="artist-row">
                <div class="artist-name"><?= htmlspecialchars($a['ArtistNavn']) ?></div>
                <form method="post">
                    <input type="hidden" name="artistID" value="<?= $a['ArtistID'] ?>">
                    <input type="hidden" name="landID" value="<?= $selectedLandID ?>">
                    <input type="number" name="antallStemmer" min="0" value="<?= $a['AntallStemmer'] ?>" required>
                    <button type="submit" name="update">Lagre</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="index.php" class="back">← Tilbake til oversikt</a>
</div>
            <br>
            <br>
            <br>
            <br>
        <footer>
        <h2>Kontaktinformasjon</h2>
        <p>Telefon: 123 45 322</p>
        <p>E-post: valg@kommune.no</p>
        <p>Adresse: 1617 Fredrikstad</p>
    </footer>
</body>
</html>

<?php
if ($conn) {
    sqlsrv_close($conn);
}
?>