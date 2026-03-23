<?php

if (!function_exists('supported_languages')) {
    function supported_languages() {
        return ['de', 'en', 'es'];
    }
}

if (!function_exists('normalize_language')) {
    function normalize_language($lang) {
        $lang = strtolower((string)$lang);
        return in_array($lang, supported_languages(), true) ? $lang : '';
    }
}

if (!function_exists('bootstrap_language')) {
    function bootstrap_language() {
        $lang = '';

        if (isset($_GET['lang'])) {
            $lang = normalize_language($_GET['lang']);
            if ($lang !== '') {
                $_SESSION['lang'] = $lang;
            }
        }

        if ($lang === '' && isset($_SESSION['lang'])) {
            $lang = normalize_language($_SESSION['lang']);
        }

        if ($lang === '') {
            $acceptLanguage = (string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
            foreach (preg_split('/\s*,\s*/', $acceptLanguage) as $entry) {
                $candidate = substr($entry, 0, 2);
                $candidate = normalize_language($candidate);
                if ($candidate !== '') {
                    $lang = $candidate;
                    break;
                }
            }
        }

        if ($lang === '') {
            $lang = 'de';
        }

        $_SESSION['lang'] = $lang;
        $GLOBALS['app_lang'] = $lang;
    }
}

if (!function_exists('current_lang')) {
    function current_lang() {
        return (string)($GLOBALS['app_lang'] ?? 'de');
    }
}

if (!function_exists('with_lang')) {
    function with_lang($url, $lang = null) {
        $lang = normalize_language($lang ?? current_lang());
        if ($lang === '') {
            $lang = current_lang();
        }

        $parts = parse_url((string)$url);
        $path = $parts['path'] ?? '/';
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query['lang'] = $lang;
        $qs = http_build_query($query);
        $result = $path . ($qs !== '' ? '?' . $qs : '');

        if (!empty($parts['fragment'])) {
            $result .= '#' . $parts['fragment'];
        }

        return $result;
    }
}

if (!function_exists('current_url_with_lang')) {
    function current_url_with_lang($lang) {
        $current = (string)($_SERVER['REQUEST_URI'] ?? '/home/index.php');
        return with_lang($current, $lang);
    }
}

if (!function_exists('t')) {
    function t($key) {
        static $translations = [
            'de' => [
                'nav.home' => 'Startseite',
                'nav.gallery' => 'Galerie',
                'nav.history' => 'Historie',
                'nav.contact' => 'Kontakt',
                'nav.lang_switcher' => 'Sprachauswahl',
                'footer.imprint' => 'Impressum & Datenschutz',

                'home.title' => 'Startseite',
                'home.hero.image_alt' => 'Wildpferde auf einer Wiese',
                'home.hero.title' => 'Wildpferde',
                'home.hero.subtitle' => 'Nationalpark Schwarzwald',
                'home.scroll' => 'Nach unten scrollen',
                'home.about.heading' => 'Uber die Koniks',
                'home.about.intro' => 'Im Nationalpark Schwarzwald werden Konik-Pferde seit mehreren Jahren eingesetzt, um die offenen Bergheiden, die sogenannten Grinden, zu erhalten. Diese Grinden sind einzigartige Lebensraume hoch uber dem Wald und wurden ohne Beweidung langsam verbuschen. Die robusten Tiere stammen aus Polen und gehoren zu einer ursprunglichen Pferderasse, die noch Gene der fruheren Wildpferde (Tarpane) tragt.',
                'home.about.habitat.title' => 'Lebensraum',
                'home.about.habitat.text' => 'Die Koniks leben auf offenen Heide- und Grasflachen im nordlichen Schwarzwald, rund um Zollstock/Hilseneck und nahe dem Schliffkopf. Durch ihr Grasen bleiben die Flachen offen und artenreich.',
                'home.about.habitat.image_alt' => 'Lebensraum der Pferde',
                'home.about.behavior.title' => 'Verhalten',
                'home.about.behavior.text' => 'Koniks sind robust und anpassungsfahig. Sie leben in Herden, fressen Graser, Krauter und gelegentlich Rinde und Triebe. So fordern sie die Strukturvielfalt der Vegetation und helfen vielen anderen Arten.',
                'home.about.behavior.image_alt' => 'Verhalten der Koniks',
                'home.about.care.title' => 'Schutz und Pflege',
                'home.about.care.text' => 'Obwohl die Pferde wild wirken, werden sie regelmassig kontrolliert. Mitarbeitende des Parks achten auf Gesundheit, Hufpflege und Weidedauer. Besucher sollten Abstand halten und die Tiere nicht futtern.',
                'home.about.care.image_alt' => 'Schutz und Pflege der Pferde',
                'home.map.heading' => 'Anfahrt zum Nationalpark',
                'home.map.button' => '📍 Standort anzeigen',
                'home.map.popup_title' => 'Nationalparkzentrum Schwarzwald',
                'home.map.popup_subtitle' => 'Bad Liebenzell-Unterreichenbach',
                'home.map.marker_title' => 'Nationalparkzentrum Schwarzwald',
                'home.lightbox.prev' => 'Vorheriges Bild',
                'home.lightbox.next' => 'Nachstes Bild',
                'home.lightbox.close' => 'Schliessen',

                'gallery.title' => 'Galerie',
                'gallery.lightbox.prev' => 'Vorheriges Bild',
                'gallery.lightbox.next' => 'Nachstes Bild',
                'gallery.lightbox.close' => 'Schliessen',
                'gallery.upload.title' => '📸 Neue Fotos hochladen',
                'gallery.upload.email' => 'Deine E-Mail (bei Ruckfragen)',
                'gallery.upload.event_title' => "Titel (z. B. 'Verschneiter Tag im Wald')",
                'gallery.upload.description' => 'Beschreibung des Ereignisses...',
                'gallery.upload.submit' => 'Foto hochladen und zur Prufung senden',
                'gallery.upload.error_prefix' => 'Fehler:',
                'gallery.upload.token_missing' => 'Fehler: Sicherheitstoken fehlt. Bitte lade die Seite neu.',

                'history.title' => 'Historie',
                'history.empty' => 'Keine Eintrage in der Historie verfugbar.',

                'contact.title' => 'Kontakt',
                'contact.info.title' => 'Nachricht gesendet!',
                'contact.info.text' => 'Deine Nachricht wurde erfolgreich gesendet. Wir melden uns so schnell wie moglich.',
                'contact.error.title' => 'Fehler!',
                'contact.error.default' => 'Deine Nachricht wurde nicht gesendet!',
                'contact.form.title' => 'Kontaktformular',
                'contact.form.name' => 'Name',
                'contact.form.email' => 'E-Mail',
                'contact.form.message' => 'Nachricht',
                'contact.form.send' => 'Senden',
                'contact.faq.title' => 'Haufige Fragen',
                'contact.faq.q1' => 'Durfen Besucher die Weide mit den Koniks betreten?',
                'contact.faq.a1' => 'Nein, das Betreten der Weide ist verboten. Beobachte die Tiere bitte nur von ausserhalb des Zauns und futtere sie nicht.',
                'contact.faq.q2' => 'Sind Koniks wirklich Wildpferde?',
                'contact.faq.a2' => 'Die Pferde sind halbwild. Im Sommer leben sie weitgehend eigenstandig, werden aber medizinisch kontrolliert. Im Winter kommen sie auf eine Weide mit genug Futter.',
                'contact.faq.q3' => 'Welche Pferderasse ist das?',
                'contact.faq.a3' => 'Der Konik ist eine robuste Pferderasse aus Polen, die dem ausgestorbenen Tarpan ahnelt und oft in Naturschutzprojekten eingesetzt wird.',
                'contact.js.sending' => 'Wird gesendet...',
                'contact.js.sent' => 'Nachricht gesendet!',
                'contact.js.send' => 'Senden',
                'contact.js.config_missing' => 'E-Mail-Konfiguration fehlt. Bitte versuche es spater erneut.',
                'contact.js.send_failed' => 'Die E-Mail konnte nicht gesendet werden. Bitte versuche es spater erneut.',

                'imprint.title' => 'Impressum & Datenschutz',
                'imprint.heading' => 'Impressum & Datenschutz',
                'imprint.section.imprint' => 'Impressum',
                'imprint.operator' => 'Betreiber:',
                'imprint.contact' => 'Kontakt:',
                'imprint.responsible' => 'Verantwortlich fur den Inhalt gemaB § 55 Abs. 2 RStV:',
                'imprint.vat' => 'Umsatzsteuer-Identifikationsnummer:',
                'imprint.disclaimer' => 'Haftungsausschluss:',
                'imprint.disclaimer.text' => 'Die Inhalte dieser Website wurden mit grosser Sorgfalt erstellt. Fur Richtigkeit, Vollstandigkeit und Aktualitat der Inhalte konnen wir jedoch keine Gewahr ubernehmen.',
                'imprint.external' => 'Externe Links:',
                'imprint.external.text' => 'Diese Website enthalt Links zu externen Seiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Fur diese Inhalte ist stets der jeweilige Anbieter verantwortlich.',
                'imprint.section.privacy' => 'Datenschutz',
                'imprint.privacy.text1' => 'Personenbezogene Daten aus dem Kontaktformular (Name, E-Mail, Nachricht) werden ausschliesslich zur Bearbeitung deiner Anfrage verwendet und nicht an Dritte weitergegeben.',
                'imprint.privacy.text2' => 'Cookies werden nur fur die technische Funktion der Website verwendet.',
                'imprint.privacy.text3' => 'Weitere Informationen folgen in einer vollstandigen Datenschutzerklarung.',
                'imprint.section.reach' => 'So erreichst du uns',
                'imprint.reach.text' => 'Bei Fragen zu Impressum oder Datenschutz nutze bitte das Kontaktformular oder schreibe an die unten genannte E-Mail-Adresse.'
            ],
            'en' => [
                'nav.home' => 'Home',
                'nav.gallery' => 'Gallery',
                'nav.history' => 'History',
                'nav.contact' => 'Contact',
                'nav.lang_switcher' => 'Language switcher',
                'footer.imprint' => 'Imprint & Privacy Policy',

                'home.title' => 'Home',
                'home.hero.image_alt' => 'Wild horses in a meadow',
                'home.hero.title' => 'Wild Horses',
                'home.hero.subtitle' => 'Black Forest National Park',
                'home.scroll' => 'Scroll down',
                'home.about.heading' => 'About the Koniks',
                'home.about.intro' => 'In the Black Forest National Park, Konik horses have been used for several years to maintain the open mountain pastures known as "Grinden." These habitats would gradually overgrow without grazing. The hardy animals come from Poland and still carry genes of former wild horses (Tarpans).',
                'home.about.habitat.title' => 'Habitat',
                'home.about.habitat.text' => 'The Koniks live on open heath and grasslands in the northern Black Forest, around Zollstock/Hilseneck and near Schliffkopf. Their grazing keeps these areas open and diverse.',
                'home.about.habitat.image_alt' => 'The horses habitat',
                'home.about.behavior.title' => 'Behavior',
                'home.about.behavior.text' => 'Koniks are hardy and adaptable. They live in herds and feed on grasses, herbs and sometimes bark or shoots. Their feeding supports biodiversity in vegetation and wildlife.',
                'home.about.behavior.image_alt' => 'Konik behavior',
                'home.about.care.title' => 'Protection and Care',
                'home.about.care.text' => 'Although the horses appear wild, they are regularly monitored. Park staff check their health, hoof care and grazing periods. Visitors should keep distance and not feed the animals.',
                'home.about.care.image_alt' => 'Protection and care of the horses',
                'home.map.heading' => 'Getting to the National Park',
                'home.map.button' => '📍 Show location',
                'home.map.popup_title' => 'Black Forest National Park Center',
                'home.map.popup_subtitle' => 'Bad Liebenzell-Unterreichenbach',
                'home.map.marker_title' => 'Black Forest National Park Center',
                'home.lightbox.prev' => 'Previous image',
                'home.lightbox.next' => 'Next image',
                'home.lightbox.close' => 'Close',

                'gallery.title' => 'Gallery',
                'gallery.lightbox.prev' => 'Previous image',
                'gallery.lightbox.next' => 'Next image',
                'gallery.lightbox.close' => 'Close',
                'gallery.upload.title' => '📸 Add New Photos',
                'gallery.upload.email' => 'Your email (for follow-up questions)',
                'gallery.upload.event_title' => "Title (e.g. 'Snowy day in the forest')",
                'gallery.upload.description' => 'Event description...',
                'gallery.upload.submit' => 'Upload photo and send for review',
                'gallery.upload.error_prefix' => 'Error:',
                'gallery.upload.token_missing' => 'Error: Security token is missing. Please reload the page.',

                'history.title' => 'History',
                'history.empty' => 'No entries available in the history.',

                'contact.title' => 'Contact',
                'contact.info.title' => 'Message sent!',
                'contact.info.text' => 'Your message has been sent successfully. We will get back to you as soon as possible.',
                'contact.error.title' => 'Error!',
                'contact.error.default' => 'Your message was not sent!',
                'contact.form.title' => 'Contact Form',
                'contact.form.name' => 'Name',
                'contact.form.email' => 'E-Mail',
                'contact.form.message' => 'Message',
                'contact.form.send' => 'Send',
                'contact.faq.title' => 'Frequently Asked Questions',
                'contact.faq.q1' => 'Can visitors enter the paddock with the Koniks?',
                'contact.faq.a1' => 'No, entering the pasture is forbidden. You can watch the animals from behind the fence, but do not feed them.',
                'contact.faq.q2' => 'Are the Koniks really wild horses?',
                'contact.faq.a2' => 'The horses are semi-wild. In summer they are mostly left alone and monitored medically. In winter they are moved to a pasture with enough food.',
                'contact.faq.q3' => 'What breed of horse are they?',
                'contact.faq.a3' => 'The Konik is a robust horse breed from Poland that resembles the extinct Tarpan and is often used in conservation projects.',
                'contact.js.sending' => 'Sending...',
                'contact.js.sent' => 'Message sent!',
                'contact.js.send' => 'Send',
                'contact.js.config_missing' => 'Email service configuration is missing. Please try again later.',
                'contact.js.send_failed' => 'The email could not be sent. Please try again later.',

                'imprint.title' => 'Imprint & Privacy Policy',
                'imprint.heading' => 'Imprint & Privacy Policy',
                'imprint.section.imprint' => 'Imprint',
                'imprint.operator' => 'Operator:',
                'imprint.contact' => 'Contact:',
                'imprint.responsible' => 'Responsible for content according to § 55 Abs. 2 RStV:',
                'imprint.vat' => 'VAT identification number:',
                'imprint.disclaimer' => 'Disclaimer:',
                'imprint.disclaimer.text' => 'The content of this website has been created with great care. However, we cannot guarantee correctness, completeness and timeliness.',
                'imprint.external' => 'External links:',
                'imprint.external.text' => 'This website contains links to external third-party websites whose content we cannot influence. The respective provider is responsible for linked content.',
                'imprint.section.privacy' => 'Privacy Policy',
                'imprint.privacy.text1' => 'Personal data from the contact form (name, email, message) is used only to answer your request and is not shared with third parties.',
                'imprint.privacy.text2' => 'Cookies are only used for technical functionality of the website.',
                'imprint.privacy.text3' => 'More details will follow in a full privacy policy.',
                'imprint.section.reach' => 'How to reach us',
                'imprint.reach.text' => 'If you have questions about imprint or privacy, use the contact form or send an email to the address below.'
            ],
            'es' => [
                'nav.home' => 'Inicio',
                'nav.gallery' => 'Galeria',
                'nav.history' => 'Historia',
                'nav.contact' => 'Contacto',
                'nav.lang_switcher' => 'Selector de idioma',
                'footer.imprint' => 'Aviso legal y privacidad',

                'home.title' => 'Inicio',
                'home.hero.image_alt' => 'Caballos salvajes en un prado',
                'home.hero.title' => 'Caballos Salvajes',
                'home.hero.subtitle' => 'Parque Nacional de la Selva Negra',
                'home.scroll' => 'Desplazate hacia abajo',
                'home.about.heading' => 'Sobre los Konik',
                'home.about.intro' => 'En el Parque Nacional de la Selva Negra, los caballos Konik se usan desde hace anos para mantener los pastizales abiertos de montana llamados "Grinden". Sin pastoreo, estos habitats se cerrarian poco a poco. Los animales proceden de Polonia y conservan genes de antiguos caballos salvajes (Tarpanes).',
                'home.about.habitat.title' => 'Habitat',
                'home.about.habitat.text' => 'Los Konik viven en brezales y praderas del norte de la Selva Negra, alrededor de Zollstock/Hilseneck y cerca de Schliffkopf. Su pastoreo mantiene estas zonas abiertas y diversas.',
                'home.about.habitat.image_alt' => 'Habitat de los caballos',
                'home.about.behavior.title' => 'Comportamiento',
                'home.about.behavior.text' => 'Los Konik son resistentes y adaptables. Viven en manadas y comen hierbas, pastos y a veces corteza o brotes. Su alimentacion favorece la biodiversidad.',
                'home.about.behavior.image_alt' => 'Comportamiento de los Konik',
                'home.about.care.title' => 'Proteccion y cuidado',
                'home.about.care.text' => 'Aunque parecen salvajes, se controlan de forma regular. El personal del parque revisa su salud, cascos y periodos de pastoreo. Los visitantes deben mantener distancia y no alimentarlos.',
                'home.about.care.image_alt' => 'Proteccion y cuidado de los caballos',
                'home.map.heading' => 'Como llegar al parque nacional',
                'home.map.button' => '📍 Mostrar ubicacion',
                'home.map.popup_title' => 'Centro del Parque Nacional de la Selva Negra',
                'home.map.popup_subtitle' => 'Bad Liebenzell-Unterreichenbach',
                'home.map.marker_title' => 'Centro del Parque Nacional de la Selva Negra',
                'home.lightbox.prev' => 'Imagen anterior',
                'home.lightbox.next' => 'Imagen siguiente',
                'home.lightbox.close' => 'Cerrar',

                'gallery.title' => 'Galeria',
                'gallery.lightbox.prev' => 'Imagen anterior',
                'gallery.lightbox.next' => 'Imagen siguiente',
                'gallery.lightbox.close' => 'Cerrar',
                'gallery.upload.title' => '📸 Anadir fotos nuevas',
                'gallery.upload.email' => 'Tu correo (para consultas)',
                'gallery.upload.event_title' => "Titulo (p. ej. 'Dia nevado en el bosque')",
                'gallery.upload.description' => 'Descripcion del evento...',
                'gallery.upload.submit' => 'Subir foto y enviar para revision',
                'gallery.upload.error_prefix' => 'Error:',
                'gallery.upload.token_missing' => 'Error: falta el token de seguridad. Recarga la pagina.',

                'history.title' => 'Historia',
                'history.empty' => 'No hay entradas disponibles en la historia.',

                'contact.title' => 'Contacto',
                'contact.info.title' => 'Mensaje enviado',
                'contact.info.text' => 'Tu mensaje se envio correctamente. Te responderemos lo antes posible.',
                'contact.error.title' => 'Error',
                'contact.error.default' => 'Tu mensaje no se envio.',
                'contact.form.title' => 'Formulario de contacto',
                'contact.form.name' => 'Nombre',
                'contact.form.email' => 'Correo electronico',
                'contact.form.message' => 'Mensaje',
                'contact.form.send' => 'Enviar',
                'contact.faq.title' => 'Preguntas frecuentes',
                'contact.faq.q1' => 'Pueden los visitantes entrar al prado con los Konik?',
                'contact.faq.a1' => 'No, entrar al pastizal esta prohibido. Observa los animales desde detras de la valla y no los alimentes.',
                'contact.faq.q2' => 'Son realmente caballos salvajes?',
                'contact.faq.a2' => 'Los caballos son semisalvajes. En verano viven casi solos y se controlan medicamente. En invierno se trasladan a un pastizal con alimento suficiente.',
                'contact.faq.q3' => 'Que raza de caballo son?',
                'contact.faq.a3' => 'El Konik es una raza robusta de Polonia que se parece al Tarpan extinguido y se usa mucho en proyectos de conservacion.',
                'contact.js.sending' => 'Enviando...',
                'contact.js.sent' => 'Mensaje enviado',
                'contact.js.send' => 'Enviar',
                'contact.js.config_missing' => 'Falta la configuracion del servicio de correo. Intentalo de nuevo mas tarde.',
                'contact.js.send_failed' => 'No se pudo enviar el correo. Intentalo de nuevo mas tarde.',

                'imprint.title' => 'Aviso legal y privacidad',
                'imprint.heading' => 'Aviso legal y privacidad',
                'imprint.section.imprint' => 'Aviso legal',
                'imprint.operator' => 'Titular:',
                'imprint.contact' => 'Contacto:',
                'imprint.responsible' => 'Responsable del contenido segun § 55 Abs. 2 RStV:',
                'imprint.vat' => 'Numero de IVA:',
                'imprint.disclaimer' => 'Descargo de responsabilidad:',
                'imprint.disclaimer.text' => 'El contenido de este sitio web se ha creado con gran cuidado. Sin embargo, no podemos garantizar su exactitud, integridad y actualidad.',
                'imprint.external' => 'Enlaces externos:',
                'imprint.external.text' => 'Este sitio contiene enlaces a paginas externas de terceros sobre las que no tenemos control. El proveedor correspondiente es responsable del contenido enlazado.',
                'imprint.section.privacy' => 'Politica de privacidad',
                'imprint.privacy.text1' => 'Los datos personales del formulario de contacto (nombre, correo, mensaje) se usan solo para responder tu consulta y no se comparten con terceros.',
                'imprint.privacy.text2' => 'Las cookies se utilizan solo para la funcionalidad tecnica del sitio.',
                'imprint.privacy.text3' => 'Pronto publicaremos una politica de privacidad completa.',
                'imprint.section.reach' => 'Como contactarnos',
                'imprint.reach.text' => 'Si tienes preguntas sobre aviso legal o privacidad, usa el formulario de contacto o escribe al correo indicado abajo.'
            ]
        ];

        $lang = current_lang();
        if (isset($translations[$lang][$key])) {
            return $translations[$lang][$key];
        }

        if (isset($translations['en'][$key])) {
            return $translations['en'][$key];
        }

        return (string)$key;
    }
}

if (!function_exists('localized_field')) {
    function localized_field($row, $field, $lang = null) {
        if (!is_array($row)) {
            return '';
        }

        $lang = normalize_language($lang ?? current_lang());
        if ($lang === '') {
            $lang = current_lang();
        }

        $mapKey = $field . '_i18n';
        $map = isset($row[$mapKey]) && is_array($row[$mapKey]) ? $row[$mapKey] : [];

        if ($lang !== '' && isset($map[$lang]) && trim((string)$map[$lang]) !== '') {
            return (string)$map[$lang];
        }

        foreach (['de', 'en', 'es'] as $fallbackLang) {
            if (isset($map[$fallbackLang]) && trim((string)$map[$fallbackLang]) !== '') {
                return (string)$map[$fallbackLang];
            }
        }

        return (string)($row[$field] ?? '');
    }
}

if (!function_exists('translate_text_remote')) {
    function translate_text_remote($text, $sourceLang, $targetLang) {
        $text = trim((string)$text);
        $sourceLang = strtolower(trim((string)$sourceLang));
        if ($sourceLang !== 'auto') {
            $sourceLang = normalize_language($sourceLang);
        }
        $targetLang = normalize_language($targetLang);

        if ($sourceLang === '') {
            $sourceLang = 'auto';
        }

        if ($text === '' || $targetLang === '' || $sourceLang === $targetLang) {
            return $text;
        }

        static $cache = [];
        $cacheKey = $sourceLang . '|' . $targetLang . '|' . md5($text);
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $translated = '';

        $googleUrl = 'https://translate.googleapis.com/translate_a/single?client=gtx&dt=t&sl=' . rawurlencode($sourceLang) . '&tl=' . rawurlencode($targetLang) . '&q=' . rawurlencode($text);

        if (function_exists('curl_init')) {
            $ch = curl_init($googleUrl);
            if ($ch !== false) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                $response = curl_exec($ch);
                $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($response !== false && $status >= 200 && $status < 300) {
                    $decoded = json_decode((string)$response, true);
                    if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                        $parts = [];
                        foreach ($decoded[0] as $chunk) {
                            if (is_array($chunk) && isset($chunk[0])) {
                                $parts[] = (string)$chunk[0];
                            }
                        }
                        $translated = trim(implode('', $parts));
                    }
                }
            }
        }

        if ($translated === '') {
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 6,
                    'ignore_errors' => true,
                ],
            ]);
            $response = @file_get_contents($googleUrl, false, $ctx);
            if ($response !== false) {
                $decoded = json_decode((string)$response, true);
                if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                    $parts = [];
                    foreach ($decoded[0] as $chunk) {
                        if (is_array($chunk) && isset($chunk[0])) {
                            $parts[] = (string)$chunk[0];
                        }
                    }
                    $translated = trim(implode('', $parts));
                }
            }
        }

        if ($translated === '' && class_exists('COM')) {
            try {
                $http = new COM('WinHttp.WinHttpRequest.5.1');
                $http->Open('GET', $googleUrl, false);
                $http->SetTimeouts(3000, 3000, 6000, 6000);
                $http->Send();

                $status = (int)$http->Status;
                if ($status >= 200 && $status < 300) {
                    $response = (string)$http->ResponseText;
                    $decoded = json_decode($response, true);
                    if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                        $parts = [];
                        foreach ($decoded[0] as $chunk) {
                            if (is_array($chunk) && isset($chunk[0])) {
                                $parts[] = (string)$chunk[0];
                            }
                        }
                        $translated = trim(implode('', $parts));
                    }
                }
            } catch (Exception $e) {
            }
        }

        if ($translated === '' && function_exists('shell_exec')) {
            $payload = base64_encode((string)json_encode([
                'text' => $text,
                'source' => $sourceLang,
                'target' => $targetLang,
            ], JSON_UNESCAPED_UNICODE));

            $ps = '$p = \'' . $payload . '\';'
                . '$j = [System.Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($p)) | ConvertFrom-Json;'
                . '$q = [System.Uri]::EscapeDataString([string]$j.text);'
                . '$sl = [string]$j.source; $tl = [string]$j.target;'
                . '$u = "https://translate.googleapis.com/translate_a/single?client=gtx&dt=t&sl=$sl&tl=$tl&q=$q";'
                . 'try { (Invoke-WebRequest -UseBasicParsing -TimeoutSec 8 $u).Content } catch { "" }';

            $utf16Script = function_exists('mb_convert_encoding')
                ? mb_convert_encoding($ps, 'UTF-16LE', 'UTF-8')
                : iconv('UTF-8', 'UTF-16LE', $ps);
            $encodedPs = base64_encode((string)$utf16Script);
            $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -EncodedCommand ' . $encodedPs;
            $response = (string)shell_exec($cmd);
            if ($response !== '') {
                $decoded = json_decode($response, true);
                if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                    $parts = [];
                    foreach ($decoded[0] as $chunk) {
                        if (is_array($chunk) && isset($chunk[0])) {
                            $parts[] = (string)$chunk[0];
                        }
                    }
                    $translated = trim(implode('', $parts));
                }
            }
        }

        if ($translated === '') {
            $fallbackSource = $sourceLang === 'auto' ? 'en' : $sourceLang;
            $url = 'https://api.mymemory.translated.net/get?q=' . rawurlencode($text) . '&langpair=' . rawurlencode($fallbackSource . '|' . $targetLang);

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                if ($ch !== false) {
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                    $response = curl_exec($ch);
                    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($response !== false && $status >= 200 && $status < 300) {
                        $decoded = json_decode((string)$response, true);
                        $translated = trim((string)($decoded['responseData']['translatedText'] ?? ''));
                    }
                }
            }

            if ($translated === '') {
                $ctx = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 5,
                        'ignore_errors' => true,
                    ],
                ]);
                $response = @file_get_contents($url, false, $ctx);
                if ($response !== false) {
                    $decoded = json_decode((string)$response, true);
                    $translated = trim((string)($decoded['responseData']['translatedText'] ?? ''));
                }
            }
        }

        $translated = html_entity_decode((string)$translated, ENT_QUOTES, 'UTF-8');
        if ($translated === '') {
            $translated = $text;
        }

        $cache[$cacheKey] = $translated;
        return $translated;
    }
}

if (!function_exists('detect_text_language')) {
    function detect_text_language($text) {
        $text = strtolower(trim((string)$text));
        if ($text === '') {
            return 'de';
        }

        if (preg_match('/[äöüß]/u', $text) === 1) {
            return 'de';
        }
        if (preg_match('/[áéíóúñ¿¡]/u', $text) === 1) {
            return 'es';
        }

        $deHits = preg_match_all('/\b(und|der|die|das|nicht|mit|fuer|von|im|ist)\b/u', $text, $m1);
        $esHits = preg_match_all('/\b(el|la|los|las|que|con|para|una|un|del|por)\b/u', $text, $m2);
        $enHits = preg_match_all('/\b(the|and|for|with|from|this|that|is|are|to|in)\b/u', $text, $m3);

        if ($deHits > $esHits && $deHits > $enHits) {
            return 'de';
        }
        if ($esHits > $deHits && $esHits > $enHits) {
            return 'es';
        }

        return 'en';
    }
}

if (!function_exists('build_i18n_text_map')) {
    function build_i18n_text_map($text, $sourceLang = null, $maxLength = 3000) {
        $sourceLang = normalize_language($sourceLang ?? '');
        if ($sourceLang === '') {
            $sourceLang = detect_text_language($text);
        }

        $text = trim((string)$text);
        if ($maxLength > 0) {
            $text = function_exists('mb_substr') ? mb_substr($text, 0, $maxLength) : substr($text, 0, $maxLength);
        }

        $result = [];
        foreach (supported_languages() as $lang) {
            if ($lang === $sourceLang) {
                $result[$lang] = $text;
            } else {
                $translated = translate_text_remote($text, 'auto', $lang);
                if ($maxLength > 0) {
                    $translated = function_exists('mb_substr') ? mb_substr($translated, 0, $maxLength) : substr($translated, 0, $maxLength);
                }
                $result[$lang] = $translated;
            }
        }

        return $result;
    }
}
