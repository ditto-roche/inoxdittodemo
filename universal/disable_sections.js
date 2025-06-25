// ===============================================
// Script: disable_sections.js
// Purpose: Disable UI sections based on admin's session permissions
// Features:
//   - Fetches list of section IDs to disable from backend
//   - Adds 'disabled-overlay' class to restricted sections
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DEPENDENCY:
//   - Expects 'get_user_session_data.php' to return:
//     { disabled_sections: [ "sectionID1", "sectionID2", ... ] }
//
// ===============================================

document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM fully loaded");

  fetch('../../universal/get_user_session_data.php')
    .then(response => {
      console.log("Fetched response", response);
      return response.json();
    })
    .then(data => {
      console.log("Parsed data:", data);
      const disabledSections = data.disabled_sections || [];
      disabledSections.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          element.classList.add('disabled-overlay');
          element.title = 'Disabled by Admin';
          console.log("Disabled element:", id);
        } else {
          console.warn("Element not found:", id);
        }
      });
    })
    .catch(err => {
      console.error("Fetch or JSON parse failed:", err);
    });
});
