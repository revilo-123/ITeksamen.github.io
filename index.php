<?php
// Konfigurerer tilkobling til SQL Server-databasen
$serverName = "OLIVERSIN";
$connectionOptions = [
    "Database" => "Valg2",       // Navnet på databasen
    "Uid" => "kantine",          // Brukernavn for pålogging
    "PWD" => "kantine",          // Passord for pålogging
    "CharacterSet" => "UTF-8"    // Tegnsett for kommunikasjon
];

// Oppretter tilkobling til databasen
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Hvis tilkoblingen feiler, vis en feilmelding og stopp scriptet
if ($conn === false) die("Databasefeil: " . print_r(sqlsrv_errors(), true));

// Henter alle land fra tabellen "Land" og lagrer dem i et array
$land = [];
$stmt = sqlsrv_query($conn, "SELECT LandID, LandNavn FROM Land ORDER BY LandNavn");
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $land[] = $row;  // Legger hvert land (ID og navn) inn i $land-arrayet
}

// Henter alle artister fra tabellen "Artister" og lagrer dem i et array
$artister = [];
$stmt = sqlsrv_query($conn, "SELECT ArtistID, ArtistNavn FROM Artister ORDER BY ArtistNavn");
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $artister[] = $row;  // Legger hver artist (ID og navn) inn i $artister-arrayet
}

// Lager et 2D-array som inneholder antall stemmer gitt fra hvert land til hver artist
// Strukturen blir: $stemmer[LandID][ArtistID] = AntallStemmer
$stemmer = [];
$stmt = sqlsrv_query($conn, "SELECT LandID, ArtistID, AntallStemmer FROM Stemmegivning");
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $stemmer[$row['LandID']][$row['ArtistID']] = $row['AntallStemmer'];
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Alle stemmer</title>
    <style>
        /* --- STILSETTING FOR SIDEN --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            padding: 40px 20px 20px 20px;
            color: #333;
            line-height: 1.6;
        }

        /* Stil for knapp til admin-side */
        .admin-button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #6c3483;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(108, 52, 131, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }

        .admin-button:hover {
            background-color: #5b2c6f;
            box-shadow: 0 6px 20px rgba(91, 44, 111, 0.6);
        }

        /* Beholder for hovedinnholdet */
        .container {
            max-width: 1100px;
            margin: auto;
            background: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h1 {
            color: #6c3483;
            font-weight: 700;
            margin-bottom: 40px;
            font-size: 2.4rem;
            border-bottom: 2px solid #6c3483;
            padding-bottom: 10px;
        }

        h2 {
            color: #55307a;
            font-weight: 600;
            margin-top: 50px;
            margin-bottom: 15px;
            font-size: 1.8rem;
            border-left: 6px solid #6c3483;
            padding-left: 12px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        thead th {
            background-color: #dcd0e7;
            color: #4b306e;
            font-weight: 600;
            padding: 14px 12px;
            border-radius: 8px 8px 0 0;
            text-align: left;
            box-shadow: inset 0 -2px 3px rgba(0,0,0,0.1);
        }

        tbody tr {
            background-color: #fafafa;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background-color: #e9e2f1;
        }

        tbody td {
            padding: 14px 12px;
            border-left: 4px solid transparent;
            transition: border-color 0.3s ease;
        }

        tbody tr:hover td {
            border-left: 4px solid #6c3483;
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

<!-- Knapp for å gå til administrasjonssiden -->
<a href="admin.php" class="admin-button">Gå til administrasjon</a>

<div class="container">
    <h1>Melodi Grand Prix – Stemmer per land</h1>

    <!-- Løkke gjennom alle land -->
    <?php foreach ($land as $l): ?>
        <h2><?= htmlspecialchars($l['LandNavn']) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Artist</th>
                    <th>Stemmer</th>
                </tr>
            </thead>
            <tbody>
                <!-- For hvert land, vis stemmer til alle artister -->
                <?php foreach ($artister as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['ArtistNavn']) ?></td>
                        <!-- Hent stemmer fra riktig [LandID][ArtistID], vis 0 hvis ikke funnet -->
                        <td><?= $stemmer[$l['LandID']][$a['ArtistID']] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>

<!-- Footer med kontaktinformasjon -->
<footer>
    <h2>Kontaktinformasjon</h2>
    <p>Telefon: 123 45 322</p>
    <p>E-post: valg@kommune.no</p>
    <p>Adresse: 1617 Fredrikstad</p>
</footer>

</body>
</html>

<?php
// Lukker databasen når alt er ferdig
sqlsrv_close($conn);
?>
