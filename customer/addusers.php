<?php
session_start();

// Restrict access if not logged in as customer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
  header("Location: login.html");
  exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Welcome</title>
  <style>
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px 40px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      box-sizing: border-box;
    }

    h1 {
      text-align: center;
      color: #4f1919;
      margin-bottom: 30px;
    }

    form.search-form {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 30px;
      justify-content: center;
      background: #ffffff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid #e0e0e0;
    }

    form.search-form label {
      flex: 1 1 160px;
      font-weight: 600;
      font-size: 15px;
      color: #333333;
      align-self: center;
    }

    form.search-form input[type="text"],
    form.search-form select,
    form.search-form input[type="date"] {
      flex: 2 1 260px;
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      background-color: #fafafa;
      transition: border 0.3s ease, box-shadow 0.3s ease;
      max-width: 1010px;
      width: 100%;
    }

    form.search-form input[type="text"]:focus,
    form.search-form select:focus,
    form.search-form input[type="date"]:focus {
      border-color: #ff3333;
      outline: none;
      box-shadow: 0 0 6px rgba(255, 51, 51, 0.25);
    }

    form.search-form button {
      flex: 1 1 180px;
      background: linear-gradient(45deg, #4f1919, #ff3333);
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      padding: 12px 0;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    form.search-form button:hover {
      background: #ff4c4c;
      transform: translateY(-1px);
    }


    table.results {
      width: 100%;
      border-collapse: collapse;
      display: none;
    }

    table.results thead {
      background-color: #4f1919;
      color: white;
    }

    table.results th,
    table.results td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: center;
      font-size: 14px;
    }

    table.results tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    table.results tbody tr:hover {
      background-color: #ffe6e6;
    }

    @media (max-width: 600px) {
      form.search-form {
        flex-direction: column;
      }

      form.search-form label,
      form.search-form input,
      form.search-form select,
      form.search-form button {
        flex: 1 1 100%;
      }
    }

    .font {
      color: black;
      font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
      font-size: 25px;
      text-transform: uppercase;
    }

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
      min-width: 130px;
      /* ← Add this */
      justify-content: center;
    }

    nav a:hover {
      color: white;
    }

    nav span {
      position: absolute;
      top: 0;
      left: 0px;
      width: 130px;
      height: 100%;
      background: linear-gradient(45deg, #4f1919, #ff3333);
      border-radius: 8px;
      transition: left 0.4s ease;
      z-index: 0;
    }

    nav a:nth-child(1):hover~span {
      left: 0px;
    }

    nav a:nth-child(2):hover~span {
      left: 180px;
    }

    nav a:nth-child(3):hover~span {
      left: 360px;
    }

    nav a:nth-child(4):hover~span {
      left: 540px;
    }

    nav a:nth-child(5):hover~span {
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
      height: 40px;
      /* Adjust logo height */
      margin-right: 20px;
      /* Space between logo and nav */
      user-select: none;
      /* Optional: avoid text selection on logo */
    }


    .autocomplete-suggestions {
      position: absolute;
      border: 1px solid #ccc;
      border-top: none;
      max-height: 180px;
      overflow-y: auto;
      background: white;
      width: 100%;
      z-index: 1000;
      box-sizing: border-box;
    }

    .autocomplete-item {
      padding: 8px;
      cursor: pointer;
    }

    .autocomplete-item:hover {
      background-color: #f0f0f0;
    }

    .autocomplete-wrapper {
      position: relative;
      width: 100%;
    }
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
      <a href="http://localhost/ditto/customer/dashboard.php" style="text-transform:uppercase">
        <?php echo htmlspecialchars($username); ?>
      </a>
      <span></span>
    </nav>
  </div>

  <div class="container">
    <h1>Search Rates & Transport Details</h1>

    <!-- Step 1 form -->
    <form class="search-form" id="searchFormStep1">
      <label for="origin">Origin Port:</label>
      <input type="text" id="origin" name="origin" placeholder="Enter origin port" autocomplete="off" required />

      <label for="destination">Destination Port:</label>
      <input type="text" id="destination" name="destination" placeholder="Enter destination port" autocomplete="off" required />

      <label for="departureDate">Departure Date From:</label>
      <input type="date" id="departureDate" name="departureDate" required />

      <button type="submit">Next</button>
    </form>

    <!-- Step 2 form (hidden initially) -->
    <form class="search-form" id="searchFormStep2" style="display: none;">
      <label for="packageDetails">Package Description:</label>
      <input type="text" id="packageDetails" name="packageDetails" placeholder="e.g., electronics, furniture"
        required />

      <label for="containerType">Container Type:</label>
      <select id="containerType" name="containerType" required>
        <option value="">Select Type</option>
        <option value="20ft">20 ft</option>
        <option value="40ft">40 ft</option>
        <option value="40ftHC">40 ft HC</option>
        <option value="reefer">Reefer</option>
      </select>

      <label for="hazardous">Hazardous:</label>
      <select id="hazardous" name="hazardous" required>
        <option value="">Select</option>
        <option value="No">No</option>
        <option value="Yes">Yes</option>
      </select>

      <label for="packageSize">Package Size (CBM):</label>
      <input type="text" id="packageSize" name="packageSize" placeholder="e.g., 5.2" required />

      <button type="submit">Search</button>
    </form>

    <!-- Results table (hidden initially) -->
    <table class="results" id="resultsTable" style="display: none;">
      <thead>
        <tr>
          <th>Vessel Name</th>
          <th>Voyage</th>
          <th>ETD</th>
          <th>ETA</th>
          <th>Rate (USD)</th>
          <th>Transit Time</th>
          <th>Remarks</th>
          <th>More Details</th>
          <th>Contact</th>
        </tr>
      </thead>
      <tbody id="resultsBody"></tbody>
    </table>
  </div>

  <script>
    // AUTOCOMPLETE FUNCTION (unchanged)
    function setupAutocomplete(inputId) {
      const input = document.getElementById(inputId);

      const wrapper = document.createElement('div');
      wrapper.classList.add('autocomplete-wrapper');
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);

      const suggestionsBox = document.createElement('div');
      suggestionsBox.classList.add('autocomplete-suggestions');
      wrapper.appendChild(suggestionsBox);

      input.addEventListener('input', () => {
        const val = input.value.trim();
        if (val.length < 2) {
          suggestionsBox.innerHTML = '';
          suggestionsBox.style.display = 'none';
          return;
        }

        fetch(`search_ports.php?q=${encodeURIComponent(val)}`)
          .then(response => response.json())
          .then(data => {
            suggestionsBox.innerHTML = '';
            if (!data.length) {
              suggestionsBox.style.display = 'none';
              return;
            }

            data.forEach(item => {
              const div = document.createElement('div');
              div.classList.add('autocomplete-item');
              div.textContent = `${item.port_name} (${item.country}) [${item.port_code}]`;

              div.addEventListener('click', () => {
                input.value = item.port_code;
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
              });

              suggestionsBox.appendChild(div);
            });

            suggestionsBox.style.display = 'block';
          })
          .catch(() => {
            suggestionsBox.innerHTML = '';
            suggestionsBox.style.display = 'none';
          });
      });

      document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
          suggestionsBox.innerHTML = '';
          suggestionsBox.style.display = 'none';
        }
      });
    }

    setupAutocomplete('origin');
    setupAutocomplete('destination');

    // HANDLE FORM STEPS AND SEARCH RESULTS
    const step1 = document.getElementById('searchFormStep1');
    const step2 = document.getElementById('searchFormStep2');
    const resultsTable = document.getElementById('resultsTable');
    const resultsBody = document.getElementById('resultsBody');

    // Step 1 submit: validate and move to step 2 (optional, can be kept if you want)
    step1.addEventListener('submit', (e) => {
      e.preventDefault();

      const origin = document.getElementById('origin').value.trim();
      const destination = document.getElementById('destination').value.trim();

      if (!origin || !destination) {
        alert("Please fill in both origin and destination.");
        return;
      }

      // If you want, you can skip step2 and show results immediately here,
      // or just show step2 as before.
      step1.style.display = 'none';
      step2.style.display = 'flex';
      resultsTable.style.display = 'none'; // Hide results when going to step 2
    });

    // Step 2 submit: now fetch real results based ONLY on origin and destination
    step2.addEventListener('submit', (e) => {
      e.preventDefault();

      const origin = document.getElementById('origin').value.trim();
      const destination = document.getElementById('destination').value.trim();

      if (!origin || !destination) {
        alert("Origin and destination are required.");
        return;
      }

      // Prepare data for POST request
      fetch(`fetch_rates_simple.php?origin=${encodeURIComponent(origin)}&destination=${encodeURIComponent(destination)}`)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            alert("Error: " + data.error);
            resultsTable.style.display = 'none';
            return;
          }

          if (data.length === 0) {
            alert("No results found for the selected route.");
            resultsTable.style.display = 'none';
            return;
          }

          resultsBody.innerHTML = '';
          data.forEach(ship => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td>${ship.vessel_name}</td>
          <td>${ship.voyage}</td>
          <td>${ship.etd}</td>
          <td>${ship.eta}</td>
          <td>${ship.rate_usd}</td>
          <td>${ship.transit_time}</td>
          <td>${ship.remarks}</td>
          <td></td>
          <td></td>
        `;
            resultsBody.appendChild(tr);
          });

          resultsTable.style.display = 'table';
          step2.style.display = 'none';  // hide step 2 after results
        })
        .catch(() => {
          alert("Failed to fetch rates. Please try again later.");
          resultsTable.style.display = 'none';
        });
    });
  </script>

</body>

</html>