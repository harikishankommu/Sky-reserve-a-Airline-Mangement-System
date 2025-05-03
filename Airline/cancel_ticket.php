<?php
session_start();
require_once 'config.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

// Initialize messages
$success_message = $error_message = '';
$cancelled_pnr = '';

// Handle seat cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Security verification failed.";
    } else {
        // Sanitize and validate input
        $pnr = trim($_POST['pnr']);
        $seat_number = trim($_POST['seat_number']);
        $flight_number = trim($_POST['flight_number']);
        $cabin_code = trim($_POST['cabin_code']);
        $class_type = trim($_POST['class_type']);

        // Basic validation
        if (empty($pnr) || empty($seat_number) || empty($flight_number)) {
            $error_message = "Required fields are missing.";
        } else {
            // Start transaction
            $conn->begin_transaction();

            try {
                // Delete from Seats_Booked
                $delete_sql = "DELETE FROM Seats_Booked WHERE PNR = ? AND Flight_Number = ? AND Seat_Number = ?";
                $stmt = $conn->prepare($delete_sql);
                $stmt->bind_param("sss", $pnr, $flight_number, $seat_number);
                $stmt->execute();
                
                // Check if any row was actually deleted
                if ($stmt->affected_rows === 0) {
                    throw new Exception("No matching booking found to cancel.");
                }

                // Insert into Seats_Available
                $insert_sql = "INSERT INTO Seats_Available (Flight_Number, Cabin_Code, Seat_Number, ClassType)
                               VALUES (?, ?, ?, ?)";
                $stmt2 = $conn->prepare($insert_sql);
                $stmt2->bind_param("ssss", $flight_number, $cabin_code, $seat_number, $class_type);
                $stmt2->execute();

                // Commit transaction
                $conn->commit();
                $success_message = "Seat cancelled successfully.";
                $cancelled_pnr = $pnr;
                
                // Store in session for displaying the success message after redirect
                $_SESSION['cancellation_success'] = [
                    'message' => $success_message,
                    'pnr' => $pnr
                ];
                
                // Redirect to prevent form resubmission
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error_message = "Error cancelling seat: " . $e->getMessage();
                error_log("Cancellation Error: " . $e->getMessage());
            } finally {
                // Close statements
                if (isset($stmt)) $stmt->close();
                if (isset($stmt2)) $stmt2->close();
            }
        }
    }
}

// Check for success message from session (after redirect)
if (isset($_SESSION['cancellation_success'])) {
    $success_message = $_SESSION['cancellation_success']['message'];
    $cancelled_pnr = $_SESSION['cancellation_success']['pnr'];
    unset($_SESSION['cancellation_success']); // Clear the message
}

// Fetch user bookings with error handling
$user_id = $_SESSION['UserID'];
$bookings = [];

try {
    $sql = "SELECT SB.PNR, SB.Flight_Number, F.Airport_Name,
                   F.Source_Airport, F.Destination_Airport,
                   T.Journey_Date, SB.Seat_Number, SB.Class, SB.Cabin_Code
            FROM Seats_Booked SB
            JOIN Flights F ON SB.Flight_Number = F.Flight_Number
            JOIN Tickets T ON SB.PNR = T.PNR
            WHERE T.UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    $error_message = "Error retrieving your bookings. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Flight Seat</title>
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
            max-width: 1200px;
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

        .ticket-card {
            background-color: rgba(11, 12, 16, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .ticket-card.cancelled {
            opacity: 0.6;
            background-color: rgba(255, 87, 34, 0.1);
            border-color: var(--warning-color);
        }

        .ticket-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 252, 241, 0.1);
        }

        .ticket-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .ticket-sub {
            color: var(--accent-color);
            margin-top: 8px;
            font-size: 0.9rem;
        }

        .cancel-button {
            background-color: var(--warning-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            max-width: 200px;
        }

        .cancel-button:hover {
            background-color: #e64a19;
        }

        .route-arrow {
            margin: 0 10px;
            font-weight: bold;
            color: var(--accent-color);
        }

        .booking-info {
            margin-top: 15px;
            color: var(--text-light);
            font-size: 0.9rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .back-button {
            max-width: 200px;
            margin: 40px auto 0;
            display: block;
            background-color: var(--warning-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
        }

        .back-button:hover {
            background-color: #e64a19;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .ticket-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .cancel-button {
                max-width: 100%;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Cancel Booked Flight Seat</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert success" id="success-message">
            <?= htmlspecialchars($success_message) ?>
            <div style="margin-top: 10px; font-size: 0.9em;">
                Your ticket (PNR: <?= htmlspecialchars($cancelled_pnr) ?>) has been successfully cancelled.
            </div>
        </div>
        
        <script>
            // Auto-close the cancelled ticket after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const cancelledTickets = document.querySelectorAll('.ticket-card.cancelled');
                    cancelledTickets.forEach(ticket => {
                        ticket.style.display = 'none';
                    });
                    
                    // Hide success message after tickets are hidden
                    document.getElementById('success-message').style.display = 'none';
                }, 5000);
            });
        </script>
    <?php elseif (!empty($error_message)): ?>
        <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <p style="text-align: center;">No flight bookings found.</p>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <div class="ticket-card <?= ($booking['PNR'] === $cancelled_pnr) ? 'cancelled' : '' ?>">
                <div class="ticket-header">
                    <div>
                        <span style="font-size: 1.2rem;"><?= htmlspecialchars(strtoupper($booking['Airport_Name'])) ?></span>
                        <div class="ticket-sub">
                            <?= htmlspecialchars($booking['Source_Airport']) ?> 
                            <span class="route-arrow">→</span> 
                            <?= htmlspecialchars($booking['Destination_Airport']) ?>
                        </div>
                    </div>
                    <div style="color: var(--warning-color); margin-top: 10px;">
                        Flight: <strong><?= htmlspecialchars($booking['Flight_Number']) ?></strong>
                        <br>PNR: <strong><?= htmlspecialchars($booking['PNR']) ?></strong>
                    </div>
                </div>

                <div class="booking-info">
                    <div><strong>Seat:</strong> <?= htmlspecialchars($booking['Seat_Number']) ?></div>
                    <div><strong>Cabin:</strong> <?= htmlspecialchars($booking['Cabin_Code']) ?></div>
                    <div><strong>Class:</strong> <?= htmlspecialchars($booking['Class']) ?></div>
                    <div><strong>Date:</strong> <?= htmlspecialchars(date("D, d M Y", strtotime($booking['Journey_Date']))) ?></div>
                </div>

                <?php if ($booking['PNR'] !== $cancelled_pnr): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this seat?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="pnr" value="<?= htmlspecialchars($booking['PNR']) ?>">
                        <input type="hidden" name="seat_number" value="<?= htmlspecialchars($booking['Seat_Number']) ?>">
                        <input type="hidden" name="flight_number" value="<?= htmlspecialchars($booking['Flight_Number']) ?>">
                        <input type="hidden" name="cabin_code" value="<?= htmlspecialchars($booking['Cabin_Code']) ?>">
                        <input type="hidden" name="class_type" value="<?= htmlspecialchars($booking['Class']) ?>">
                        <button type="submit" class="cancel-button">Cancel Seat</button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; color: var(--warning-color); padding: 10px; font-weight: bold;">
                        [CANCELLED]
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<a href="dashboard.php" class="back-button">⬅ Back to Dashboard</a>

</body>
</html>