<?php
require_once 'config.php';

$pnr = '';
$ticket_details = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pnr'])) {
    $pnr = $_POST['pnr'];

    $stmt = $conn->prepare("SELECT p.Passenger_No, p.Name, p.Age, p.Gender, p.Class, p.Flight_Number, p.Journey_Date, f.Airport_Name 
                            FROM Passengers p 
                            JOIN Flights f ON p.Flight_Number = f.Flight_Number
                            WHERE p.PNR = ?");
    $stmt->bind_param("s", $pnr);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ticket_details = $result->fetch_assoc();
    } else {
        $ticket_details = 'No booking found for the entered PNR.';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PNR Inquiry</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0B0C10;
            color: #66FCF1;
            margin: 0;
            padding: 30px;
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
            max-width: 1100px auto;
            margin: 0 auto;
            background-color: #1F2833;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #66FCF1;
            box-shadow: 0 0 20px rgba(102, 252, 241, 0.2);
        }

        .heading {
            text-align: center;
            font-size: 26px;
            margin-bottom: 20px;
            color: #ff5722;
        }

        form {
            text-align: center;
        }

        input[type="text"] {
            width: 70%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #45A29E;
            border-radius: 5px;
            background-color: #0B0C10;
            color:white;
        }

        button[type="submit"] {
            width: 25%;
            padding: 12px;
            font-size: 16px;
            margin-left: 10px;
            background-color: #ff5722;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #e64a19;
        }

        .ticket-details {
            margin-top: 30px;
            padding: 20px;
            background-color: #0B0C10;
            border: 1px solid #66FCF1;
            border-radius: 8px;
        }

        h3 {
            color: #ff5722;
        }

        table {
            width: 100%;
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

        label {
            font-size: 16px;
            color: #45A29E;
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

<!-- Main PNR Inquiry Section -->
<div class="container">
    <div class="heading">PNR Inquiry</div>

    <form action="pnr_enquiry.php" method="POST">
        <label for="pnr">Enter your PNR:</label><br><br>
        <input type="text" id="pnr" name="pnr" required placeholder="Enter PNR" value="<?= htmlspecialchars($pnr) ?>">
        <button type="submit">Check Booking</button>
    </form>

    <?php if ($ticket_details): ?>
        <?php if (is_array($ticket_details)): ?>
            <div class="ticket-details">
                <h3>Booking Details:</h3>
                <table>
                    <tr>
                        <th>Passenger No</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Class</th>
                        <th>Flight Number</th>
                        <th>Journey Date</th>
                        <th>Airline</th>
                    </tr>
                    <tr>
                        <td><?= htmlspecialchars($ticket_details['Passenger_No']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Name']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Age']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Gender']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Class']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Flight_Number']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Journey_Date']) ?></td>
                        <td><?= htmlspecialchars($ticket_details['Airport_Name']) ?></td>
                    </tr>
                </table>
            </div>
        <?php else: ?>
            <div class="ticket-details">
                <p><?= htmlspecialchars($ticket_details) ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
