<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="stil/contact.css">
        <link rel="stylesheet" href="stil/navbar.css">
        <link rel="stylesheet" href="stil/footer.css">
    </head>
    <body>

    <?php include 'navbar.php'; ?>

    <section class="contact-section">
        <h1>Kontaktieren Sie uns</h1>
        <form action="send.php" method="post" class="contact-form">

            <label for="email">E-Mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Nachricht:</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Absenden</button>
        </form>
    </section>

    <?php include 'footer.php'; ?>

    </body>
</html>