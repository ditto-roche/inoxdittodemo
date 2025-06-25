// ===============================================
// Script: add_user.php
// Purpose: Handles insertion of new users with assigned roles and types
// Features:
//   - Validates username, email, and phone for uniqueness
//   - SEND POST data to beckend user_add.php
//   - Inserts data into `users` table with relevant fields
// Author: DITTO
// Created on: 12-06-2025
// ===============================================

document.addEventListener('DOMContentLoaded', () => {
  const submitBtn = document.getElementById('submit-btn');
  const verifyEmailBtn = document.getElementById('verify-btn');
  const verifyPhoneBtn = document.getElementById('verify-btn1');

  let isUsernameValid = false;
  let isPasswordValid = false;
  let isEmailValid = false;
  let isPhoneValid = false;

  function updateSubmitState() {
    submitBtn.disabled = !(isUsernameValid && isPasswordValid && isEmailValid && isPhoneValid);
  }

  // Username Validation
  const usernameInput = document.getElementById('username');
  const usernameError = document.getElementById('username-error');
  usernameInput.addEventListener('input', () => {
    const username = usernameInput.value.trim();

    if (!username) {
      usernameError.textContent = '';
      isUsernameValid = false;
      updateSubmitState();
      return;
    }

    fetch(`http://localhost/ditto/admin/admin_add_users/users/user_validate_credep.php?username=${encodeURIComponent(username)}`)
      .then(res => res.text())
      .then(data => {
        data = data.trim();
        if (data === 'taken') {
          usernameError.textContent = 'Username already exists. Please choose another.';
          usernameError.style.color = 'red';
          isUsernameValid = false;
        } else if (data === 'available') {
          usernameError.textContent = 'Unique Username';
          usernameError.style.color = 'green';
          isUsernameValid = true;
        } else {
          usernameError.textContent = 'ERROR 101';
          usernameError.style.color = 'red';
          isUsernameValid = false;
        }
        updateSubmitState();
      })
      .catch(() => {
        usernameError.textContent = 'Error checking username.';
        usernameError.style.color = 'red';
        isUsernameValid = false;
        updateSubmitState();
      });
  });

  // Password Validation
  const passwordInput = document.getElementById('password');
  const passwordError = document.getElementById('password-error');

  passwordInput.addEventListener('input', () => {
    const password = passwordInput.value;
    const isValid = /[a-zA-Z]/.test(password) && /[0-9]/.test(password);

    if (!isValid) {
      passwordError.textContent = 'Password should be alphanumeric (include both letters and numbers).';
      passwordError.style.color = 'red';
      isPasswordValid = false;
    } else {
      passwordError.textContent = 'Password is strong.';
      passwordError.style.color = 'green';
      isPasswordValid = true;
    }
    updateSubmitState();
  });

  // Email Validation
  const emailInput = document.getElementById('email');
  const emailError = document.getElementById('email-error');

  emailInput.addEventListener('input', () => {
    const email = emailInput.value;
    const isValidEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);

    if (!isValidEmail) {
      emailError.textContent = 'INVALID EMAIL';
      emailError.style.color = 'red';
      isEmailValid = false;
      verifyEmailBtn.disabled = true;
      updateSubmitState();
      return;
    }

    fetch(`http://localhost/ditto/admin/admin_add_users/users/user_validate_credep.php?email=${encodeURIComponent(email)}`)
      .then(res => res.text())
      .then(data => {
        data = data.trim();
        if (data === 'email_taken') {
          emailError.textContent = 'Email already exists';
          emailError.style.color = 'red';
          isEmailValid = false;
          verifyEmailBtn.disabled = true;
        } else {
          emailError.textContent = 'Valid Email';
          emailError.style.color = 'green';
          isEmailValid = true;
          verifyEmailBtn.disabled = false;
        }
        updateSubmitState();
      })
      .catch(() => {
        emailError.textContent = 'Error checking email.';
        emailError.style.color = 'red';
        isEmailValid = false;
        verifyEmailBtn.disabled = true;
        updateSubmitState();
      });
  });

  // Phone Validation
  const phoneInput = document.getElementById('phone');
  const phoneError = document.getElementById('phone-error');

  phoneInput.addEventListener('input', () => {
    const phone = phoneInput.value;
    const isValidPhone = /^[0-9]{10}$/.test(phone);

    if (!isValidPhone) {
      phoneError.textContent = 'Invalid Phone Number';
      phoneError.style.color = 'red';
      isPhoneValid = false;
      verifyPhoneBtn.disabled = true;
      updateSubmitState();
      return;
    }

    fetch(`http://localhost/ditto/admin/admin_add_users/users/user_validate_credep.php?phone=${encodeURIComponent(phone)}`)
      .then(res => res.text())
      .then(data => {
        data = data.trim();
        if (data === 'phone_taken') {
          phoneError.textContent = 'Phone No. already exists';
          phoneError.style.color = 'red';
          isPhoneValid = false;
          verifyPhoneBtn.disabled = true;
        } else {
          phoneError.textContent = 'Valid Phone No.';
          phoneError.style.color = 'green';
          isPhoneValid = true;
          verifyPhoneBtn.disabled = false;
        }
        updateSubmitState();
      })
      .catch(() => {
        phoneError.textContent = 'Error checking Phone No.';
        phoneError.style.color = 'red';
        isPhoneValid = false;
        verifyPhoneBtn.disabled = true;
        updateSubmitState();
      });
  });

  // OTP Component Setup
  function setupOtpFlow(config) {
    const { triggerBtn, section, otpInputs, submitBtn, resultEl, successMsg, failMsg } = config;
    let generatedOtp = null;

    triggerBtn.addEventListener('click', () => {
      generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();
      console.log('Generated OTP:', generatedOtp);

      section.style.display = 'block';
      otpInputs.forEach(input => (input.value = ''));
      otpInputs[0].focus();

      resultEl.textContent = 'OTP sent!';
      resultEl.style.color = 'green';
      submitBtn.disabled = true;
    });

    submitBtn.addEventListener('click', () => {
      const enteredOtp = otpInputs.map(input => input.value).join('');
      if (enteredOtp === generatedOtp) {
        resultEl.textContent = successMsg;
        resultEl.style.color = 'green';
        section.style.display = 'none';
      } else {
        resultEl.textContent = failMsg;
        resultEl.style.color = 'red';
      }
    });

    otpInputs.forEach((input, index) => {
      input.addEventListener('input', () => {
        if (input.value && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
        updateBtnState();
      });

      input.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !input.value && index > 0) {
          otpInputs[index - 1].focus();
        }
        setTimeout(updateBtnState, 10);
      });
    });

    function updateBtnState() {
      const allFilled = otpInputs.every(i => i.value.trim());
      submitBtn.disabled = !allFilled;
    }
  }

  // Setup Email OTP
  setupOtpFlow({
    triggerBtn: verifyEmailBtn,
    section: document.getElementById('otp-section'),
    otpInputs: [...Array(6)].map((_, i) => document.getElementById(`otp-${i}`)),
    submitBtn: document.getElementById('verify-otp-btn'),
    resultEl: document.getElementById('result'),
    successMsg: 'Email verified successfully!',
    failMsg: 'Incorrect OTP, try again.'
  });

  // Setup Phone OTP
  setupOtpFlow({
    triggerBtn: verifyPhoneBtn,
    section: document.getElementById('otp-section1'),
    otpInputs: [...Array(6)].map((_, i) => document.getElementById(`otp-${i}${i}`)),
    submitBtn: document.getElementById('verify-otp-btn1'),
    resultEl: document.getElementById('result1'),
    successMsg: 'Phone No verified successfully!',
    failMsg: 'Incorrect OTP, try again.'
  });

  // Final form field required validation
  const form = document.querySelector('form[name="userForm"]');
  const submitBtn1 = document.getElementById('submit-btn1');
  const requiredFields = form.querySelectorAll('input[required], select[required]');

  function validateForm() {
    let allFilled = true;
    requiredFields.forEach(field => {
      if (!field.value.trim()) {
        allFilled = false;
      }
    });
    submitBtn1.disabled = !allFilled;
  }

  requiredFields.forEach(field => {
    field.addEventListener('input', validateForm);
    field.addEventListener('change', validateForm);
  });

  validateForm();

  // Role-based type selection
  const roleSelect = document.getElementById('roleSelect');
  const typeSelect = document.getElementById('typeSelect');
  const typesByRole = {
    customer: ['Direct Shipper', 'Freight Forwarder'],
    agent: ['Sales & Marketing', 'Operations', 'Customer Service', 'Accounts', 'Management'],
    Admin: ['Level 0', 'Level-1']
  };

  roleSelect.addEventListener('change', () => {
    const selectedRole = roleSelect.value;
    typeSelect.innerHTML = '<option value="" disabled selected>Select type</option>';

    if (typesByRole[selectedRole]) {
      typeSelect.disabled = false;
      typesByRole[selectedRole].forEach(type => {
        const option = document.createElement('option');
        option.value = type.toLowerCase().replace(/\s+/g, '-');
        option.textContent = type;
        typeSelect.appendChild(option);
      });
    } else {
      typeSelect.disabled = true;
    }
  });

  const subbtn1 = document.getElementById('submit-btn');
  const portcode1 = document.getElementById('portcode');
  const portError1 = document.getElementById('code-error');

  portcode1.addEventListener('input', () => {
    const pname1 = portcode1.value;
    const ogpname = ogportcode.value;

    fetch(`http://localhost/ditto/admin/admin_add_users/ports/port_validate_code.php?portcode=${encodeURIComponent(pname1)}&currentportcode=${encodeURIComponent(ogpname)}`)
      .then(res => res.text())
      .then(data => {
        data = data.trim();
        if (data === 'taken') {
          portError1.textContent = 'Port Code already exsit';
          portError1.style.color = 'red';
          subbtn1.disabled = true;
        } else {
          portError1.textContent = 'Valid Port code';
          portError1.style.color = 'green';
          subbtn1.disabled = false;
        }
      });
  });

  const portcontact = document.getElementById('portcontactedit');
  const ogportcontact = document.getElementById('ogportcontactdit');
  const portcontactError = document.getElementById('contact-error-edit');

  portcontact.addEventListener('input', () => {
    const pcedit = portcontact.value;
    const ogcedit = ogportcontact.value;
    if (pcedit === ogcedit) {
      portcontactError.textContent = 'Entered same name';
      portcontactError.style.color = 'orange';
      subbtn.disabled = true;
    } else if (pcedit === "") {
      portcontactError.textContent = 'Port name cannot be empty';
      portcontactError.style.color = 'red';
      subbtn.disabled = true;
    } else {
      portcontactError.textContent = 'Valid Port Name';
      portcontactError.style.color = 'green';
      subbtn.disabled = false;
    }
  });

  const portcode = document.getElementById('portcodeedit');
  const ogportcode = document.getElementById('ogportcodeedit');
  const portError = document.getElementById('port-error-edit');
  const submitBtneditport = document.getElementById('submit-edit-port');

  portcode.addEventListener('input', () => {
    const pname = portcode.value;
    const ogpname = ogportcode.value;

    fetch(`http://localhost/ditto/admin/admin_add_users/ports/port_validate_code.php?portcode=${encodeURIComponent(pname)}&currentportcode=${encodeURIComponent(ogpname)}`)
      .then(res => res.text())
      .then(data => {
        data = data.trim();
        if (data === 'same') {
          portError.textContent = 'Entered same port code';
          portError.style.color = 'orange';
          submitBtneditport.disabled = true;
        } else if (data === 'taken') {
          portError.textContent = 'Port Code already exsit';
          portError.style.color = 'red';
          submitBtneditport.disabled = true;
        } else {
          portError.textContent = 'Valid Port code';
          portError.style.color = 'green';
          submitBtneditport.disabled = false;
        }
      });
  });

  const submitBtn11 = document.getElementById('save-update');
  const verifyEmailBtn11 = document.getElementById('verify-btnemail');
  const verifyPhoneBtn11 = document.getElementById('verify-btnphone');

  // Password Validation
  const passwordInput11 = document.getElementById('password1');
  const passwordError11 = document.getElementById('password1-error');

  passwordInput11.addEventListener('input', () => {
    const pwd11 = passwordInput11.value;
    if (/[a-zA-Z]/.test(pwd11) && /[0-9]/.test(pwd11)) {
      passwordError11.textContent = 'Password is strong.';
      passwordError11.style.color = 'green';
      submitBtn11.disabled = false;
    } else {
      passwordError11.textContent = 'Password should be alphanumeric.';
      passwordError11.style.color = 'red';
      submitBtn11.disabled = true;
    }
  });

  // Email Validation
  const emailInput11 = document.getElementById('email1');
  const ogEmail11 = document.getElementById('ogemail');
  const emailError11 = document.getElementById('email1-error');

  emailInput11.addEventListener('input', () => {
    const email11 = emailInput11.value;
    const og11 = ogEmail11.value;
    const isValid11 = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email11);

    if (!isValid11) {
      emailError11.textContent = 'INVALID EMAIL';
      emailError11.style.color = 'red';
      verifyEmailBtn11.disabled = true;
      return;
    }

    fetch(`http://localhost/ditto/admin/admin_add_users/users/user_validate_credep.php?email=${encodeURIComponent(email11)}&currentemail=${encodeURIComponent(og11)}`)
      .then(res11 => res11.text())
      .then(data11 => {
        data11 = data11.trim();
        if (data11 === 'email_same') {
          emailError11.textContent = 'Entered same Email';
          emailError11.style.color = 'orange';
          verifyEmailBtn11.disabled = true;
        } else if (data11 === 'email_taken') {
          emailError11.textContent = 'Email already exists';
          emailError11.style.color = 'red';
          verifyEmailBtn11.disabled = true;
        } else {
          emailError11.textContent = 'Valid Email';
          emailError11.style.color = 'green';
          verifyEmailBtn11.disabled = false;
        }
      });
  });

  // Phone Validation
  const phoneInput11 = document.getElementById('phone1');
  const ogphone111 = document.getElementById('ogphone');
  const phoneError11 = document.getElementById('phone1-error');

  phoneInput11.addEventListener('input', () => {
    const phone11 = phoneInput11.value;
    const og111 = ogphone111.value;
    if (!/^[0-9]{10}$/.test(phone11)) {
      phoneError11.textContent = 'Invalid Phone Number';
      phoneError11.style.color = 'red';
      verifyPhoneBtn11.disabled = true;
      return;
    }

    fetch(`http://localhost/ditto/admin/admin_add_users/users/user_validate_credep.php?phone=${encodeURIComponent(phone11)}&currentphone=${encodeURIComponent(og111)}`)
      .then(res11 => res11.text())
      .then(data11 => {
        data11 = data11.trim();
        if (data11 === 'phone_same') {
          phoneError11.textContent = 'Entered same phone';
          phoneError11.style.color = 'orange';
          verifyPhoneBtn11.disabled = true;
        } else if (data11 === 'phone_taken') {
          phoneError11.textContent = 'Phone already exists';
          phoneError11.style.color = 'red';
          verifyPhoneBtn11.disabled = true;
        } else {
          phoneError11.textContent = 'Valid Phone';
          phoneError11.style.color = 'green';
          verifyPhoneBtn11.disabled = false;
        }
      });
  });

  const statusupdate = document.getElementById('statusupdate');
  const ogstatusupdate = document.getElementById('ogstatusupdate');

  statusupdate.addEventListener('input', () => {
    const astatusupdate = statusupdate.value;
    const oastatusupdate = ogstatusupdate.value;

    if (astatusupdate === oastatusupdate) {
      submitBtn11.disabled=true;
    } else {
      submitBtn11.disabled=false;
    }
  });

  // OTP Logic
  function setupOtp11(triggerBtn11, sectionId11, inputPrefix11, verifyBtnId11, resultId11, successMsg11, failMsg11) {
    const section11 = document.getElementById(sectionId11);
    const inputs11 = Array.from({ length: 6 }, (_, i11) => document.getElementById(`${inputPrefix11}${i11}`));
    const verifyBtn11 = document.getElementById(verifyBtnId11);
    const result11 = document.getElementById(resultId11);
    let generatedOtp11 = '';

    triggerBtn11.addEventListener('click', () => {
      generatedOtp11 = Math.floor(100000 + Math.random() * 900000).toString();
      console.log(`Generated OTP: ${generatedOtp11}`);
      section11.style.display = 'block';
      inputs11.forEach(i11 => i11.value = '');
      inputs11[0].focus();
      result11.textContent = 'OTP sent!';
      result11.style.color = 'green';
    });

    inputs11.forEach((input11, idx11) => {
      input11.addEventListener('input', () => {
        if (input11.value && idx11 < 5) inputs11[idx11 + 1].focus();
        verifyBtn11.disabled = inputs11.some(i11 => i11.value.trim() === '');
      });

      input11.addEventListener('keydown', e11 => {
        if (e11.key === 'Backspace' && !input11.value && idx11 > 0) {
          inputs11[idx11 - 1].focus();
        }
      });
    });

    verifyBtn11.addEventListener('click', () => {
      const entered11 = inputs11.map(i11 => i11.value).join('');
      if (entered11 === generatedOtp11) {
        result11.textContent = successMsg11;
        submitBtn11.disabled = false;
        result11.style.color = 'green';
        section11.style.display = 'none';
      } else {
        result11.textContent = failMsg11;
        result11.style.color = 'red';
        submitBtn11.disabled = true;
      }
    });
  }

  setupOtp11(verifyEmailBtn11, 'otp-sectionedit', 'otp-e', 'verify-otp-btnemail', 'resultedit', 'Email verified successfully!', 'Incorrect OTP');
  setupOtp11(verifyPhoneBtn11, 'otp-sectionphone', 'otp-p', 'verify-otp-btnphone', 'resultphone', 'Phone verified successfully!', 'Incorrect OTP');


});
