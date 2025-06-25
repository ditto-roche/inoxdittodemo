<?php
include '../auth_admin.php';
require_once '../auth_check.php';
?>
<!DOCTYPE html>
<html>
<!-- 
=================================================
Script: admin_add_users.php
Purpose: Admin dashboard UI for managing users
Features:
  - Displays welcome message and navigation
  - Dynamically disables UI blocks via backend
  - Allows user management: Add, View, Edit, Login History
  - Integrates AngularJS for section control
Author: DITTO
Created on: 20-06-2025
=================================================

TECHNOLOGIES:
  - AngularJS         ==> for section switching and dynamic behavior
  - PHP               ==> for session data and user info rendering
  - JavaScript Fetch  ==> for disabling sections using session permissions
  - HTML/CSS          ==> for user interface design and layout

INTERACTS WITH:
  - get_user_session_data.php   ==> Fetches section access for current admin

  - user_add.php                ==> Submits form data for adding a user
  - user_delete.php             ==> Soft deletes a user
  - user_update.php             ==> Used by AngularJS function updateUser()
  - user_logs.php / user_logins.php ==> Load activity logs
=================================================
-->

<head>
  <title>DATABASE</title>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-animate.js"></script>
  <script src="../../scripts.js"></script>
  <script src="../../universal/time_utils.js"></script>
  <script src="admin_database_scripts.js"></script>
  <script src="../../universal/disable_sections.js"></script>
  <link rel="stylesheet" href="../css/admin_add_users.css">
  <style>
    .disabled-overlay {
      pointer-events: none;
      opacity: 0.5;
      filter: grayscale(100%);
    }

    button.logout-button {
      background: linear-gradient(135deg, rgb(255, 0, 21), rgb(255, 123, 123));
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 15px;
      font-weight: bold;
      border-radius: 30px;
      cursor: pointer;
      margin-top: 0px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
      letter-spacing: 1px;
    }


    button.logout-button:hover {
      background: linear-gradient(135deg, rgb(9, 255, 0), rgb(0, 243, 125));
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    }
  </style>
</head>

<body bgcolor="#FAF9F6" ng-app="sample" ng-controller="eoo">
  <!--
SECTION: Navigation Header
Displays logo, welcome username, and navigation links
PHP used to pull username from session
-->
  <div class="nav-wrapper">
    <img src="../../universal/images/logo.png" alt="Company Logo" class="company-logo" />
    <div class="font">
      Welcome <?php echo htmlspecialchars($_SESSION['username']); ?>!
    </div>
    <nav>
      <a href="http://localhost/ditto/admin/admin_add_users/admin_add_users.php">DATABASE</a>
      <a href="http://localhost/ditto/admin/booking.php">BOOKING</a>
      <a href="http://localhost/ditto/admin/rate.php">RATES</a>
      <a href="http://localhost/ditto/admin/invoice.php">INVOICE</a>
      <a href="http://localhost/ditto/admin/dashboard.php"
        style="text-transform:uppercase"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
      <span></span>
    </nav>
    <div style="margin-left: 10px;">
      <button type="submit" class="logout-button" ng-click="logout()">Logout</button>
    </div>
  </div>

  <!--
SECTION: Top Buttons - Switch Between USERS / PORTS
Uses AngularJS to toggle `showSection1` value
functions: 
 - switchSection defined in scripts.js
 - time: /universal/time_utils.js
-->
  <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px;">
    <div>
      <button ng-click="showSection1 = 'users'; switchSection('add')">USERS</button>
      <button ng-click="showSection1 = 'ports'; switchSection('addports')">PORTS</button>
     </div>

    <div style="text-align: right;">
      <h3 id="time" style="margin: 0;">12:00:00</h3>
      <h3 id="date" style="margin: 0;">time</h3>
    </div>
  </div>

  <!--SECTION: USERS Sub-Menu
Visible only when showSection1 === 'users'
AngularJS Buttons to view different user features

Functions:
  - loadUsers(): load all users, defined in scripts.js
  - loadhistory(): load all login history, defined in scripts.js-->
  <div ng-show="showSection1 === 'users'"
    style="display: flex; align-items: center; justify-content: space-between; padding: 10px;">
    <div>
      <button ng-click="showSection = 'add'">ADD USERS</button>
      <button ng-click="loadUsers()">VIEW USERS</button>
      <button ng-click="loadhistory()">LOGIN HISTORY</button>
      <button id="startShare" ng-click="showSection = 'screen'">Start Screen Share</button>
    </div>
  </div>

  <!--
  SECTION: ADD USERS Form
  Form POSTs to /admin/admin_add_users/users/user_add.php to create a new user
  Includes:
    - Name, Username, Password
    - Role/Type selector
    - Email and Phone with OTP verification UI
-->
  <div id="add" ng-show="showSection === 'add'">
    <div>
      <h5
        style="margin: 0;display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
        All Users</h5>
      <form name="userForm" action="http://localhost/ditto/admin/admin_add_users/users/user_add.php" method="POST">
        <label>Name:</label>
        <label style="color: red; font-size: 10px;">
          NOTE: ENTER NAME AS PER AADHAR CARD
        </label>
        <input type="text" name="name" placeholder="Enter your full name" required />
        <br />

        <div class="inline-fields">
          <div class="field-group">
            <label>Username:</label>
            <input type="text" name="username" id="username" placeholder="Enter username" required />
            <span id="username-error" style="color: red;"></span>
          </div>
          <div class="field-group">
            <label>Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required />
            <span id="password-error" style="color: red;"></span>
          </div>
        </div>
        <br />
        <div class="inline-fields">
          <div class="field-group">
            <label>Role:</label>
            <select name="role" id="roleSelect" required>
              <option value="" disabled selected>Select role</option>
              <option value="Admin">Admin</option>
              <option value="customer">Customer</option>
              <option value="agent">Agent</option>
            </select>
          </div>

          <div class="field-group" style="margin-left: 20px;" required>
            <label>Type:</label>
            <select name="type" id="typeSelect" disabled>
              <option value="" disabled selected>Select type</option>
            </select>
          </div>
        </div><br><br>

        <div class="inline-fields">
          <div class="field-group">
            <label>Email:</label>
            <div class="inline-fields">
              <input type="email" name="email" id="email" placeholder="Enter email" />
              <br><br>
              <button style="width: 300px" type="button" id="verify-btn" disabled>VERIFY EMAIL</button>
            </div>
            <span id="email-error" style="color: red;"></span>

            <div id="otp-section" class="otp-section" style="display:none;">
              <label>Enter OTP:</label><br />
              <div class="otp-inputs">
                <input type="text" maxlength="1" id="otp-0" required />
                <input type="text" maxlength="1" id="otp-1" required />
                <input type="text" maxlength="1" id="otp-2" required />
                <input type="text" maxlength="1" id="otp-3" required />
                <input type="text" maxlength="1" id="otp-4" required />
                <input type="text" maxlength="1" id="otp-5" required />
                <button id="verify-otp-btn" type="button" style="height: 40px" disabled>Verify</button>
              </div>
            </div>

            <div id="result"></div>
          </div>
        </div>

        <br /><br />

        <div class="inline-fields">
          <div class="field-group">
            <label>Phone:</label>
            <div class="inline-fields">
              <input type="tel" name="phone" id="phone" placeholder="Enter phone number" />
              <br><br>
              <button style="width: 300px" type="button" id="verify-btn1" disabled>VERIFY PHONE</button>
            </div>
            <span id="phone-error" style="color: red;"></span>

            <div id="otp-section1" class="otp-section" style="display:none;">
              <label>Enter OTP:</label><br />
              <div class="otp-inputs">
                <input type="text" maxlength="1" id="otp-00" required />
                <input type="text" maxlength="1" id="otp-11" required />
                <input type="text" maxlength="1" id="otp-22" required />
                <input type="text" maxlength="1" id="otp-33" required />
                <input type="text" maxlength="1" id="otp-44" required />
                <input type="text" maxlength="1" id="otp-55" required />
                <button id="verify-otp-btn1" type="button" style="height: 40px" disabled>Verify</button>
              </div>
            </div>

            <div id="result1"></div>
          </div>
        </div>
        <br /><br />
        <button type="submit" id="submit-btn1" disabled>Add User</button>
      </form>
    </div>
  </div>

  <!-- 
  SECTION: VIEW USERS Table
  Lists all users using Angular `ng-repeat`
  Features:
    - Filter/search
    - Edit / Delete (soft delete via PHP)
  Functions:
    - loadLogs(user.id): logs of the user past updates
    - editUser1(user,'edit','users'), ogUser1(user): copy of user data is stored
  PHP: admin_add_users/users/user_delete.php
-->
  <div ng-show="showSection === 'view'">
    <div
      style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
      <h5 style="margin: 0;">All Users</h5>
      <input type="text" ng-model="userSearch" placeholder="Search users..." style="padding: 6px 12px; width: 300px; border: 1.5px solid #ccc; border-radius: 4px; 
    font-size: 14px; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#007BFF'"
        onblur="this.style.borderColor='#ccc'">
    </div>

    <table class="user-table">
      <tr>
        <th>Sr. No</th>
        <th>ID</th>
        <th>Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Type</th>
        <th>ACCOUNT CREATION</th>
        <th>LOGS</th>
        <th>REMARK</th>
        <th>Actions</th>
      </tr>
      <tr ng-repeat="user in allUsers | filter:userSearch">
        <td>{{$index + 1}}</td>
        <td>{{user.id}}</td>
        <td>{{user.name}}</td>
        <td>{{user.username}}</td>
        <td>{{user.email}}</td>
        <td>{{user.phone}}</td>
        <td>{{user.role}}</td>
        <td>{{user.type}}</td>
        <TD>{{user.createdt}}</TD>
        <td><button ng-click="loadLogs(user.id)">LOGS</button></td>
        <td ng-style="{'color': user.status == 0 ? 'red' : 'green'}">
          {{ user.status == 0 ? 'Inactive' : 'Active' }}
        </td>
        <td>
          <a href="" ng-if="user.status == 0" ng-click="editUser1(user,'edit','users'); ogUser1(user)">Edit</a>
          <span ng-if="user.status != 0">
            <a href="" ng-click="editUser1(user,'edit','users'); ogUser1(user)">Edit</a> |
            <a href="" ng-click="deleteuser(user.id)" onclick="return confirm('Are you sure?')">Delete</a>
          </span>
        </td>
      </tr>
    </table>
  </div>

  <!--
  SECTION: EDIT USER Form
  Populates with `editingUser` and `ogUserdetails`
  Calls updateUser() via AngularJS
  Verifies updated email and phone using OTP
-->
  <div ng-show="showSection === 'edit'">
    <h5
      style="margin: 0;display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
      EDITING USER: {{editingUser.name}}</h5>
    <form id="edit-user-form1" ng-submit="updateUser()">
      <label for="name1">Name:</label><br />
      <input type="text" id="name1" ng-model="editingUser.name" required /><br /><br />

      <label for="username1">Username:</label><br />
      <input type="text" id="ogname" ng-model="ogUserdetails.username" readonly />

      <!-- Email Section -->
      <div class="inline-fields">
        <div class="field-group">
          <label for="email1">Email:</label><br />
          <div class="inline-fields">
            <input type="text" id="ogemail" ng-model="ogUserdetails.email" ng-show="false" required />
            <input type="email" id="email1" ng-model="editingUser.email" required /><br /><br />
            <button style="width: 300px; height: 45px; font-size: 14px;" type="button" id="verify-btnemail"
              disabled>VERIFY EMAIL</button>
          </div>
          <span id="email1-error" style="color: red;"></span>
          <div id="otp-sectionedit" class="otp-section" style="display:none;">
            <label>Enter OTP:</label><br />
            <div class="otp-inputs">
              <input type="text" maxlength="1" id="otp-e0" />
              <input type="text" maxlength="1" id="otp-e1" />
              <input type="text" maxlength="1" id="otp-e2" />
              <input type="text" maxlength="1" id="otp-e3" />
              <input type="text" maxlength="1" id="otp-e4" />
              <input type="text" maxlength="1" id="otp-e5" />
              <button id="verify-otp-btnemail" type="button" style="height: 40px" disabled>Verify</button>
            </div>
          </div>
          <div id="resultedit"></div>
        </div>
      </div>

      <!-- Phone Section -->
      <div class="inline-fields">
        <div class="field-group">
          <label for="phone1">Phone:</label><br />
          <div class="inline-fields">
            <input type="text" id="ogphone" ng-model="ogUserdetails.phone" ng-show="false" required />
            <input type="text" id="phone1" ng-model="editingUser.phone" required /><br /><br />
            <button style="width: 300px; height: 45px; font-size: 14px;" type="button" id="verify-btnphone"
              disabled>VERIFY PHONE</button>
          </div>
          <span id="phone1-error" style="color: red;"></span>
          <div id="otp-sectionphone" class="otp-section" style="display:none;">
            <label>Enter OTP:</label><br />
            <div class="otp-inputs">
              <input type="text" maxlength="1" id="otp-p0" />
              <input type="text" maxlength="1" id="otp-p1" />
              <input type="text" maxlength="1" id="otp-p2" />
              <input type="text" maxlength="1" id="otp-p3" />
              <input type="text" maxlength="1" id="otp-p4" />
              <input type="text" maxlength="1" id="otp-p5" />
              <button id="verify-otp-btnphone" type="button" style="height: 40px" disabled>Verify</button>
            </div>
          </div>
          <div id="resultphone"></div>
        </div>
      </div>

      <div class="inline-fields">
        <div class="field-group">
          <label for="password1">Password:</label><br />
          <input type="password" id="password1" ng-model="editingUser.password"
            placeholder="Enter new password if you want to change" /><br /><br />
          <span id="password1-error" style="color: red;"></span>
        </div>
        <div class="field-group">
          <label for="status">Status:</label><br />
          <input type="text" id="ogstatusupdate" ng-model="ogUserdetails.status" ng-show="false" required />
          <select id="statusupdate" ng-model="editingUser.status" required>
            <option value="" disabled selected>Status Update</option>
            <option value="0" selected>Inactive</option>
            <option value="1" selected>Active</option>
          </select>
        </div>
      </div>

      <button type="submit" id="save-update" disabled>Save & Update</button>
    </form>
  </div>

  <!-- 
  SECTION: USER LOGS Table
  Fetches logs for a user
  Shows: operation, field name, old/new values, timestamp
  Note: Revert button not functional yet
-->
  <div ng-show="showSection === 'logs'">
    <h4>Update Logs</h4>
    <h3>Showing the update table of {{ user.name }}</h3>
    <table class="user-table">
      <thead style="background: #eee;">
        <tr>
          <th>Sr. No</th>
          <th>User ID</th>
          <th>OPERATION</th>
          <th>Field</th>
          <th>Old Value</th>
          <th>New Value</th>
          <th>Updated By</th>
          <th>Date</th>
          <th>REVERT</th>
        </tr>
      </thead>
      <tbody>
        <tr ng-if="logs.length === 0">
          <td colspan="9"
            style="text-align:center; font-style: italic; color: {{ logMessage.includes('not authorized') ? 'red' : '#666' }};">
            {{logMessage || "No update logs to display."}}
          </td>
        </tr>
        <tr ng-repeat="log in logs">
          <td>{{$index + 1}}</td>
          <td>{{log.userid}}</td>
          <td ng-style="{'font-weight': 'bold','color': log.operation === 'DELETE' ? 'red' : 'green'}">
            {{log.operation}}
          </td>
          <td>{{log.fieldname}}</td>
          <td>{{log.oldvalue}}</td>
          <td>{{log.newvalue}}</td>
          <td>{{log.updatedby}}</td>
          <td>{{log.updatedt | date:'medium'}}</td>
          <td><button ID="REVERT" ng-click="">Click Me</button></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!--
  SECTION: LOGIN HISTORY
  Lists login/logout activities from user_logins table
  Status: Active / Inactive / Disabled
-->
  <div ng-show="showSection === 'login'">
    <div
      style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
      <h5 style="margin: 0;">LOGIN HISTORY</h5>
      <input type="text" ng-model="userSearch" placeholder="Search users..." style="padding: 6px 12px; width: 300px; border: 1.5px solid #ccc; border-radius: 4px; 
    font-size: 14px; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#007BFF'"
        onblur="this.style.borderColor='#ccc'">
    </div>

    <div class="table-wrapper">
      <table class="user-table">
        <thead>
          <tr>
            <th>SR.NO</th>
            <th>USER ID</th>
            <th>Status</th>
            <th>Username</th>
            <th>TIME</th>
            <th>IP ADDRESS</th>
            <th>SESSION ID</th>
            <th>BROWSER</th>
            <th>OS</th>
            <th>DEVICE</th>
            <th>LOCATION</th>
            <th>DURATION</th>
            <!-- <th>OPERATION</th> -->
          </tr>
        </thead>
        <tbody>
          <tr ng-repeat="login in alllogin | filter:userSearch">
            <td>{{$index + 1}}</td>
            <td>{{login.U_id}}</td>
            <td ng-style="{
                'color': login.actions === 'LOGOUT' ? 'orange' :
                        login.actions === 'DELETED' ? 'red' :
                        login.actions === 'LOGIN' ? 'green' :
                        login.actions === 'DISABLED' ? 'blue' :
                        'black',
                'font-weight': 'bold'
              }">
              {{
              login.actions === 'LOGOUT' ? 'Inactive' :
              login.actions === 'DELETED' ? 'User-Deleted' :
              login.actions === 'LOGIN' ? 'Active' :
              login.actions === 'DISABLED' ? 'User-Disabled' :
              'Unknown'
              }}
            </td>

            <td>{{login.username}}</td>
            <td>{{login.login_time}}</td>
            <td>{{login.ip_address}}</td>
            <td>{{login.session_id}}</td>
            <td>{{login.browser}}</td>
            <td>{{login.os}}</td>
            <td>{{login.device_type}}</td>
            <td>{{login.location}}</td>
            <!-- <td>
              <button ng-if="login.actions === 'LOGIN' && login.current == 1" ng-click="disableuser(login.U_id)">
                DISABLE USER
              </button>
              <lable ng-if="login.current == 0">NO OPERATIONS</label>
            </td> -->
            <td>
              <span ng-if="login.actions === 'LOGIN' && login.current == 1">
                {{ sessionDurations[login.username] || 'Calculating...' }}
              </span>
              <span ng-if="login.actions === 'LOGOUT' || login.actions === 'DISABLED' || login.actions === 'DELETED'">
                {{ sessionDurations[login.username] ? sessionDurations[login.username] + ' ago' : 'Calculating...' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div ng-show="showSection1 === 'ports'"
    style="display: flex; align-items: center; justify-content: space-between; padding: 10px;">
    <div>
      <button ng-click="showSection = 'addports'">ADD PORTS</button>
      <button ng-click="loadports()">VIEW PORTS</button>
      <button ng-click="showSection = 'addcountries'">ADD COUNTRY AND AGENTS</button>
      <button ng-click="loadcountriesagents()">VIEW COUNTRIES</button>
    </div>
  </div>
  <div id="addports" ng-show="showSection === 'addports'">
    <h5
      style="margin: 0;display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
      Add Ports</h5>
    <form name="userForm" action="http://localhost/ditto/admin/admin_add_users/ports/port_add.php" method="POST">
      <label>PORT NAME:</label>
      <input type="text" name="portname" id="portname" placeholder="Enter Port name" required />
      <span id="port-error" style="color: red;"></span>
      <br />

      <div class="inline-fields">
        <div class="field-group">
          <label>PORT COUNTRY</label>
          <select ng-model="selectedCountry" ng-change="loadAgentsByCountry(selectedCountry)" required>
            <option value="" disabled selected>Select country</option>
            <option ng-repeat="country in countries" value="{{country}}">{{country}}</option>
          </select>
          <input type="hidden" name="selectedCountry" ng-value="selectedCountry" />
        </div>
        <div class="field-group">
          <label>PORT CODE</label>
          <input type="text" name="portcode" id="portcode" placeholder="Enter Port Code" required />
          <span id="code-error" style="color: red;"></span>
        </div>
      </div>
      <br />

      <div class="inline-fields">
        <div class="field-group">
          <label>HEAD</label>
          <input type="text" name="porthead" id="porthead" placeholder="Enter Port Head" required />
        </div>
        <div class="field-group">
          <label>CONTACT</label>
          <input type="text" name="portcode" id="portcontact" placeholder="Enter Port Contact" />
        </div>
      </div>
      <br>

      <div class="inline-fields">
        <div class="field-group">
          <label>PORT INDIA AGENT</label>
          <select id="portindia" ng-model="portindag" ng-options="agent.id as agent.username for agent in indiaAgents"
            required>
            <option value="" disabled selected>Select Agent</option>
          </select>
          <input type="hidden" name="portindia" ng-value="portindag" />
        </div>
        <div class="field-group">
          <label>PORT FORIEGN AGENT</label>
          <select id="portforiegn" ng-model="portforag"
            ng-options="agent.id as agent.username for agent in foreignAgents" required>
            <option value="" disabled selected>Select Agent</option>
          </select>
          <input type="hidden" name="portforiegn" ng-value="portforag" />
        </div>
      </div>
      <br />

      <button type="submit" id="submit-btn" disabled>Add Port</button>
    </form>
  </div>
  <div ng-show="showSection === 'viewports'">
    <div
      style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
      <h5 style="margin: 0;">All PORTS</h5>
      <input type="text" ng-model="userSearch1" placeholder="Search users..." style="padding: 6px 12px; width: 300px; border: 1.5px solid #ccc; border-radius: 4px; 
    font-size: 14px; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='#007BFF'"
        onblur="this.style.borderColor='#ccc'">
    </div>
    <table class="user-table">
      <tr>
        <th>ID</th>
        <th>PORT NAME</th>
        <th>COUNTRY</th>
        <th>PORT CODE</th>
        <th>PORT INDIA AGENT</th>
        <th>PORT COUNTRY AGENT</th>
        <th>HEAD</th>
        <th>CONTACT PORT</th>
        <th>REMARK</th>
        <th>Actions</th>
      </tr>
      <tr ng-repeat="user1 in allPorts | filter:userSearch1">
        <td>{{user1.id}}</td>
        <td>{{user1.port_name}}</td>
        <td>{{user1.country}}</td>
        <td>{{user1.port_code}}</td>
        <td>{{user1.port_india_agent}}</td>
        <td>{{user1.port_country_agent}}</td>
        <td>{{user1.port_head}}</td>
        <td>{{user1.port_contact}}</td>
        <td ng-style="{'color': user1.status == 0 ? 'red' : 'green'}">
          {{ user1.status == 0 ? 'Inactive' : 'Active' }}
        </td>
        <td>
          <a href="" ng-if="user1.status == 0" ng-click="editUser1(user1,'editports','ports'); ogUser1(user1)">Edit</a>
          <span ng-if="user1.status != 0">
            <a href="" ng-click="editUser1(user1,'editports','ports'); ogUser1(user1)">Edit</a> |
            <a href="admin_add_users/ports/port_delete.php?id={{user1.id}}"
              onclick="return confirm('Are you sure?')">Delete</a>
          </span>
        </td>
      </tr>
    </table>
  </div>
  <div ng-show="showSection === 'editports'">
    <form name="userFormeditport" method="POST">
      <div class="inline-fields">
        <div class="field-group">
          <label>PORT NAME</label>
          <input type="text" id="ognameport" ng-model="ogUserdetails.port_name" ng-show="false" required />
          <input type="text" name="portname" id="portnameedit" ng-model="editingUser.port_name" />
        </div>
        <div class="field-group">
          <label>PORT COUNTRY</label>
          <input type="text" name="portcountry" id="portcountryedit" ng-model="ogUserdetails.country" readonly />
        </div>
      </div>
      <br>

      <div class="inline-fields">
        <div class="field-group">
          <label>PORT CODE</label>
          <input type="text" id="ogportcodeedit" ng-model="ogUserdetails.port_code" ng-show="false" required />
          <input type="text" name="portcode" id="portcodeedit" ng-model="editingUser.port_code"
            style="text-transform: uppercase;" />
          <span id="port-error-edit" style="color: red;"></span>
        </div>
        <div class="field-group">
          <label for="status">Status:</label>
          <select id="statusupdateport" ng-model="editingUser.status" required>
            <option value="" disabled selected>Status Update</option>
            <option value="0" selected>Inactive</option>
            <option value="1" selected>Active</option>
          </select>
        </div>
      </div>
      <br>

      <div class="inline-fields">
        <div class="field-group">
          <label>HEAD</label>
          <input type="text" id="ogportheaddit" ng-model="ogUserdetails.port_head" ng-show="false" required />
          <input type="text" name="porthead" id="portheadedit" ng-model="editingUser.port_head" />
          <span id="head-error-edit" style="color: red;"></span>
        </div>
        <div class="field-group">
          <label>CONTACT</label>
          <input type="text" id="ogportcontactdit" ng-model="ogUserdetails.port_contact" ng-show="false" required />
          <input type="text" name="portcode" id="portcontactedit" ng-model="editingUser.port_contact" />
          <span id="contact-error-edit" style="color: red;"></span>
        </div>
      </div><br /><br />

      <div class="inline-fields">
        <div class="field-group">
          <label>CURRENT PORT INDIA AGENT</label>
          <input type="text" readonly value="{{ getAgentNameById(ogUserdetails.port_india_agent, indiaAgents) }}" />
        </div>

        <div class="field-group">
          <label>CURRENT PORT FOREIGN AGENT</label>
          <input type="text" readonly value="{{ getAgentNameById(ogUserdetails.port_country_agent, foreignAgents) }}" />
        </div>
      </div>

      <div class="inline-fields">
        <div class="field-group">
          <label>PORT INDIA AGENT</label>
          <select ng-model="editingUser.port_india_agent"
            ng-options="agent.id as agent.username for agent in indiaAgents" required>
            <option value="" disabled selected>Select Agent</option>
          </select>
        </div>
        <div class="field-group">
          <label>PORT FORIEGN AGENT</label>
          <select ng-model="editingUser.port_country_agent"
            ng-options="agent.id as agent.username for agent in foreignAgents" required>
            <option value="" disabled selected>Select Agent</option>
          </select>
        </div>
      </div>
      <br /><br />

      <button type="submit" id="submit-edit-port" disabled>Add Port</button>
    </form>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const submitBtneditport = document.getElementById('submit-edit-port');
      const portnameedit = document.getElementById('portnameedit');
      const ogportnameedit = document.getElementById('ognameport');

      // Ensure all required elements exist
      if (!portnameedit) {
        console.warn("PORT NAME CANNOT BE EMPTY.");
        return;
      }

      portnameedit.addEventListener('input', () => {
        const portnamevalue = portnameedit.value.trim();
        const ogportnamevalue = ogportnameedit.value.trim();

        if (!portnamevalue || portnamevalue === ogportnamevalue) {
          submitBtneditport.disabled = true;
        } else {
          submitBtneditport.disabled = false;
        }
      });

      const portheadedit = document.getElementById('portheadedit');
      const ogportheadedit = document.getElementById('ogportheaddit');

      // Ensure all required elements exist
      if (!portheadedit) {
        console.warn("PORT NAME CANNOT BE EMPTY.");
        return;
      }

      portheadedit.addEventListener('input', () => {
        const portheadvalue = portheadedit.value.trim();
        const ogportheadvalue = ogportheadedit.value.trim();

        if (portheadvalue === ogportheadvalue) {
          submitBtneditport.disabled = true;
        } else {
          submitBtneditport.disabled = false;
        }
      });

    });
  </script>
  <div ng-show="showSection === 'addcountries'">
    <h5
      style="margin: 0;display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;border-bottom: 3px solid #176a01;">
      Add Countries</h5>
    <form name="userForm1" action="http://localhost/ditto/admin/admin_add_users/ports/port_country" method="POST">
      <div>
        <label>COUNTRY</label>
        <select id="selectednonCountry" name="selectednonCountry" ng-model="selectednonCountry"
          ng-change="loadAgentsByCountry(selectednonCountry)" required>
          <option value="" disabled selected>Select country</option>
          <option ng-repeat="noncountry in noncountries" value="{{noncountry}}">{{noncountry}}</option>
        </select>
      </div>
      <br>

      <div class="inline-fields">
        <div class="field-group">
          <label>ADD PORT INDIA AGENT</label>
          <select id="portindiacountry" name="portindiacountry" ng-model="entry.portindag"
            ng-options="agent.id as agent.username for agent in indiaAgents" required>
            <option value="" disabled selected>Select Agent</option>
          </select>
        </div>
        <div class="field-group">
          <label>ADD PORT FOREIGN AGENT</label>
          <select id="portforeigncountry" name="portforiegncountry" ng-model="entry.portforag"
            ng-options="agent.id as agent.username for agent in foreignAgents" required>
            <option value="" disabled selected>Select Agent</option>
          </select>
        </div>
      </div>

      <br />
      <button type="submit" id="submit-btn2">Submit</button>
    </form>
  </div>
  <div ng-show="showSection === 'screen'">
  <button id="shareBtn">Start Sharing (Agent)</button>
  <button id="viewBtn">View Screen (Admin)</button>

  <video id="remoteVideo" autoplay playsinline controls></video>

  <script>
    const sessionId = "screen-session-001";
    const shareBtn = document.getElementById("shareBtn");
    const viewBtn = document.getElementById("viewBtn");
    const remoteVideo = document.getElementById("remoteVideo");

    let pc = new RTCPeerConnection();

    pc.ontrack = (e) => {
      remoteVideo.srcObject = e.streams[0];
    };

    async function postData(data) {
      await fetch(`users/signaling.php?session=${sessionId}`, {
        method: "POST",
        body: JSON.stringify(data),
      });
    }

    async function getData() {
      const res = await fetch(`users/signaling.php?session=${sessionId}`);
      return res.json();
    }

    shareBtn.onclick = async () => {
      const stream = await navigator.mediaDevices.getDisplayMedia({ video: true });
      stream.getTracks().forEach(track => pc.addTrack(track, stream));

      const offer = await pc.createOffer();
      await pc.setLocalDescription(offer);
      await postData({ offer });

      const checkAnswer = setInterval(async () => {
        const data = await getData();
        if (data.answer) {
          clearInterval(checkAnswer);
          await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
        }
      }, 1000);
    };

    viewBtn.onclick = async () => {
      const checkOffer = setInterval(async () => {
        const data = await getData();
        if (data.offer) {
          clearInterval(checkOffer);
          await pc.setRemoteDescription(new RTCSessionDescription(data.offer));
          const answer = await pc.createAnswer();
          await pc.setLocalDescription(answer);
          await postData({ answer });
        }
      }, 1000);
    };
  </script>
  </div>

</body>

</html>