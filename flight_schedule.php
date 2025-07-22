<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}

$flightNumber = "";
$flightData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flightNumber = $_POST["flightNumber"];

    $stmt = $conn->prepare("SELECT * FROM Flights WHERE Flight_Number = ?");
    $stmt->bind_param("s", $flightNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $flightData = $result->fetch_assoc();
    } else {
        $error_message = "Flight not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Schedule</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #0B0C10;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            color: #C5C6C7;
        }

        .top-buttons {
            position: fixed;
    top: 15px;
    right: 580px;
    z-index: 1000;
        }

        .top-buttons a {
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }

        .top-buttons button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: rgb(70, 188, 182);
            color: black;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .top-buttons button:hover {
            background-color: #66FCF1;
        }

        .container {
            margin: 50pxauto;
            max-width: 1300px;
            padding: 20px;
            background: #1F2833;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(102, 252, 241, 0.2);
            border: 1px solid #45A29E;
        }

        h2, h3 {
            text-align: center;
            color: #66FCF1;
        }

        .form-section {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            width: 250px;
            border-radius: 6px;
            border: 1px solid #45A29E;
            background-color: #0B0C10;
            color: #C5C6C7;
        }

        button[type="submit"] {
            padding: 10px 20px;
            background-color: rgb(70, 188, 182);
            color: #0B0C10;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 10px;
        }

        button[type="submit"]:hover {
            background-color: #66FCF1;
        }

        table {
            width: 100%;
            background-color: #0B0C10;
            border-collapse: collapse;
            color: #66FCF1;
        }

        th, td {
            padding: 12px;
            border: 1px solid #45A29E;
            text-align: center;
        }

        th {
            background-color: #66FCF1;
            color: black;
        }
    </style>
</head>
<body>

<!-- Top Navigation Buttons -->
<div class="top-buttons">
    <a href="index.php">
        <button>🏠 Home</button>
    </a>
    <a href="dashboard.php">
        <button>📊 Dashboard</button>
    </a>
</div>

<!-- Main Flight Schedule Section -->
<div class="container">
    <h2>FLIGHT Details</h2>

    <div class="form-section">
        <form method="POST">
            <input type="text" name="flightNumber" placeholder="Enter Flight Number" required value="<?= htmlspecialchars($flightNumber) ?>">
            <button type="submit">SUBMIT</button>
        </form>
    </div>

    <?php if (!empty($flightData)) : ?>
        <h3>Schedule for Flight Number: <?= htmlspecialchars($flightNumber) ?></h3>
        <table>
            <tr>
                <th>Flight Name</th>
                <th>Source</th>
                <th>Destination</th>
                <th>Departure Time</th>
                <th>Arrival Time</th>
                <th>Economy Seats</th>
                <th>Premium Economy</th>
                <th>Business Class</th>
                <th>First Class</th>
                <th>Operating Days</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($flightData['Airport_Name']) ?></td>
                <td><?= htmlspecialchars($flightData['Source_Airport']) ?></td>
                <td><?= htmlspecialchars($flightData['Destination_Airport']) ?></td>
                <td><?= htmlspecialchars($flightData['Departure_Time']) ?></td>
                <td><?= htmlspecialchars($flightData['Arrival_Time']) ?></td>
                <td><?= htmlspecialchars($flightData['Economy_Class_Seats']) ?></td>
                <td><?= htmlspecialchars($flightData['Premium_Economy_Class_Seats']) ?></td>
                <td><?= htmlspecialchars($flightData['Business_Class_Seats']) ?></td>
                <td><?= htmlspecialchars($flightData['First_Class_Seats']) ?></td>
                <td><?= htmlspecialchars($flightData['Operating_Days']) ?></td>
            </tr>
        </table>
    <?php elseif (isset($error_message)) : ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
</div>

</body>
</html>
