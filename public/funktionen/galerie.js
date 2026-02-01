let allImages = [];
let currentIndex = 0;

// Collect all images from the DOM
document.querySelectorAll('.year-section').forEach(section => {
    const year = section.querySelector('h2').textContent;
    section.querySelectorAll('img').forEach(img => {
        const index = allImages.length;
        allImages.push({ src: img.src, alt: img.alt, year: year });
        img.addEventListener('click', () => {
            currentIndex = index;
            showImage();
            lightbox.classList.add('active');
        });
    });
});

// Timeline scroll tracking
const yearSections = document.querySelectorAll('.year-section');
const dots = document.querySelectorAll('.timeline-dot');
window.addEventListener('scroll', () => {
    let index = 0;
    yearSections.forEach((section, i) => {
        if(section.getBoundingClientRect().top < window.innerHeight / 2) index = i;
    });
    dots.forEach(p => p.classList.remove('active'));
    dots[index].classList.add('active');
});

const images = document.querySelectorAll(".images img");
const lightbox = document.getElementById("lightbox");
const lightboxImage = document.getElementById("lightbox-image");
const description = document.getElementById("description");
const imageYear = document.getElementById("image-year");
const closeBtn = document.getElementById("close");
const nextBtn = document.querySelector(".next");
const prevBtn = document.querySelector(".prev");

let currentIndexGallery = 0;

if (images.length && lightbox && lightboxImage) {
    images.forEach((img, index) => {
        img.addEventListener("click", () => {
            currentIndexGallery = index;
            updateLightbox();
            lightbox.style.display = "flex";
            document.body.style.overflow = "hidden";
        });
    });

    function updateLightbox() {
        lightboxImage.style.opacity = 0;
        setTimeout(() => {
            lightboxImage.src = images[currentIndexGallery].src;
            description.textContent = images[currentIndexGallery].alt || "";
            imageYear.textContent = allImages[currentIndexGallery].year || "";
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
        currentIndexGallery = (currentIndexGallery + 1) % images.length;
        updateLightbox();
    });
    if (prevBtn) prevBtn.addEventListener("click", () => {
        currentIndexGallery = (currentIndexGallery - 1 + images.length) % images.length;
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