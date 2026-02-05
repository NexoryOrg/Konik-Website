  // EmailJS initialisieren
  emailjs.init("i5L8uctnUoYlZ1CCX");

  function sendEmail(event) {
    event.preventDefault(); // verhindert Seiten-Neuladen

    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const message = document.getElementById('message').value;

    emailjs.send("service_kd8fsfe", "template_nvlnvt9", {
      from_name: name,
      from_email: email,
      message: message
    })
    .then(response => {
      document.getElementById('status').className = 'success';
      document.getElementById('status').innerText = 'E-Mail erfolgreich gesendet!';
      document.getElementById('contactForm').reset();
    }, error => {
      document.getElementById('status').className = 'error';
      document.getElementById('status').innerText = 'Fehler beim Senden: ' + error.text;
    });
  }