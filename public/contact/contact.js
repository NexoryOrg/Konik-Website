//fetch emailjs data
let emailjsConfig;
fetch("../datenbank/data/user.json")
.then(response => response.json())
.then(data => {
    const email_js = data.emailjs.emailjs_data[0];

    service_id = email_js.service_id;
    template_id = email_js.template_id;
    public_key = email_js.public_key;

}).catch(error => {
    console.error("Error fetching emailjs data:", error);
});


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

    emailjs.init(public_key);
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

        try {
            await emailjs.send(service_id, template_id, templateParams);
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
