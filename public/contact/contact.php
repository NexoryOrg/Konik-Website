<?php
require_once __DIR__ . '/../init.php';
?>

<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <base href="/">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <title><?= e(t('contact.title')) ?></title>
        <link rel="icon" type="image/png" href="../database/images/logo/logo.png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="../footer/footer.css">
        <link rel="stylesheet" href="../contact/contact.css">
        <link rel="stylesheet" href="../navbar/navbar.css">
    </head>
    <body>

        <?php include __DIR__ . '/../navbar/navbar.php'; ?>

        <div class="info-msg" id="info-msg" hidden="true">
            <h3><?= e(t('contact.info.title')) ?></h3>
            <p><?= e(t('contact.info.text')) ?></p>
        </div>

        <div class="error-msg" id="error-msg" hidden="true">
            <h3><?= e(t('contact.error.title')) ?></h3>
            <p id="error-text"><?= e(t('contact.error.default')) ?></p>
        </div>

        <div class="contact-wrapper">
            <div class="contact-container">
                <h2><?= e(t('contact.form.title')) ?></h2>
                <form id="contactForm">
                    <label><?= e(t('contact.form.name')) ?></label>
                    <input type="text" id="name" required>

                    <label><?= e(t('contact.form.email')) ?></label>
                    <input type="email" id="email" required>

                    <label><?= e(t('contact.form.message')) ?></label>
                    <textarea id="message" required></textarea>

                    <button
                        type="submit"
                        id="contact_button"
                        data-label-sending="<?= e(t('contact.js.sending')) ?>"
                        data-label-sent="<?= e(t('contact.js.sent')) ?>"
                        data-label-send="<?= e(t('contact.js.send')) ?>"
                        data-config-missing="<?= e(t('contact.js.config_missing')) ?>"
                        data-send-failed="<?= e(t('contact.js.send_failed')) ?>"
                    ><?= e(t('contact.form.send')) ?></button>
                </form>
            </div>
        </div>

        <div class="section-separator"></div>

        <div class="faq-wrapper">
            <div class="faq-container">
                <h2><?= e(t('contact.faq.title')) ?></h2>

                <div class="faq-item" data-id="1">
                    <div class="faq-question">
                        <h3><?= e(t('contact.faq.q1')) ?></h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p><?= e(t('contact.faq.a1')) ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-id="2">
                    <div class="faq-question">
                        <h3><?= e(t('contact.faq.q2')) ?></h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p><?= e(t('contact.faq.a2')) ?></p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-id="3">
                    <div class="faq-question">
                        <h3><?= e(t('contact.faq.q3')) ?></h3>
                        <span class="arrow">+</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p><?= e(t('contact.faq.a3')) ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="section-separator"></div>

        <?php include __dir__. '/../footer/footer.php'; ?>

        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
        <script src="../contact/contact.js"></script>
        <script src="../navbar/navbar.js"></script>

    </body>
</html>
