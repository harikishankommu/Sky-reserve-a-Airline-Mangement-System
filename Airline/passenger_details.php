<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}

// Get flight info from GET parameters
$flightNumber = $_GET['flightNumber'] ?? '';
$journeyDate = $_GET['journeyDate'] ?? '';
$fromAirport = $_GET['fromAirport'] ?? '';
$toAirport = $_GET['toAirport'] ?? '';

$successMessage = "";
$errorMessage = "";
$maxPassengers = 9;

// Initialize passenger session data
if (!isset($_SESSION['passengers'])) {
    $_SESSION['passengers'] = [];
    $_SESSION['passenger_count'] = 0;
}
$existingCount = $_SESSION['passenger_count'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submitPassenger'])) {
    if ($existingCount >= $maxPassengers) {
        $errorMessage = "Maximum of 9 passengers already added.";
    } else {
        $passengerNo = $existingCount + 1;
        $name = $_POST['name'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        $class = $_POST['class'];

        $_SESSION['passengers'][] = [
            'Passenger_No' => $passengerNo,
            'Name' => $name,
            'Age' => $age,
            'Gender' => $gender,
            'Class' => $class
        ];

        $_SESSION['passenger_count'] = $passengerNo;

        if (!isset($_SESSION['pnr'])) {
            do {
                $generatedPNR = strtoupper(substr(md5(uniqid()), 0, 6));
                $stmt = $conn->prepare("SELECT COUNT(*) FROM Passengers WHERE PNR = ?");
                $stmt->bind_param("s", $generatedPNR);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();
            } while ($count > 0);

            $_SESSION['pnr'] = $generatedPNR;
        }

        $successMessage = "Passenger added successfully!";
        header("Location: passenger_details.php?flightNumber=$flightNumber&journeyDate=$journeyDate&fromAirport=$fromAirport&toAirport=$toAirport");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Details</title>
    <style>
        :root {
            --primary-bg: #0B0C10;
            --secondary-bg: #1F2833;
            --accent-color: #66FCF1;
            --warning-color: #ff5722;
            --border-color: #45A29E;
            --text-light: #C5C6C7;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--primary-bg);
            color: var(--accent-color);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--secondary-bg);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: var(--warning-color);
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .flight-info {
            background-color: rgba(11, 12, 16, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .route-arrow {
            margin: 0 10px;
            font-weight: bold;
            color: var(--accent-color);
        }

        .passenger-count {
            color: var(--warning-color);
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
        }

        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
        }

        .alert.success {
            background-color: rgba(102, 252, 241, 0.1);
            border-left: 4px solid var(--accent-color);
        }

        .alert.error {
            background-color: rgba(255, 87, 34, 0.1);
            border-left: 4px solid var(--warning-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent-color);
        }

        input, select {
            width: 95%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: var(--primary-bg);
            color: var(--accent-color);
            font-size: 16px;
        }

        button {
            background-color: #66FCF1 ;
            color: black;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background-color:orangered;
        }

        .back-button {
            margin: 40px auto 0;
            display: block;
            background-color: transparent;
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            max-width: 200px;
        }

        .back-button:hover {
            background-color: orangered;
            color: white;
        }

        .pnr-box {
            margin-top: 15px;
            padding: 12px;
            border: 2px dashed var(--accent-color);
            border-radius: 10px;
            background-color: rgba(102, 252, 241, 0.05);
            text-align: center;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Passenger Details</h2>

    <div class="flight-info">
        <p><strong>Flight Number:</strong> <?= htmlspecialchars($flightNumber) ?></p>
        <p><strong>Journey Date:</strong> <?= htmlspecialchars($journeyDate) ?></p>
        <p><strong>Route:</strong> <?= htmlspecialchars($fromAirport) ?> <span class="route-arrow">→</span> <?= htmlspecialchars($toAirport) ?></p>

        <?php if (!empty($_SESSION['pnr'])): ?>
            <div class="pnr-box">
                <strong>PNR:</strong> <?= htmlspecialchars($_SESSION['pnr']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="passenger-count">
        Passengers Added: <?= $_SESSION['passenger_count'] ?>/9
    </div>

    <?php if ($errorMessage): ?>
        <div class="alert error"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert success"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if ($_SESSION['passenger_count'] < $maxPassengers): ?>
        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required min="1" max="120">
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="class">Class</label>
                <select id="class" name="class" required>
                    <option value="">Select Class</option>
                    <option value="Economy">Economy</option>
                    <option value="Premium Economy">Premium Economy</option>
                    <option value="Business">Business</option>
                    <option value="First">First Class</option>
                </select>
            </div>

            <button type="submit" name="submitPassenger">Add Passenger</button>
        </form>
    <?php endif; ?>

    <?php if ($_SESSION['passenger_count'] > 0): ?>
        <form action="payment.php" method="GET">
            <input type="hidden" name="flightNumber" value="<?= htmlspecialchars($flightNumber) ?>">
            <input type="hidden" name="journeyDate" value="<?= htmlspecialchars($journeyDate) ?>">
            <input type="hidden" name="fromAirport" value="<?= htmlspecialchars($fromAirport) ?>">
            <input type="hidden" name="toAirport" value="<?= htmlspecialchars($toAirport) ?>">
            <input type="hidden" name="pnr" value="<?= htmlspecialchars($_SESSION['pnr']) ?>">
            <input type="hidden" name="class" value="<?= htmlspecialchars($_SESSION['passengers'][0]['Class'] ?? '') ?>">
            <button type="submit">Proceed to Payment</button>
        </form>
    <?php endif; ?>

    <a href="flight_schedule.php" class="back-button">← Back to Flights</a>
</div>

</body>
</html>
