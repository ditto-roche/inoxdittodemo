// ===============================================
// Script: admin_dashboard_controller.js
// Purpose: Main AngularJS controller for managing admin dashboard
// Modules: Login, User CRUD, Port CRUD, Country-Agent Assignment, Logs, Chat
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
// FUNCTIONS:
// - LOGIN FUNCTIONALITY
//   - $scope.login --> VALIDATES USER AND DATA FETCHED FROM loginrole.php [list used: $scope.credentials]
//
// - SECTION SWITCHING & VIEW INITIALIZATION
//   - $scope.switchSection(section) --> switches section [default: $scope.showSection1 = 'users'; $scope.showSection = 'add']
//   - sessionStorage.getItem('showSection') --> persists section state across reload
//
// - USER
//   - $scope.loadUsers --> loads all users 
//     --> list used: $scope.allUsers
//     --> php called: admin_add_users/users/user_view.php
//     --> DB: user
//
//   - $scope.loadLogs --> load update logs of the user 
//     --> php called: admin_add_users/users/user_log_details.php
//     --> DB: updatelogtable
//
//   - $scope.updateUser --> updates user details
//     --> php called: admin_add_users/users/user_edit.php
//     --> DB: user
//     --> refresh via: $scope.loadUsers()
//
//   - $scope.loadhistory --> load login history
//     --> php called: admin_add_users/users/user_view_login.php
//     --> DB: user_logins
//
//   - $scope.disableuser --> disables a user
//     --> php called: admin_add_users/users/user_disable.php
//     --> DB: user
//
// - UNIVERSAL
//   - $scope.editUser1 --> edit functionality for both user and ports
//   - $scope.ogUser1 --> get original data of user before update
//
// - PORTS
//   - $scope.loadports --> loads all port data
//     --> php called: admin_add_users/ports/port_view.php
//     --> DB: ports
//
//   - $scope.loadAgentsByCountry --> fetch agents mapped to a selected country
//     --> php called: admin_add_users/ports/port_fetch_agent.php
//     --> DB: user
//
//   - $scope.getAgentNameById --> get username by agent ID from agent list
//
// - COUNTRY LIST FETCH
//   - $http.get('port_fetch_existing_con.php') --> fetch assigned countries
//   - $http.get('port_fetch_nonexisting_con.php') --> fetch unassigned countries
//
// - CHAT MODULE
//   - $scope.agents, $scope.selectedAgent, $scope.chatMessages --> mock real-time agent selection and messaging
//   - $scope.selectAgent --> sets current agent context
//   - $scope.sendMessage --> simulate sending message
//   - $scope.goBack --> clears chat context
//
// - UTILS
//   - $scope.redirectNoBack --> hard redirect with no back option
//   - $scope.toggleMenu1, $scope.menuVisible --> toggles popup menu display
//   - $scope.images, $interval(...) --> image slider logic

var sample = angular.module("sample", ["ngAnimate"]);
sample.controller("eoo", function ($scope, $http, $interval, $document, $window) {

  // ===============================================
  // LOGIN FUNCTIONALITY
  // ===============================================
  $scope.credentials = {
    USERNAME: '',
    PASSWORD: '',
    ROLE: ''
  };

  $scope.login = function () {
    if (!$scope.credentials.USERNAME || !$scope.credentials.PASSWORD || !$scope.credentials.ROLE) {
      document.getElementById("error-label").textContent = "Please fill in all fields.";
      return;
    }

    $http.post("loginrole.php", $scope.credentials)
      .then(function (response) {
        const res = response.data;
        if (res.success) {
          sessionStorage.setItem('username', res.username);
          sessionStorage.setItem('role', res.role);
          sessionStorage.setItem('disabled_sections', JSON.stringify(res.disabled_sections || []));

          if (res.role === "admin") {
            window.location.href = "admin/dashboard.php";
          } else if (res.role === "agent") {
            window.location.href = "agent.php";
          } else {
            window.location.href = "customer/dashboard.php";
          }
        } else {
          document.getElementById("error-label").textContent = res.message || "Invalid credentials";
        }
      })
      .catch(function (error) {
        document.getElementById("error-label").textContent = "Server error during login.";
        console.error(error);
      });
  };

  // ===============================================
  // SECTION SWITCHING & VIEW INITIALIZATION
  // ===============================================
  $scope.showSection1 = 'users';
  $scope.showSection = 'add';
  $scope.switchSection = function (section) {
    $scope.showSection = section;
  };

  // ===============================================
  // USERS MODULE
  // ===============================================
  $scope.users = [];
  $scope.loadUsers = function () {
    $scope.switchSection('view');
    return $http.get('users/user_view.php').then(function (response) {
      console.log("Fetched users:", response.data.allUsers);
      $scope.allUsers = response.data.allUsers;
    }, function (error) {
      alert('Error loading users');
      console.error(error);
    });
  };

  $scope.loadLogs = function (userId) {
    $http.post('users/user_log_details.php', { userid: userId })
      .then(function (response) {
        $scope.user = response.data.user || { id: userId };
        $scope.logs = response.data.logs || [];
        $scope.logMessage = response.data.message || '';
        $scope.switchSection('logs');
      }, function () {
        $scope.logs = [];
        $scope.logMessage = "Failed to load logs.";
        $scope.switchSection('logs');
      });
  };

  $scope.loadhistory = function () {
    $scope.switchSection('login');
    $scope.loading = true;
    $http.get('users/user_view_login.php')
      .then(function (response) {
        $scope.alllogin = response.data.alllogin || [];
      })
      .catch(function (error) {
        alert('Failed to load login history');
        console.error('Error:', error);
      })
      .finally(function () {
        $scope.loading = false;
      });
  };

  $scope.sessionDurations = {}; // Step 1: Initialize empty object

  function fetchSessionDurations() {
    console.log("Fetching session durations...");
    $http.get('users/user_session_duration.php')
      .then(function (response) {
        console.log("Response received:", response); // Debug log
        if (response.data.sessions) {
          response.data.sessions.forEach(function (session) {
            console.log("Session for:", session.username, "→", session.duration);
            $scope.sessionDurations[session.username] = session.duration;
          });
        } else {
          console.warn("No 'sessions' key in response:", response.data);
        }
      })
      .catch(function (error) {
        console.error("Failed to fetch session durations", error);
      });
  }


  // Step 5: Call it on load
  fetchSessionDurations();


  // Refresh every 10 seconds
  $interval(fetchSessionDurations, 10000);


  $scope.disableuser = function (userId) {
    $http.post('users/user_disable.php', { userid: userId })
      .then(function (response) {
        if (response.data.success) {
          alert("User disabled successfully.");
          $scope.loadhistory();
        } else {
          alert("Failed to disable user: " + (response.data.message || 'Unknown error.'));
        }
      }, function () {
        alert("Request failed. Server error.");
      });
  };

  $scope.deleteuser = function (userId) {
    $http.post('users/user_delete.php', { userid: userId })
      .then(function (response) {
        if (response.data.success) {
          alert("User deleted successfully.");
          $scope.loadUsers();
        } else {
          alert("Failed to disable user: " + (response.data.message || 'Unknown error.'));
        }
      }, function () {
        alert("Request failed. Server error.");
      });
  };

  $scope.editUser1 = function (user, section, type) {
    let dataList = [];
    if (type === 'users') {
      dataList = $scope.allUsers || [];
    } else if (type === 'ports') {
      dataList = $scope.allPorts || [];
    }
    let updatedUser = dataList.find(u => u.id === user.id);
    $scope.editingUser = angular.copy(updatedUser || user);
    if (type === 'ports' && $scope.editingUser.country) {
      $scope.loadAgentsByCountry($scope.editingUser.country);
    }
    $scope.switchSection(section || 'edit');
  };

  $scope.ogUser1 = function (user) {
    console.log("Original user:", user);
    $scope.ogUserdetails = angular.copy(user);
  };

  $scope.updateUser = function () {
    let updatePayload = angular.copy($scope.editingUser);
    if (!updatePayload.password || updatePayload.password.trim() === "") {
      delete updatePayload.password;
    }
    $http.post('users/user_edit.php', updatePayload)
      .then(function (response) {
        document.getElementById('edit-user-form1').reset();
        alert("User updated successfully.");
        return $scope.loadUsers();
      })
      .then(function () {
        $scope.showSection1 = 'users';
        $scope.switchSection('view');
      })
      .catch(function (error) {
        alert("Update failed");
        console.error(error);
      });
  };


  // ===============================================
  // PORTS MODULE
  // ===============================================
  $scope.loadports = function () {
    $scope.switchSection('viewports');
    return $http.get('ports/port_view.php').then(function (response) {
      $scope.allPorts = response.data.allPorts;
    }, function (error) {
      console.error(error);
    });
  };

  $scope.portCountry = null;
  $scope.loadAgentsByCountry = function (country) {
    if (!country) {
      $scope.indiaAgents = [];
      $scope.foreignAgents = [];
      return;
    }
    $http({
      method: 'GET',
      url: 'ports/port_fetch_agent.php',
      params: { country: country },
      headers: { 'Content-Type': 'application/json' }
    }).then(function (response) {
      $scope.indiaAgents = response.data.indiaAgents;
      $scope.foreignAgents = response.data.foreignAgents;
    }, function (error) {
      alert('Error loading agents');
      console.error(error);
      $scope.indiaAgents = [];
      $scope.foreignAgents = [];
    });
  };

  $scope.getAgentNameById = function (id, list) {
    if (!Array.isArray(list)) return 'N/A';
    const agent = list.find(a => a.id == id);
    return agent ? agent.username : 'N/A';
  };


  var storedSection = sessionStorage.getItem('showSection');
  var storedSection1 = sessionStorage.getItem('showSection1');
  if (storedSection && storedSection1) {
    $scope.showSection = storedSection;
    $scope.showSection1 = storedSection1;
    sessionStorage.removeItem('showSection');
    if (storedSection === 'view') {
      $scope.switchSection('view');
      $scope.loadUsers();
    }
    else if (storedSection === 'viewports') {
      $scope.loadports();
    }
  }
  // ===============================================
  // CHAT MODULE
  // ===============================================
  $scope.agents = [
    { id: 1, username: 'agent_john' },
    { id: 2, username: 'agent_sara' },
    { id: 3, username: 'agent_raj' }
  ];
  $scope.selectedAgent = null;
  $scope.chatMessages = [];

  $scope.selectAgent = function (agent) {
    $scope.selectedAgent = agent;
    $scope.chatMessages = [];
  };

  $scope.sendMessage = function () {
    if ($scope.newMessage && $scope.selectedAgent) {
      $scope.chatMessages.push({ sender: 'You', text: $scope.newMessage, time: new Date().toLocaleTimeString() });
      $scope.newMessage = '';
    }
  };

  $scope.goBack = function () {
    $scope.selectedAgent = null;
    $scope.chatMessages = [];
  };

  // ===============================================
  // OTHER UTILITIES
  // ===============================================
  $scope.namescript = sessionStorage.getItem('username');
  $scope.role = sessionStorage.getItem('role');

  $scope.images = ['universal/images/ship1.jpg', 'universal/images/ship2.jpg', 'universal/images/ship3.jpg', 'universal/images/ship4.jpg'];
  $scope.currentIndex = 0;
  $interval(function () {
    $scope.currentIndex = ($scope.currentIndex + 1) % $scope.images.length;
  }, 3000);

  $scope.redirectNoBack = function (url) {
    window.location.replace(url);
  };

  $scope.menuVisible = false;
  $scope.toggleMenu1 = function () {
    $scope.menuVisible = !$scope.menuVisible;
  };

  $document.on('click', function (event) {
    var isClickInside = event.target.closest('.popup-menu-container');
    if (!isClickInside) {
      $scope.$apply(function () {
        $scope.menuVisible = false;
      });
    }
  });

  $scope.logout = function () {
    $http.post('../../universal/logout.php')  // Backend PHP logout script
      .then(function (response) {
        if (response.data.success) {
          $window.location.href = '/ditto/login.html';
        } else {
          alert("Logout failed: " + response.data.message);
        }
      }, function (error) {
        alert("Server error during logout.");
      });
  };

  // ===============================================
  // COUNTRY LIST FOR PORT ASSIGNMENT
  // ===============================================
  $scope.countries = [];
  $http.get("http://localhost/ditto/admin/admin_add_users/ports/port_fetch_existing_con.php")
    .then(function (response) {
      if (Array.isArray(response.data)) {
        const seen = new Set();
        $scope.countries = response.data.filter(country => {
          const trimmed = country.trim();
          if (!seen.has(trimmed)) {
            seen.add(trimmed);
            return true;
          }
          return false;
        });
      } else {
        console.error('Invalid response format:', response.data);
      }
    })
    .catch(function (error) {
      console.error('Error fetching countries:', error);
    });

  $scope.noncountries = [];
  $http.get("http://localhost/ditto/admin/admin_add_users/ports/port_fetch_nonexisting_con.php")
    .then(function (response) {
      if (Array.isArray(response.data)) {
        const seen = new Set();
        $scope.noncountries = response.data.filter(country => {
          const trimmed = country.trim();
          if (!seen.has(trimmed)) {
            seen.add(trimmed);
            return true;
          }
          return false;
        });
      } else {
        console.error('Invalid response format:', response.data);
      }
    })
    .catch(function (error) {
      console.error('Error fetching countries:', error);
    });

});
