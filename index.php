<?php  
session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';
session_unset();

function showError($error){
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm){
    return $formName === $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <img src="aircraft-airplane-airline-logo-or-label-journey-vector-21441986_1_-removebg-preview.png" alt="SkyReserve Logo" class="logo-img">
  <title>SkyReserve | Login & Register</title>
  
  <!-- Styles for login/register form -->
  <link rel="stylesheet" href="style.css">

  <!-- Styles for logo animation (ONLY for logo) -->
  <link rel="stylesheet" href="aero1.css">

  <style>
  /* Logo screen styles */
.logo-screen {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  flex-direction: column;  /* Ensures the logo and text are stacked vertically */
  opacity: 1;
  animation: fadeOut 1s ease forwards;
  animation-delay: 10s;
}

.logo-screen.hidden {
  display: none;
}

@keyframes fadeOut {
  to {
    opacity: 0;
    visibility: hidden;
  }
}

/* Hide main content initially */
.container {
  display: none;
  opacity: 0;
}

.container.visible {
  display: block;
  animation: fadeIn 1s ease forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Plane image sliding in smoothly from the right */
.logo-img {
  width: 300px;
  margin-bottom: 20px;  /* Added margin to create space between the logo image and text */
  opacity: 0;
  transform: translateX(200px);
  animation: slideInRight 2s ease-in-out forwards,
             float 3s ease-in-out infinite;
  animation-delay: 1s, 3s;
  z-index: 0;
  position: relative;
  
  filter: brightness(1000) saturate(90) contrast(1000) hue-rotate(1deg);
}

@keyframes slideInRight {
  0% {
    transform: translateX(200px);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes float {
  0% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
  100% {
    transform: translateY(0);
  }
}

/* SkyReserve text styles */
.logo-text {
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 2.5rem;
  font-weight: bold;
}

.logo-text span {
  display: inline-block;
  opacity: 0;
  animation: fadeInText 2s ease forwards;
}

/* Left side letters */
.logo-text .left {
  animation: slideFromLeft 0.8s forwards;
}

.logo-text .right {
  animation: slideFromRight 0.8s forwards;
}

.logo-text .center {
  animation: fadeInCenter 1s forwards;
}

/* Stagger animation delays */
.logo-text span:nth-child(1) { animation-delay: 0.8s; }
.logo-text span:nth-child(2) { animation-delay: 0.7s; }
.logo-text span:nth-child(3) { animation-delay: 0.6s; }
.logo-text span:nth-child(4) { animation-delay: 0.5s; }
.logo-text span:nth-child(5) { animation-delay: 0.5s; }
.logo-text span:nth-child(6) { animation-delay: 0.6s; }
.logo-text span:nth-child(7) { animation-delay: 0.7s; }
.logo-text span:nth-child(8) { animation-delay: 0.8s; }
.logo-text span:nth-child(9) { animation-delay: 0.9s; }
.logo-text span:nth-child(10) { animation-delay: 1.1s; }

/* Animations */
@keyframes slideFromLeft {
  0% { transform: translateX(-100px); opacity: 0; }
  100% { transform: translateX(0); opacity: 1; }
}

@keyframes slideFromRight {
  0% { transform: translateX(100px); opacity: 0; }
  100% { transform: translateX(0); opacity: 1; }
}

@keyframes fadeInCenter {
  from { opacity: 0; }
  to   { opacity: 1; }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }}

</style>

</head>
<body>

  <!-- Logo Screen -->
  <div class="logo-screen" id="logoScreen">
    
    <!-- SkyReserve animated text (uses aero1.css) -->
    <div class="logo-text">
      <span class="left">S</span>
      <span class="left">k</span>
      <span class="left">y</span>
      <span class="left">R</span>
      <span class="center">e</span>
      <span class="right">s</span>
      <span class="right">e</span>
      <span class="right">r</span>
      <span class="right">v</span>
      <span class="right">e</span>
    </div>
  </div>

  <!-- Main Content (Login & Register Form) -->
  <div class="container" id="mainContent">
    <!-- Login Form -->
    <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
      <form action="login_register.php" method="post">
        <h2>Login</h2>
        <?= showError($errors['login']); ?>
        <input type="email" id="email" name="email" placeholder="Email" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
      </form>
    </div>

    <!-- Register Form -->
    <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
      <form action="login_register.php" method="post">
        <h2>Register</h2>
        <?= showError($errors['register']); ?>
        <input type="text" id="username" name="username" placeholder="Username" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <input type="text" id="name" name="name" placeholder="Full Name" required>
        <input type="email" id="email" name="email" placeholder="Email" required>
        <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>
        <button type="submit" name="register">Register</button>
        <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
      </form>
    </div>
  </div>

  <script>
    // Show login/register form after 7 seconds
    window.addEventListener("DOMContentLoaded", () => {
      setTimeout(() => {
        document.getElementById("logoScreen").classList.add("hidden");
        document.getElementById("mainContent").classList.add("visible");
      }, 7000); // 7 seconds
    });

    // Toggle between login and register forms
    function showForm(formId) {
      document.getElementById("login-form").classList.remove("active");
      document.getElementById("register-form").classList.remove("active");
      document.getElementById(formId).classList.add("active");
    }
  </script>

</body>
</html>
