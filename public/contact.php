<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' https://cdnjs.cloudflare.com; script-src 'self' https://cdn.jsdelivr.net; connect-src 'self' https://api.emailjs.com;">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <title>Contact</title>
        <link rel="icon" type="image/png" href="datenbank/bilder/logo/logo.png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="!footer/footer.css">
        <link rel="stylesheet" href="stil/footer.css">
        <link rel="stylesheet" href="stil/contact.css">
        <link rel="stylesheet" href="!navebar/navbar.css">
    </head>
    <body>

        <?php include '!navebar/navbar.php'; ?>

        <div class="info-msg" id="info-msg" hidden="true">
            <h3>Message sent!</h3>
            <p>Your message has been sent successfully. <br>We will get back to you as soon as possible.</p>
        </div>

        <div class="error-msg" id="error-msg" hidden="true">
            <h3>Error!</h3>
            <p id="error-text">Your message was not sent!</p>
        </div>

        <div class="contact-wrapper">
            <div class="contact-container">
                <h2>Contact Form</h2>
                <form id="contactForm">
                    <label>Name</label>
                    <input type="text" id="name" required>

                    <label>E-Mail</label>
                    <input type="email" id="email" required>

                    <label>Message</label>
                    <textarea id="message" required></textarea>

                    <button type="submit" id="contact_button">Send</button>
                </form>
            </div>
        </div>

        <div class="section-separator"></div>

        <div class="faq-wrapper">
            <div class="faq-container">
                <h2>Frequently Asked Questions</h2>

                <div class="faq-item" data-id="1">
                    <div class="faq-question">
                        <h3>Can visitors enter the paddock with the Koniks?</h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>No, entering the pasture is strictly forbidden!<br>
                            You are welcome to watch the animals from behind the fence, but do not go under the fence or feed them.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-id="2">
                    <div class="faq-question">
                        <h3>Are the Koniks really wild horses?</h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>The horses in the national park are semi–wild. In summer they are mostly left to themselves; only for medical checks and when food becomes scarce in autumn does someone check on them and care for them. In winter they are moved to a pasture with plenty of grass and other food sources. Someone also looks after them there.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-id="3">
                    <div class="faq-question">
                        <h3>What breed of horse are they?</h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>The Konik (Polish: Konik polski) is a robust horse breed originating from Poland that resembles the extinct Tarpan. It is known for its hardiness and frugality and is often used in conservation projects for landscape maintenance.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="section-separator"></div>

        <?php include '!footer/footer.php'; ?>

        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
        <script src="funktionen/contact.js"></script>
        <script src="!navebar/navbar.js"></script>

    </body>
</html>
