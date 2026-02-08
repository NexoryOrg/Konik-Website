window.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById("contact_button");
    if (!btn) {
        console.error("Button nicht gefunden!");
        return;
    }

    if (!window.emailjs) {
        console.error("EmailJS ist nicht geladen!");
        return;
    }

    emailjs.init("i5L8uctnUoYlZ1CCX");
    console.log("EmailJS geladen:", emailjs);

    const form = document.getElementById("contactForm");
    if (!form) {
        console.error("Formular nicht gefunden!");
        return;
    }

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        // Button während dem Senden deaktivieren
        btn.disabled = true;
        btn.textContent = "Sending...";

        const templateParams = {
            name: document.getElementById("name").value,
            from_email: document.getElementById("email").value,
            message: document.getElementById("message").value,
            time: new Date().toLocaleString()
        };
        console.log("Template Parameter:", templateParams);

        emailjs.send("service_kd8fsfe", "template_nvlnvt9", templateParams)
        .then(() => {
            form.reset();
            btn.textContent = "Email gesendet!";
            btn.disabled = false;
        })
        .catch((error) => {
            console.error("Fehler beim Senden der E-Mail:", error);
            alert("Fehler beim Senden der E-Mail. Details in Konsole.");
            btn.textContent = "Absenden";
            btn.disabled = false;
        });
    });
});