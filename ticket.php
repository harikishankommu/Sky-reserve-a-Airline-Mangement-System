<?php
session_start();
require_once 'config.php';

$pnr = $_GET['pnr'] ?? '';
$flightNumber = $_GET['flightNumber'] ?? '';
$journeyDate = $_GET['journeyDate'] ?? '';
$flightClass = $_GET['class'] ?? '';
$fromAirport = $_GET['fromAirport'] ?? '';
$toAirport = $_GET['toAirport'] ?? '';

// Get flight details
$stmt = $conn->prepare("SELECT * FROM flights WHERE Flight_Number = ?");
$stmt->bind_param("s", $flightNumber);
$stmt->execute();
$flightInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get passenger details
$stmt = $conn->prepare("
    SELECT DISTINCT p.Name, p.Age, p.Gender, sb.Seat_Number, sb.Cabin_Code
    FROM Passengers p
    LEFT JOIN Seats_Booked sb 
        ON p.Flight_Number = sb.Flight_Number AND p.Passenger_No = sb.Passenger_ID
    WHERE p.PNR = ?
");
$stmt->bind_param("s", $pnr);
$stmt->execute();
$passengers = $stmt->get_result();
$stmt->close();

// Initialize an array to track already displayed passengers
$displayedPassengers = [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Boarding Pass</title>
    <!-- External Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to top, #0B0C10, #1F2833);
            margin: 0;
            padding: 40px 0;
        }

        .boarding-pass {
            display: flex;
            color:black;
            max-width: 750px;
            margin: auto;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .left-section, .right-section {
            padding: 30px;
        }

        .left-section {
            width: 70%;
            border-right: 2px dashed #ccc;
        }

        .right-section {
            width: 30%;
            background:linear-gradient(to top, #0B0C10,rgb(24, 148, 206)); ;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        h2 {
            margin-top: 0;
            font-size: 22px;
            color:rgb(24, 148, 206);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px 30px;
            margin-top: 15px;
        }

        .info-group {
            display: flex;
            flex-direction: column;
        }

        .info-group strong {
            font-weight: 600;
            color: #555;
        }

        .info-group span {
            font-size: 15px;
            color: #000;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 8px 10px;
            border: 1px solid #ccc;
        }

        th {
            background:rgb(24, 148, 206);
            color: white;
        }

        .right-section h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .barcode {
            margin-top: 20px;
            background: white;
            padding: 8px;
            color: black;
            font-family: monospace;
        }

        #qrcode {
            margin-top: 10px;
            background: white;
            padding: 6px;
            display: inline-block;
        }

        .footer-btn {
            text-align: center;
            margin-top: 20px;
        }

        .footer-btn a, .footer-btn button {
            text-decoration: none;
            background:rgb(24, 148, 206);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 5px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .footer-btn, .footer-btn a, .footer-btn button {
                display: none !important;
            }

            .boarding-pass {
                box-shadow: none;
                border: none;
                max-width: 100%;
                margin: 0;
            }
        }

        @media (max-width: 600px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="boarding-pass" id="pass">
    <div class="left-section">
        <h2>🛫 BOARDING PASS</h2>
        <div class="details-grid">
            <div class="info-group"><strong>PNR</strong><span><?= htmlspecialchars($pnr) ?></span></div>
            <div class="info-group"><strong>Flight Number</strong><span><?= htmlspecialchars($flightNumber) ?></span></div>
            <div class="info-group"><strong>Date</strong><span><?= htmlspecialchars($journeyDate) ?></span></div>
            <div class="info-group"><strong>Class</strong><span><?= htmlspecialchars($flightClass) ?></span></div>
            <div class="info-group"><strong>From</strong><span><?= htmlspecialchars($fromAirport) ?></span></div>
            <div class="info-group"><strong>To</strong><span><?= htmlspecialchars($toAirport) ?></span></div>
            <div class="info-group"><strong>Distance</strong><span><?= $flightInfo['Distance_travelled'] ?> km</span></div>
        </div>

        <table>
            <tr>
                <th>Name</th><th>Age</th><th>Gender</th><th>Seat</th><th>Cabin</th>
            </tr>
            <?php
            while ($row = $passengers->fetch_assoc()) {
                // Check for duplicate passenger details
                if (!in_array($row['Name'], $displayedPassengers)) {
                    $displayedPassengers[] = $row['Name']; // Track displayed passengers
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Age']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Gender']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Seat_Number'] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Cabin_Code'] ?? '-') . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>

    <div class="right-section">
        <h3><?= htmlspecialchars($fromAirport) ?> → <?= htmlspecialchars($toAirport) ?></h3>
        <div>
            <strong>Departure</strong>
            <p><?= htmlspecialchars($flightInfo['Departure_Time'] ?? 'N/A') ?></p>
            <strong>Arrival</strong>
            <p><?= htmlspecialchars($flightInfo['Arrival_Time'] ?? 'N/A') ?></p>
            <strong>Status</strong>
            <p><?= htmlspecialchars($flightInfo['Flight_Status'] ?? 'On Time') ?></p>
        </div>
        <div class="barcode"><?= substr($pnr, 0, 10) . rand(1000,9999) ?></div>
        <div id="qrcode"></div>
    </div>
</div>

<div class="footer-btn">
    <a href="dashboard.php">← Back to Dashboard</a>
    <button onclick="downloadPDF()">📥 Download PDF</button>
    <button onclick="window.print()">🖨 Print Boarding Pass</button>
</div>

<script>
    const qrData = "PNR: <?= $pnr ?> | Flight: <?= $flightNumber ?> | From: <?= $fromAirport ?> → <?= $toAirport ?> | Date: <?= $journeyDate ?>";
    QRCode.toCanvas(document.getElementById('qrcode'), qrData, { width: 100 }, function (error) {
        if (error) console.error(error);
    });

    async function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();
        const element = document.getElementById('pass');
        const canvas = await html2canvas(element);
        const imgData = canvas.toDataURL('image/png');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save('boarding_pass_<?= htmlspecialchars($pnr) ?>.pdf');
    }
</script>

</body>
</html>
