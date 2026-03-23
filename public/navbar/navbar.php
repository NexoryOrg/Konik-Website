<nav class="navbar">
  <div class="logo-container">
    <a href="<?= e(with_lang('/home/index.php')) ?>"><img src="/database/images/logo/logo.png" alt="Logo" class="logo"></a>
  </div>
  <ul class="navbar-menu">
    <li><a href="<?= e(with_lang('/home/index.php')) ?>"><?= e(t('nav.home')) ?></a></li>
    <li><a href="<?= e(with_lang('/gallery/gallery.php')) ?>"><?= e(t('nav.gallery')) ?></a></li>
    <li><a href="<?= e(with_lang('/history/history.php')) ?>"><?= e(t('nav.history')) ?></a></li>
    <li><a href="<?= e(with_lang('/contact/contact.php')) ?>"><?= e(t('nav.contact')) ?></a></li>
  </ul>

  <div class="lang-switch" aria-label="<?= e(t('nav.lang_switcher')) ?>">
    <a class="<?= current_lang() === 'de' ? 'active' : '' ?>" href="<?= e(current_url_with_lang('de')) ?>">DE</a>
    <a class="<?= current_lang() === 'en' ? 'active' : '' ?>" href="<?= e(current_url_with_lang('en')) ?>">EN</a>
    <a class="<?= current_lang() === 'es' ? 'active' : '' ?>" href="<?= e(current_url_with_lang('es')) ?>">ES</a>
  </div>

  <div class="navbar-toggle" id="navbar-toggle">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>

</nav>
