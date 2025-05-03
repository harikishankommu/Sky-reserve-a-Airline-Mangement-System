<?php
session_start();
require_once 'config.php';

if (isset($_POST['register'])) {
    $userID = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("SELECT Email FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
    } else {
        $stmt = $conn->prepare("INSERT INTO Users (UserID, Name, Email, PasswordHash, Phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $userID, $name, $email, $password, $phone);
        $stmt->execute();

        $_SESSION['active_form'] = 'login'; // redirect to login form after successful registration
    }

    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['PasswordHash'])) {
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Email'] = $user['Email'];
            $_SESSION['Phone'] = $user['Phone'];

            header("Location: dashboard.php");
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}
?>