<?php
session_start();
require_once 'auth_check.php';

// Optional: Restrict access if not logged in or not admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html ng-app="sample" ng-controller="eoo">

<head>
  <title>DASHBOARD</title>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-animate.js"></script>
  <script src="../scripts.js"></script>
  <script src="../universal/time_utils.js"></script>
  <link rel="stylesheet" href="css/admin_dashboard_users.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .font {
      color: black;
      font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
      font-size: 25px;
      text-transform: uppercase;
    }

    .logout-button {
      background: linear-gradient(135deg,rgb(255, 0, 21),rgb(255, 123, 123));
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 15px;
      font-weight: bold;
      border-radius: 30px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
      letter-spacing: 1px;
    }

    .logout-button:hover {
      background: linear-gradient(135deg,rgb(9, 255, 0),rgb(0, 243, 125));
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    }
  </style>
</head>

<body bgcolor="#FAF9F6">
  <div class="nav-wrapper">
    <img src="../universal/images/logo.png" alt="Company Logo" class="company-logo" />
    <div class="font">
      Welcome <?php echo htmlspecialchars($username); ?>!
    </div>
    <nav>
      <a href="http://localhost/ditto/admin/admin_add_users/admin_add_users.php">DATABASE</a>
      <a href="http://localhost/ditto/admin/booking.php">BOOKING</a>
      <a href="http://localhost/ditto/admin/rate.php">RATES</a>
      <a href="http://localhost/ditto/admin/invoice.php">INVOICE</a>
      <a href="http://localhost/ditto/admin/dashboard.php" class="font"><?php echo htmlspecialchars($username); ?></a>
      <span></span>
    </nav>
    <div style="margin-left: 10px;">
      <button type="submit" class="logout-button">Logout</button>
    </div>
  </div>

  <div
    style="position: absolute; right: 10px; display: flex; align-items: center; justify-content: space-between; width: 30%;">
    <div>
      <p>
        <strong>Last Login:</strong>
        <span id="lastLoginText">Loading...</span>
      </p>
    </div>

    <div style="text-align: right;">
      <h3 id="time" style="margin: 0;">12:00:00</h3>
      <h3 id="date" style="margin: 0;">time</h3>
    </div>
  </div>

  <script>
    // Fetch login data from external file
    fetch('dashboard_php_code/dashboard_login_details.php')
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          document.getElementById('lastLoginText').textContent = 'Error: ' + data.error;
          return;
        }

        const loginText = (data.last_login && data.last_place)
          ? `${data.last_login}<br>${data.last_place}`
          : 'No previous login';

        document.getElementById('lastLoginText').innerHTML = loginText;

      });
  </script>

  <div class="font1">TOTAL USERS</div>

  <div class="user-stats-container">
    <div class="user-stat-box">
      <h3>Admins</h3>
      <p><span id="adminCount">0</span></p>
    </div>
    <div class="user-stat-box">
      <h3>Agents</h3>
      <p><span id="agentCount">0</span></p>
    </div>
    <div class="user-stat-box">
      <h3>Customers</h3>
      <p><span id="customerCount">0</span></p>
    </div>
  </div><br>

  <div class="font1">TOTAL BOOKINGS</div>

  <div class="user-stats-container">
    <div class="user-stat-box">
      <h3>Today's Bookings</h3>
      <p><span id="adminCount">0</span></p>
    </div>
    <div class="user-stat-box">
      <h3>Total Bookings</h3>
      <p><span id="agentCount">0</span></p>
    </div>
    <div class="user-stat-box">
      <h3>Pending Bookings</h3>
      <p><span id="customerCount">0</span></p>
    </div>
    <div class="user-stat-box">
      <h3>Completed Bookings</h3>
      <p><span id="customerCount">0</span></p>
    </div>
  </div>

  <div class="inline-fields">
    <div class="revenue-charts-container">
      <div class="field-group">
        <h3 class="font1">Daily Revenue</h3>
        <label class="chart-label">LAST 7 DAYS | <a href="#">MORE DETAILS</a></label>
        <div class="chart-card">
          <canvas id="dailyRevenueChart" height="200"></canvas>
        </div>
      </div>
      <div class="field-group">
        <h3 class="font1">Monthly Revenue</h3>
        <label class="chart-label">LAST 6 MONTHS | <a href="#">MORE DETAILS</a></label>
        <div class="chart-card">
          <canvas id="monthlyRevenueChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>


  <div class="chat-container" style="margin-top: 38px;">
    <div class="chat-header">
      Chat Box
    </div>
    <div class="chat-body">
      <!-- Chat messages will appear here -->
    </div>
    <div class="chat-input">
      <input type="text" placeholder="Type a message">
      <button>Send</button>
    </div>
  </div>

  <script>
    fetch('http://localhost/ditto/admin/dashboard_php_code/dashboard_users_total.php')
      .then(response => response.json())
      .then(data => {
        if (!data.error) {
          document.getElementById('adminCount').textContent = data.admin;
          document.getElementById('agentCount').textContent = data.agent;
          document.getElementById('customerCount').textContent = data.customer;
        } else {
          console.error('Server error:', data.error);
        }
      })
      .catch(error => console.error('Fetch error:', error));
  </script>

</body>

</html>