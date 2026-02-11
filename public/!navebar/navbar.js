const toggle = document.getElementById('navbar-toggle');
const menu = document.querySelector('.navbar-menu');

toggle.addEventListener('click', () => {
  menu.classList.toggle('active');
});