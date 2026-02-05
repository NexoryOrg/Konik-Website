<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.emailjs.com/dist/email.min.js"></script>
    <link rel="stylesheet" href="stil/contact.css">
    <link rel="stylesheet" href="stil/navbar.css">
    <link rel="stylesheet" href="stil/footer.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
    <h2>Kontaktformular</h2>
    <form id="contactForm" onsubmit="sendEmail(event)">
        <label for="name">Name:</label>
        <input type="text" id="name" required>

        <label for="email">E-Mail:</label>
        <input type="email" id="email" required>

        <label for="message">Nachricht:</label>
        <textarea id="message" rows="5" required></textarea>

        <button type="submit">Absenden</button>
        <div id="status"></div>
    </form>
    </div>

    <script src="funktionnen/contact.js"></script>

    <?php include 'footer.php'; ?>

</body>