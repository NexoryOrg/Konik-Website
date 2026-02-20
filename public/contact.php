<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' https://cdnjs.cloudflare.com; script-src 'self';">

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

        <div class="section-separator"></div>

        <div class="faq-wrapper">
            <div class="faq-container">
                <h2>Häufig gestellte Fragen</h2>

                <div class="faq-item" data-id="1">
                    <div class="faq-question">
                        <h3>Darf man bei den Koniks auf die Weide?</h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Nein, das betretten der Weide ist strengstens verboten!<br>
                            Sie dürfen die Tiere gerne hinter dem Zaun anschauen aber nicht unter dem Zaun durchgehen oder die Tiere fütter.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-id="2">
                    <div class="faq-question">
                        <h3>Sind die Koniks wirklich Wildpferde?</h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Die Pferde im Nationalpark sind Halb-Wildpfdere. Im Sommer sind sie meist auf sich alleine gestellt, nur zur ärtzlichen Untersuchung
                                und bei Nahrungsmangel im Herbst schaut jemand nach ihnen und versorgt sie. Im Winter kommen sie auf eine Weide mit genügend Grass und
                                anderen Nahrungsquellen. Dort kümmtert sich ebenfalls jemand um die Pferde.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-id="3">
                    <div class="faq-question">
                        <h3>Was ist das für eine Pferderasse?</h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>Das Konik (Polnisch: Konik polski) ist eine robuste, ursprünglich aus Polen stammende Pferderasse, die dem ausgestorbenen Tarpan ähnelt.
                                Sie gilt als besonders widerstandsfähig, genügsam und wird häufig in Naturschutzprojekten zur Landschaftspflege eingesetzt.
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
