<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['UserID']) || !isset($_SESSION['passengers']) || !isset($_GET['flightNumber']) || !isset($_GET['pnr'])) {
    echo "Session expired or missing data. Please start again.";
    exit();
}

$flightNumber = $_GET['flightNumber'];
$journeyDate = $_GET['journeyDate'];
$pnrNumber = $_GET['pnr'];
$flightClass = $_GET['class'];
$fromAirport = $_GET['fromAirport'] ?? '';
$toAirport = $_GET['toAirport'] ?? '';
$passengers = $_SESSION['passengers'];

$farePerPassenger = $totalFare = $distance = 0;

// Fetch flight distance
$stmt = $conn->prepare("SELECT Distance_travelled FROM flights WHERE Flight_Number = ?");
$stmt->bind_param("s", $flightNumber);
$stmt->execute();
$result = $stmt->get_result();
$flight = $result->fetch_assoc();
$stmt->close();

if ($flight) {
    $distance = (float)$flight['Distance_travelled'];
    switch ($flightClass) {
        case 'Economy': $farePerPassenger = (3 * $distance) + 200; break;
        case 'Premium Economy': $farePerPassenger = (4 * $distance) + 300; break;
        case 'Business': $farePerPassenger = (6 * $distance) + 500; break;
        case 'First': $farePerPassenger = (8 * $distance) + 700; break;
    }
    $totalFare = $farePerPassenger * count($passengers);
} else {
    echo "Invalid flight details.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirmPayment'])) {
    $paymentMethod = $_POST['paymentMethod'];
    $userID = $_SESSION['UserID'];

    foreach ($passengers as $p) {
        $stmt = $conn->prepare("SELECT Cabin_Code, Seat_Number FROM Seats_Available WHERE Flight_Number = ? AND ClassType = ? LIMIT 1");
        $stmt->bind_param("ss", $flightNumber, $flightClass);
        $stmt->execute();
        $result = $stmt->get_result();
        $seat = $result->fetch_assoc();
        $stmt->close();

        if ($seat) {
            $stmt = $conn->prepare("INSERT INTO Seats_Booked (Flight_Number, Class, Cabin_Code, Seat_Number, PNR, Passenger_ID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $flightNumber, $flightClass, $seat['Cabin_Code'], $seat['Seat_Number'], $pnrNumber, $p['Passenger_No']);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM Seats_Available WHERE Flight_Number = ? AND Cabin_Code = ? AND Seat_Number = ?");
            $stmt->bind_param("sss", $flightNumber, $seat['Cabin_Code'], $seat['Seat_Number']);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("INSERT INTO Passengers (UserID, Passenger_No, PNR, Name, Age, Gender, Class, Flight_Number, Journey_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssssss", $userID, $p['Passenger_No'], $pnrNumber, $p['Name'], $p['Age'], $p['Gender'], $flightClass, $flightNumber, $journeyDate);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO Tickets (PNR, UserID, Flight_Number, Journey_Date, Booking_Date, Source, Destination) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("ssssss", $pnrNumber, $userID, $flightNumber, $journeyDate, $fromAirport, $toAirport);
    $stmt->execute();
    $stmt->close();

    unset($_SESSION['passengers'], $_SESSION['passenger_count'], $_SESSION['pnr']);

    echo "<div style='background-color:#1F2833; color:#90ee90; font-family:sans-serif; padding:30px; text-align:center; border:2px solid #66FCF1; border-radius:12px; max-width:600px; margin:100px auto;'>
            <h2>✅ Payment Successful!</h2>
            <p>Your Ticket has been Booked Successfully.</p>
            <p><strong style='color:#ff5722;'>PNR: $pnrNumber</strong></p>
            <a href='ticket.php?pnr=$pnrNumber&flightNumber=$flightNumber&journeyDate=$journeyDate&class=$flightClass&fromAirport=$fromAirport&toAirport=$toAirport' 
               style='display:inline-block; margin-top:20px; padding:12px 20px; background-color:#66FCF1; color:#0B0C10; font-weight:bold; text-decoration:none; border-radius:8px;'>
               🎫 Download Ticket
            </a>
          </div>";
    exit();
}
?>

<!-- Include the same HTML/CSS for flight + passenger display and payment form here (unchanged from previous post) -->
<!-- Keep this portion identical to what you already had in payment UI. -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Payment</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0B0C10;
            color: #66FCF1;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #1F2833;
            padding: 35px;
            border-radius: 12px;
            border: 1px solid #66FCF1;
            box-shadow: 0 0 30px rgba(102, 252, 241, 0.3);
        }
        h2, h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #45A29E;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #0B0C10;
            color: #FF9800;
        }
        .cost-card {
            background-color: #0B0C10;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #45A29E;
            margin-bottom: 25px;
        }
        select, input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background-color: #0B0C10;
            color: white;
            border: 1px solid;
            border-radius: 6px;
        }
        button {
            width: 100%;
            margin-top: 30px;
            padding: 14px;
            font-size: 16px;
            background-color: #66FCF1;
            color: black;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: orangered;
            color: white;
        }
        .method-input {
            display: none;
        }
    </style>
    <script>
        function showFields() {
            const method = document.getElementById("paymentMethod").value;
            const inputs = document.querySelectorAll('.method-input');
            inputs.forEach(div => div.style.display = 'none');
            if (method === 'UPI') document.getElementById('upiField').style.display = 'block';
            else if (method === 'Card') document.getElementById('cardField').style.display = 'block';
            else if (method === 'Net Banking') document.getElementById('netField').style.display = 'block';
            else if (method === 'Digital Wallet') document.getElementById('walletField').style.display = 'block';
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Flight Payment</h2>

    <h3>✈️ Flight Details</h3>
    <table>
        <tr><th>Flight Number</th><td><?= htmlspecialchars($flightNumber) ?></td></tr>
        <tr><th>From</th><td><?= htmlspecialchars($fromAirport) ?></td></tr>
        <tr><th>To</th><td><?= htmlspecialchars($toAirport) ?></td></tr>
        <tr><th>Date</th><td><?= htmlspecialchars($journeyDate) ?></td></tr>
        <tr><th>Class</th><td><?= htmlspecialchars($flightClass) ?></td></tr>
    </table>

    <h3>👤 Passenger Details</h3>
    <table>
        <tr><th>#</th><th>Name</th><th>Age</th><th>Gender</th><th>ID</th></tr>
        <?php foreach ($passengers as $index => $p): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($p['Name']) ?></td>
                <td><?= htmlspecialchars($p['Age']) ?></td>
                <td><?= htmlspecialchars($p['Gender']) ?></td>
                <td><?= htmlspecialchars($p['Passenger_No']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="cost-card">
        <h3>💰 Distance & Fare</h3>
        <p><strong>Distance:</strong> <?= $distance ?> km</p>
        <p><strong>Fare per Passenger:</strong> ₹<?= $farePerPassenger ?></p>
        <p><strong>Total Fare (<?= count($passengers) ?> Passengers):</strong> ₹<?= $totalFare ?></p>
    </div>

    <form method="POST">
        <label for="paymentMethod">Select Payment Method:</label>
        <select id="paymentMethod" name="paymentMethod" onchange="showFields()" required>
            <option value="">-- Select --</option>
            <option>UPI</option>
            <option>Card</option>
            <option>Net Banking</option>
            <option>Digital Wallet</option>
        </select>

        <div id="upiField" class="method-input">
            <label for="upi">UPI ID:</label>
            <input type="text" id="upi" name="upi" placeholder="example@upi">
        </div>

        <div id="cardField" class="method-input">
            <label for="card">Card Number:</label>
            <input type="text" id="card" name="card" placeholder="1234 5678 9012 3456">
        </div>

        <div id="netField" class="method-input">
            <label for="bank">Bank Name:</label>
            <input type="text" id="bank" name="bank" placeholder="HDFC, SBI, ICICI...">
        </div>

        <div id="walletField" class="method-input">
            <label for="wallet">Wallet Provider:</label>
            <input type="text" id="wallet" name="wallet" placeholder="PhonePe, Paytm, Amazon Pay...">
        </div>

        <button type="submit" name="confirmPayment">Confirm & Pay ₹<?= $totalFare ?></button>
    </form>
</div>
</body>
</html>
