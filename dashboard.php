<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
     body {
    font-family: 'Segoe UI', sans-serif;
    background: #0B0C10; /* Dark background */
    margin: 0;
    padding: 0;
}

.skyreserve-bg-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 16vw; /* Responsive: takes 18% of viewport width */
  font-weight: bold;
  color: rgba(255, 255, 255, 0.05);
  z-index: 0;
  pointer-events: none;
  user-select: none;
  white-space: nowrap;
  text-align: center;
  width: 100%;
}

.dashboard-container {
    max-width: 400px;
    margin: 50px auto;
    padding: 30px;
    background: linear-gradient(to top, #0B0C10, #1F2833); 
    border-radius: 12px;
    border: 1px solid #45A29E;
    box-shadow: 0 0 20px rgba(102, 252, 241, 0.2); 
}

h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #C5C6C7; /* Soft gray for heading */
}

.option {
    background-color: #C5C6C7; /* Light gray boxes */
    padding: 15px 20px;
    margin-bottom: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #0B0C10; /* Dark text */
    transition: 0.3s ease;
    font-size: 18px;
    font-weight: 500;
}

.option i {
    margin-right: 12px;
    font-size: 20px;
    color:blueviolet; /* Aqua icon */
}

.option:hover {
    background-color: #66FCF1; /* Aqua hover background */
    color: #0B0C10; /* Dark text on hover */
}

.option:hover i {
    color: #0B0C10;
}


    </style>
     <!-- BACKGROUND TITLE TEXT -->
 <div class="skyreserve-bg-text">SkyReserve</div>
</head>
<body>

<div class="dashboard-container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['Name']) ?>!</h2>
    <a href="flight_schedule.php" class="option"><i class="fa-solid fa-plane"></i> Flight Details</a>
    <a href="book_ticket.php" class="option"><i class="fa-solid fa-ticket"></i> Book Ticket</a>
    <a href="pnr_enquiry.php" class="option"><i class="fa-solid fa-magnifying-glass"></i> PNR Enquiry</a>
    <a href="chart_vacancy.php" class="option"><i class="fa-solid fa-table-list"></i> Avaiable Seats</a>
    <a href="cancel_ticket.php" class="option"><i class="fa-solid fa-ban"></i> Cancel Ticket</a>
</div>

</body>
</html>
