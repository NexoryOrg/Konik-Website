<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stil/footer.css">
    <link rel="stylesheet" href="stil/contact.css">
    <link rel="stylesheet" href="stil/navbar.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="contact-container">
    <h2>Kontaktformular</h2>
    <form id="contactForm">
        <label for="name">Name:</label>
        <input type="text" id="name" required>

        <label for="email">E-Mail:</label>
        <input type="email" id="email" required>

        <label for="message">Nachricht:</label>
        <textarea id="message" rows="5" required></textarea>

        <button id="contact_button" type="submit">Absenden</button>
        <div id="status"></div>
    </form>
    </div>

    <?php include 'footer.php'; ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script src="funktionen/contact.js"></script>

</body>