<?php
// Bepaal de naam van ons data-bestand
$dataFile = 'data.json';

// --- DEEL 1: DATA OPSLAAN ---
// Controleer of het formulier is verzonden (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Haal de data op uit het formulier
    $temperatuur = $_POST['temperatuur'] ?? 0;
    $vochtigheid = $_POST['vochtigheid'] ?? 0;
    
    // Genereer automatisch een timestamp
    $timestamp = date("Y-m-d H:i:s");

    // Maak een array van de nieuwe meting
    $nieuweMeting = [
        "timestamp" => $timestamp,
        "temperatuur" => (float)$temperatuur,
        "vochtigheid" => (float)$vochtigheid
    ];

    $bestaandeData = [];
    
    // Controleer of het bestand al bestaat en lees de huidige data in
    if (file_exists($dataFile)) {
        $jsonString = file_get_contents($dataFile);
        $bestaandeData = json_decode($jsonString, true) ?? [];
    }

    // Voeg de nieuwe meting toe aan de bestaande data
    $bestaandeData[] = $nieuweMeting;

    // Sla alles weer op in het JSON bestand
    file_put_contents($dataFile, json_encode($bestaandeData, JSON_PRETTY_PRINT));
}

// INLEZEN 
$sensorData = [];
if (file_exists($dataFile)) {
    $jsonString = file_get_contents($dataFile);
    $sensorData = json_decode($jsonString, true) ?? [];
    // Keer de array om
    $sensorData = array_reverse($sensorData); 
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Greenhouse Dashboard</title>
    <style>
        :root {
            --primary-color: #2E7D32; /* Groen thema voor de serre */
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            font-size: 1.2rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="number"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #1b5e20;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #e8f5e9;
            color: var(--primary-color);
        }

        .alert-hot {
            color: #d32f2f;
            font-weight: bold;
        }

        .alert-cold {
            color: #1976d2;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="grid">
        <div class="card">
            <h2>Nieuwe Meting Toevoegen</h2>
            <form method="POST" action="">
                <label for="temperatuur">Temperatuur (°C):</label>
                <input type="number" step="0.1" id="temperatuur" name="temperatuur" required>

                <label for="vochtigheid">Luchtvochtigheid (%):</label>
                <input type="number" step="1" id="vochtigheid" name="vochtigheid" required>

                <button type="submit">Sla Meting Op</button>
            </form>
        </div>

        <div class="card">
            <h2>Recente Data</h2>
            <?php if (empty($sensorData)): ?>
                <p>Er is nog geen data beschikbaar. Voeg een eerste meting toe!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tijdstip (Timestamp)</th>
                            <th>Temperatuur</th>
                            <th>Vochtigheid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        //loop
                        foreach ($sensorData as $data): 
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($data['timestamp']) ?></td>
                                <td>
                                    <?php 
                                    // conditie
                                    $temp = $data['temperatuur'];
                                    if ($temp > 30) {
                                        echo "<span class='alert-hot'>{$temp} °C (Te warm!)</span>";
                                    } elseif ($temp < 15) {
                                        echo "<span class='alert-cold'>{$temp} °C (Te koud!)</span>";
                                    } else {
                                        echo "{$temp} °C (Optimaal)";
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($data['vochtigheid']) ?> %</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>