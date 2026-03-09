const hero = document.querySelector('.hero img');
if (hero) {
    window.addEventListener('scroll', () => {
        const offset = window.scrollY;
        hero.style.transform = `translateY(${offset * 0.3}px)`;
    });
}

const scrollArrow = document.getElementById('scrollArrow');
if (scrollArrow) {
    scrollArrow.addEventListener('click', () => {
        const target = document.querySelector('.text-section');
        if (target) target.scrollIntoView({ behavior: 'smooth' });
    });

    let hasScrolled = window.scrollY > 10;
    if (hasScrolled) scrollArrow.style.opacity = 0;
    window.addEventListener('scroll', () => {
        if (!hasScrolled && window.scrollY > 5) {
            hasScrolled = true;
            scrollArrow.style.opacity = 0;
        }
    });
}

const boxes = document.querySelectorAll('.info-box');
if (boxes.length) {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.3 });
    boxes.forEach(box => observer.observe(box));
}

const loadMapBtn = document.getElementById("load-map");
if (loadMapBtn) {
    loadMapBtn.addEventListener("click", function() {
        const mapDiv = document.getElementById("map");
        const placeholder = document.getElementById("map-placeholder");
        if (mapDiv && placeholder) {
            mapDiv.style.display = "block";
            placeholder.style.display = "none";

            const map = L.map('map').setView([48.5606575, 8.2220008], 19);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            L.marker([48.5606575, 8.2220008]).addTo(map)
                .bindPopup('<b>Nationalparkzentrum Schwarzwald</b>').openPopup();
            map.flyTo([48.5606575, 8.2220008], 16, { duration: 2 });
        }
    });
}

const images = document.querySelectorAll(".gallery-thumb");
const lightbox = document.getElementById("lightbox");
const lightboxImage = document.getElementById("lightbox-image");
const description = document.getElementById("description");
const closeBtn = document.getElementById("close");
const nextBtn = document.querySelector(".next");
const prevBtn = document.querySelector(".prev");

let currentIndex = 0;

if (images.length && lightbox && lightboxImage) {
    images.forEach((img, index) => {
        img.addEventListener("click", () => {
            currentIndex = index;
            updateLightbox();
            lightbox.style.display = "flex";
            document.body.style.overflow = "hidden";
        });
    });

    function updateLightbox() {
        lightboxImage.style.opacity = 0;
        setTimeout(() => {
            lightboxImage.src = images[currentIndex].src;
            description.textContent = images[currentIndex].alt || "";
            lightboxImage.style.opacity = 1;
        }, 150);
    }

    function closeLightbox() {
        lightbox.style.display = "none";
        document.body.style.overflow = "";
    }

    if (closeBtn) closeBtn.addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", e => {
        if (e.target === lightbox) closeLightbox();
    });

    if (nextBtn) nextBtn.addEventListener("click", () => {
        currentIndex = (currentIndex + 1) % images.length;
        updateLightbox();
    });
    if (prevBtn) prevBtn.addEventListener("click", () => {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateLightbox();
    });

    document.addEventListener("keydown", e => {
        if (lightbox.style.display !== "flex") return;
        if (e.key === "Escape") closeLightbox();
        if (e.key === "ArrowRight" && nextBtn) nextBtn.click();
        if (e.key === "ArrowLeft" && prevBtn) prevBtn.click();
    });

    let startX = 0;
    lightboxImage.addEventListener("touchstart", e => { startX = e.touches[0].clientX; });
    lightboxImage.addEventListener("touchend", e => {
        const endX = e.changedTouches[0].clientX;
        if (startX - endX > 50 && nextBtn) nextBtn.click();
        if (endX - startX > 50 && prevBtn) prevBtn.click();
    });
}