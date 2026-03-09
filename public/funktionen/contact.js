window.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("contact_button");
    if (!btn) {
        console.error("Submit button not found!");
        return;
    }

    if (!window.emailjs) {
        console.error("EmailJS not loaded!");
        return;
    }

    emailjs.init("i5L8uctnUoYlZ1CCX");
    console.log("EmailJS loaded:", emailjs);

    const form = document.getElementById("contactForm");
    if (!form) {
        console.error("Form not found!");
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
            btn.textContent = "Message sent!";
            
            const infoMsg = document.getElementById("info-msg");
            if (infoMsg) {
                infoMsg.hidden = false;
                await new Promise(resolve => setTimeout(resolve, 3000));
                infoMsg.hidden = true;
            }

            btn.disabled = false;
            btn.textContent = "Send";
        } catch (error) {
            console.error("Error sending email:", error);
            
            const errorMsg = document.getElementById("error-msg");
            if (errorMsg) {
                document.getElementById("error-text").textContent = "The email could not be sent. Please try again later.";
                errorMsg.hidden = false;
                await new Promise(resolve => setTimeout(resolve, 3000));
                errorMsg.hidden = true;
            }
            
            btn.textContent = "Send";
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