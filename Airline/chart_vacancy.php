<?php
require_once 'config.php';

$availableSeats = [];
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flightNumber = $_POST['flightNumber'];
    $journeyDate = $_POST['journeyDate']; // currently unused but kept for future use

    $sql = "SELECT ClassType, COUNT(*) AS vacant_count 
            FROM Seats_Available 
            WHERE Flight_Number = ? 
            GROUP BY ClassType";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $flightNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $availableSeats[$row['ClassType']] = $row['vacant_count'];
        }
    } else {
        $errorMessage = "No seats found for the given flight number.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Seats - SkyReserve</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: #0B0C10;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            color: #C5C6C7;
        }

        .skyreserve-bg-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18vw;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.05);
            z-index: 0;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
            text-align: center;
            width: 100%;
        }

        .top-buttons {
            text-align: center;
            margin-bottom: 30px;
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
            position: relative;
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            background: #1F2833;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(102, 252, 241, 0.2);
            border: 1px solid #45A29E;
            z-index: 1;
        }

        h2 {
            text-align: center;
            color: #66FCF1;
            margin-bottom: 25px;
        }

        input[type="text"],
        input[type="date"] {
            width: 95%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #45A29E;
            border-radius: 6px;
            background-color: #0B0C10;
            color: #C5C6C7;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color:rgb(233, 77, 25);
            margin-bottom: 15px;
            color: #0B0C10;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #ff5722;
            color: #0B0C10;
        }

        .results {
            margin-top: 25px;
        }

        table {
            width: 100%;
            background-color: black;
            border-collapse: collapse;
            color: white;
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

        .error {
            margin-top: 20px;
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="skyreserve-bg-text">SkyReserve</div>

    <!-- Top Navigation Buttons -->
    <div class="top-buttons">
        <a href="index.php">
            <button>🏠 Home</button>
        </a>
        <a href="dashboard.php">
            <button>📊 Dashboard</button>
        </a>
    </div>

    <div class="container">
        <h2>Flight Seat Availability</h2>
        <form method="POST">
            <input type="text" name="flightNumber" placeholder="Flight Number*" required />
            <input type="date" name="journeyDate" value="<?= date('Y-m-d') ?>" required />
            <button type="submit">Get Flight Chart</button>
        </form>

        <?php if (!empty($availableSeats)): ?>
            <div class="results">
                <h3>Vacant Seats by Class</h3>
                <table>
                    <tr>
                        <th>Class Type</th>
                        <th>Vacant Seats</th>
                    </tr>
                    <?php foreach ($availableSeats as $class => $count): ?>
                        <tr>
                            <td><?= htmlspecialchars($class) ?></td>
                            <td><?= htmlspecialchars($count) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="error"><?= $errorMessage ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
