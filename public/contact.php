<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self';">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self';">
=======

    <title>Kontakt</title>

>>>>>>> 8215e57c529b66844d022c86062e3708296ea9c7
    <link rel="stylesheet" href="stil/footer.css">
    <link rel="stylesheet" href="stil/contact.css">
    <link rel="stylesheet" href="stil/navbar.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="contact-wrapper">
        <div class="contact-container">
            <h2>Kontaktformular</h2>
            <form>
                <label>Name</label>
                <input type="text" required>

                <label>E-Mail</label>
                <input type="email" required>

                <label>Nachricht</label>
                <textarea required></textarea>

                <button type="submit">Absenden</button>
            </form>
        </div>
    </div>

    <div class="faq-wrapper">
        <div class="faq-container">
            <h2>Häufig gestellte Fragen</h2>

            <div class="faq-item" data-id="1">
                <div class="faq-question">
                    <h3>Wie lange dauert die Bearbeitung?</h3>
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    <p>In der Regel 24–48 Stunden.</p>
                </div>
            </div>

            <div class="faq-item" data-id="2">
                <div class="faq-question">
                    <h3>Welche Infos soll ich angeben?</h3>
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    <p>Name, E-Mail und eine genaue Beschreibung.</p>
                </div>
            </div>

            <div class="faq-item" data-id="3">
                <div class="faq-question">
                    <h3>Gibt es telefonischen Support?</h3>
                    <span class="arrow">+</span>
                </div>
                <div class="faq-answer">
                    <p>Aktuell nur per Formular oder E-Mail.</p>
                </div>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script src="funktionen/contact.js"></script>

</body>