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

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        btn.disabled = true;
        btn.textContent = "Sending...";

        const templateParams = {
            name: document.getElementById("name").value,
            from_email: document.getElementById("email").value,
            message: document.getElementById("message").value,
            time: new Date().toLocaleString()
        };
        console.log("Template Parameter:", templateParams);

        try {
            await emailjs.send("service_kd8fsfe", "template_nvlnvt9", templateParams);
            form.reset();
            btn.textContent = "Email gesendet!";
            
            const infoMsg = document.getElementById("info-msg");
            if (infoMsg) {
                infoMsg.hidden = false;
                await new Promise(resolve => setTimeout(resolve, 3000));
                infoMsg.hidden = true;
            }

            btn.disabled = false;
            btn.textContent = "Absenden";
        } catch (error) {
            console.error("Fehler beim Senden der E-Mail:", error);
            
            const errorMsg = document.getElementById("error-msg");
            if (errorMsg) {
                document.getElementById("error-text").textContent = "Die E-Mail konnte nicht gesendet werden. Bitte versuchen Sie es später erneut.";
                errorMsg.hidden = false;
                await new Promise(resolve => setTimeout(resolve, 3000));
                errorMsg.hidden = true;
            }
            
            btn.textContent = "Absenden";
            btn.disabled = false;
        }
    });
});

const faqItems = document.querySelectorAll(".faq-item");

const openFaqs = JSON.parse(localStorage.getItem("openFaqs")) || [];

faqItems.forEach(item => {
    const id = item.dataset.id;
    if (openFaqs.includes(id)) {
        item.classList.add("active");
    }

    item.addEventListener("click", () => {
        item.classList.toggle("active");

        const updatedOpenFaqs = [];

        faqItems.forEach(i => {
            if (i.classList.contains("active")) {
                updatedOpenFaqs.push(i.dataset.id);
            }
        });

        localStorage.setItem("openFaqs", JSON.stringify(updatedOpenFaqs));
    });
});