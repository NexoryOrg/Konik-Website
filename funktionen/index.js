// Parallax Hero
const hero = document.querySelector('.hero img');
window.addEventListener('scroll', () => {
    const offset = window.scrollY;
    hero.style.transform = `translateY(${offset * 0.3}px)`;
});

// Scroll Hint
const hint = document.getElementById('scrollHint');
hint.addEventListener('click', () => {
    document.querySelector('.text-section').scrollIntoView({ behavior: 'smooth' });
});

let hasScrolled = window.scrollY > 10;
if (hasScrolled) hint.style.opacity = 0;

window.addEventListener('scroll', () => {
    if (!hasScrolled && window.scrollY > 5) {
        hasScrolled = true;
        hint.style.opacity = 0;
    }
});

// Fade-in Info Boxes
const boxes = document.querySelectorAll('.info-box');
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if(entry.isIntersecting) entry.target.classList.add('visible');
    });
}, { threshold: 0.3 });
boxes.forEach(box => observer.observe(box));

// Map
document.getElementById("load-map").addEventListener("click", function() {
    const mapDiv = document.getElementById("map");
    const placeholder = document.getElementById("map-placeholder");
    mapDiv.style.display = "block";
    placeholder.style.display = "none";

    const map = L.map('map').setView([48.5606575, 8.2220008], 19);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    const marker = L.marker([48.5606575, 8.2220008]).addTo(map)
        .bindPopup('<b>Nationalparkzentrum Schwarzwald</b>').openPopup();
    map.flyTo([48.5606575, 8.2220008], 16, { duration: 2 });
});

// Lightbox mit Slide
const images = document.querySelectorAll(".lightbox-img");
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const caption = document.getElementById("caption");
const closeBtn = document.getElementById("close");
const nextBtn = document.querySelector(".next");
const prevBtn = document.querySelector(".prev");

let currentIndex = 0;

images.forEach((img, index) => {
    img.addEventListener("click", () => {
        currentIndex = index;
        updateLightbox();
        lightbox.style.display = "flex";
        document.body.style.overflow = "hidden";
    });
});

function updateLightbox() {
    lightboxImg.style.opacity = 0;
    setTimeout(() => {
        lightboxImg.src = images[currentIndex].src;
        caption.textContent = images[currentIndex].alt || "";
        lightboxImg.style.opacity = 1;
    }, 150);
}

function closeLightbox() {
    lightbox.style.display = "none";
    document.body.style.overflow = "";
}

closeBtn.addEventListener("click", closeLightbox);
lightbox.addEventListener("click", e => {
    if (e.target === lightbox) closeLightbox();
});

nextBtn.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % images.length;
    updateLightbox();
});
prevBtn.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateLightbox();
});

document.addEventListener("keydown", e => {
    if (lightbox.style.display !== "flex") return;
    if (e.key === "Escape") closeLightbox();
    if (e.key === "ArrowRight") nextBtn.click();
    if (e.key === "ArrowLeft") prevBtn.click();
});

let startX = 0;
lightboxImg.addEventListener("touchstart", e => { startX = e.touches[0].clientX; });
lightboxImg.addEventListener("touchend", e => {
    const endX = e.changedTouches[0].clientX;
    if (startX - endX > 50) nextBtn.click();
    if (endX - startX > 50) prevBtn.click();
});