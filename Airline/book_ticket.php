<?php
session_start();
require_once 'config.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}

$flightResults = [];
$airports = [];

// Load airports from CSV
if (($handle = fopen("airports.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $airports[] = ["code" => $data[0], "name" => $data[1]];
    }
    fclose($handle);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fromAirport = $_POST["fromAirport"];
    $toAirport = $_POST["toAirport"];
    $flightClass = $_POST["flightClass"];
    $departureDate = $_POST["departureDate"];

    $dayOfWeek = ucfirst(strtolower(date('l', strtotime($departureDate))));

    $stmt = $conn->prepare("
        SELECT * FROM Flights 
        WHERE Source_Airport = ? 
        AND Destination_Airport = ? 
        AND FIND_IN_SET(?, Operating_Days) > 0
    ");
    $stmt->bind_param("sss", $fromAirport, $toAirport, $dayOfWeek);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $flightResults[] = $row;
        }
    } else {
        $error_message = "No flights available for the selected criteria!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Flight</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0B0C10;
            margin: 0;
            padding: 0;
            color: #66FCF7;
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
            max-width: 1200px;
            margin: 200px auto;
            padding: 30px;
            background: #1F2833;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.4);
            box-shadow: 0 0 20px rgba(102, 252, 241, 0.2); /* Aqua Blue glow */
    border: 1px solid #45A29E; /* Teal Blue border */
}
        

        h2, h3 {
            text-align: center;
            color: #66FCF1;
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 500;
            margin-bottom: 6px;
            color: #C5C6C7;
        }

        select, input[type="date"] {
            padding: 10px;
            border: 1px solid #45A29E;
            background-color: #0B0C10;
            color: #C5C6C7;
            border-radius: 6px;
            width: 250px;
        }

        button {
            padding: 10px 20px;
            background-color: #45A29E;
            color: #0B0C10;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            margin-top: 15px;
        }

        button:hover {
            background-color: #66FCF1;
            color:#0B0C10;
        }

        table {
            width: 100%;
            background-color :#0B0C10;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #45A29E;
            color: #C5C6C7;
        }

        table th {
            background-color: #66FCF1;
            color: #0B0C10;
        }

        .error-message {
            color: #FF6B6B;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<!-- Top Navigation Buttons -->
<div class="top-buttons">
    <a href="index.php">
        <button>🏠 Home</button>
    </a>
    <a href="dashboard.php">
        <button>📊 Dashboard</button>
    </a>
</div>
<body>
    <div class="container">
        <h2>FLIGHT SEARCH</h2>
        <form method="POST">
            <div class="form-group">
                <label>From</label>
                <select name="fromAirport" required>
                    <?php foreach ($airports as $airport): ?>
                        <option value="<?= htmlspecialchars($airport['code']) ?>">
                            <?= htmlspecialchars($airport['name']) ?> (<?= htmlspecialchars($airport['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>To</label>
                <select name="toAirport" required>
                    <?php foreach ($airports as $airport): ?>
                        <option value="<?= htmlspecialchars($airport['code']) ?>">
                            <?= htmlspecialchars($airport['name']) ?> (<?= htmlspecialchars($airport['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Class</label>
                <select name="flightClass" required>
                    <option value="All">All Classes</option>
                    <option value="Economy">Economy</option>
                    <option value="PremiumEconomy">Premium Economy</option>
                    <option value="Business">Business</option>
                    <option value="FirstClass">First Class</option>
                </select>
            </div>

            <div class="form-group">
                <label>Departure Date</label>
                <input type="date" name="departureDate" required>
            </div>

            <button type="submit">Search Flights</button>
        </form>

        <?php if (!empty($flightResults)) : ?>
            <h3>Available Flights</h3>
            <table>
                <tr>
                    <th>Flight Number</th>
                    <th>Flight Name</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Economy</th>
                    <th>Premium Economy</th>
                    <th>Business</th>
                    <th>First Class</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($flightResults as $flight): ?>
                    <tr>
                        <td><?= htmlspecialchars($flight['Flight_Number']) ?></td>
                        <td><?= htmlspecialchars($flight['Airport_Name']) ?></td>
                        <td><?= htmlspecialchars($flight['Departure_Time']) ?></td>
                        <td><?= htmlspecialchars($flight['Arrival_Time']) ?></td>
                        <td><?= $flight['Economy_Class_Seats'] ?></td>
                        <td><?= $flight['Premium_Economy_Class_Seats'] ?></td>
                        <td><?= $flight['Business_Class_Seats'] ?></td>
                        <td><?= $flight['First_Class_Seats'] ?></td>
                        <td>
                            <form method="GET" action="passenger_details.php">
                                <input type="hidden" name="flightNumber" value="<?= htmlspecialchars($flight['Flight_Number']) ?>">
                                <input type="hidden" name="fromAirport" value="<?= htmlspecialchars($fromAirport) ?>">
                                <input type="hidden" name="toAirport" value="<?= htmlspecialchars($toAirport) ?>">
                                <input type="hidden" name="journeyDate" value="<?= htmlspecialchars($departureDate) ?>">
                                <button type="submit">Book Now</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif (isset($error_message)) : ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
