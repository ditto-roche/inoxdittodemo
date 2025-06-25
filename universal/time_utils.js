function updateTime() {
  const time = document.getElementById('time');
  if (time) {
    const d = new Date();
    time.textContent = d.toLocaleTimeString();
  }
}

function updateDate() {
  const dateElem = document.getElementById('date');
  if (dateElem) {
    const now = new Date();
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour12: true
    };
    dateElem.textContent = now.toLocaleString('en-US', options);
  }
}

setInterval(() => {
  updateTime();
  updateDate();
}, 1000);

window.onload = () => {
  updateTime();
  updateDate();

  // Show error message if exists in URL params
  const params = new URLSearchParams(window.location.search);
  if (params.has('error')) {
    const errorLabel = document.getElementById('error-label');
    if (errorLabel) {
      errorLabel.textContent = 'Invalid username, password, or role.';
    }
  }
};
