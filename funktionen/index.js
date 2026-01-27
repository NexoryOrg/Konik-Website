const hint = document.getElementById('scrollHint');
let hasScrolled = window.scrollY > 10;

if (hasScrolled) {
    hint.classList.add('hidden');
}

const interval = setInterval(() => {
    if (hasScrolled) return;

    hint.classList.add('wiggle');

    setTimeout(() => {
        hint.classList.remove('wiggle');
    }, 600);
}, 10000);

window.addEventListener('scroll', () => {
    if (hasScrolled) return;

    if (window.scrollY > 5) {
        hasScrolled = true;
        hint.classList.add('hidden');
        clearInterval(interval);
    }
}, { passive: true });

document.getElementById("load-map").addEventListener("click", function() {
  const mapDiv = document.getElementById("map");
  const placeholder = document.getElementById("map-placeholder");

  mapDiv.style.display = "block";
  placeholder.style.display = "none";

  const map = L.map('map').setView([48.5606575, 8.2220008], 19);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);

  const marker = L.marker([48.5606575, 8.2220008]).addTo(map)
    .bindPopup('<b>Nationalparkzentrum Schwarzwald</b>').openPopup();

  map.flyTo([48.5606575, 8.2220008], 17, { duration: 2 });
});