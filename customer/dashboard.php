<?php
session_start();

// Optional: Restrict access if not logged in or not admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html ng-app="sample" ng-controller="eoo">
<head>
  <title>Welcome</title>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-animate.js"></script>
  <script src="scripts.js"></script>
  <script>
    window.phpUsername = "<?php echo htmlspecialchars($username); ?>";
    window.phpRole= "<?php echo htmlspecialchars($role); ?>";
    if(window.phpUsername && window.phpRole) {
        sessionStorage.setItem('username', window.phpUsername);
        sessionStorage.setItem('role', window.phpRole);
    }
  </script>
  <style>
    div.font {
      color: black;
      font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
      font-size: 25px;
      text-transform: uppercase;
    }
    div.font1 {
      color: black;
      font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
      font-size: 40px;
      text-transform: uppercase;
    }
    .float {
      float: right;
    }
    .back {
      border-style: dotted;
      border-radius: 10px;
      padding-left: 20px;
      padding-right: 20px;
    }
    #menu {
      transition: opacity 0.5s ease-in-out;
      opacity: 1;
    }
    #menu.ng-hide {
      opacity: 0;
    }
    nav {
      position: relative;
      width: 850px;
      height: 50px;
      border-radius: 8px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
      overflow: hidden;
      margin-left: auto;
    }
    nav a {
      position: relative;
      font-size: 20px;
      font-weight: 500;
      color: black;
      text-decoration: none;
      padding: 0 25px;
      z-index: 1;
      height: 100%;
      display: flex;
      align-items: center;
      transition: color 0.3s ease;
      min-width: 130px;   /* ← Add this */
      justify-content: center;
    }
    nav a:hover {
      color: white;
    }
    nav span {
      position: absolute;
      top: 0;
      left: 720px;
      width: 130px;
      height: 100%;
      background: linear-gradient(45deg, #4f1919, #ff3333);
      border-radius: 8px;
      transition: left 0.4s ease;
      z-index: 0;
    }
    nav a:nth-child(1):hover ~ span {
      left: 0px;
    }
    nav a:nth-child(2):hover ~ span {
      left: 180px;
    }
    nav a:nth-child(3):hover ~ span {
      left: 360px;
    }
    nav a:nth-child(4):hover ~ span {
      left: 540px;
    }
    nav a:nth-child(5):hover ~ span {
      left: 720px;
    }
    .nav-wrapper {
      display: flex;
      align-items: center;
      background-color: white;
      border-radius: 8px;
      height: 50px; 
      padding: 0 15px;
      box-sizing: border-box;
    }
    .company-logo {
      height: 40px;       /* Adjust logo height */
      margin-right: 20px; /* Space between logo and nav */
      user-select: none;  /* Optional: avoid text selection on logo */
    }
    #dashboard { background-color: #f9f9f9; }
    #addRate { background-color: #e8f4f8; }
    #manageUsers { background-color: #f4e8f8; }
  </style>
</head>
<body bgcolor="#FAF9F6">
  <div class="nav-wrapper">
    <img src="images/logo.png" alt="Company Logo" class="company-logo" />
    <div class="font">
      Welcome <?php echo htmlspecialchars($username); ?>!
    </div>
    <nav>
      <a href="http://localhost/ditto/customer/addusers.php">SEARCH</a>
      <a href="http://localhost/ditto/customer/booking.php">TRACK</a>
      <a href="http://localhost/ditto/customer/rate.php">UPCOMING</a>
      <a href="http://localhost/ditto/customer/invoice.php">MY AGENTS</a>
      <a href="http://localhost/ditto/customer/dashboard.php" style="text-transform:uppercase"><?php echo htmlspecialchars($username); ?></a>
      <span></span>
    </nav>
  </div>

  <section id="dashboard">
    <h2>Dashboard</h2>
    <p>This is the dashboard section.</p>
  </section>
</body>
</html>
