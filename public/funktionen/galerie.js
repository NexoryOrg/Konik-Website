const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-image");
const lightboxClose = document.getElementById("lightbox-close");
const lightboxAlt = document.getElementById("lightbox-alt");
const lightboxJahr = document.getElementById("lightbox-year");
const navLeft = document.querySelector(".nav-prev");
const navRight = document.querySelector(".nav-next");

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

// Lightbox display function
function showImage() {
    const image = allImages[currentIndex];
    if(!image) return;
    lightboxImg.classList.remove("show");
    setTimeout(() => {
        lightboxImg.src = image.src;
        lightboxImg.alt = image.alt;
        lightboxAlt.textContent = image.alt;
        lightboxJahr.textContent = image.year;
        lightboxImg.classList.add("show");
    }, 50);
}

lightboxClose.addEventListener("click", () => lightbox.classList.remove("active"));
lightbox.addEventListener("click", e => { if(e.target === lightbox) lightbox.classList.remove("active"); });

document.addEventListener("keydown", e => {
    if(!lightbox.classList.contains("active")) return;
    if(e.key === "ArrowRight") { currentIndex = (currentIndex + 1) % allImages.length; showImage(); }
    if(e.key === "ArrowLeft") { currentIndex = (currentIndex - 1 + allImages.length) % allImages.length; showImage(); }
    if(e.key === "Escape") lightbox.classList.remove("active");
});

let startX = 0;
lightbox.addEventListener("touchstart", e => startX = e.touches[0].clientX);
lightbox.addEventListener("touchend", e => {
    const diff = startX - e.changedTouches[0].clientX;
    if(Math.abs(diff) > 50){
        if(diff > 0) currentIndex = (currentIndex + 1) % allImages.length;
        else currentIndex = (currentIndex - 1 + allImages.length) % allImages.length;
        showImage();
    }
});

navLeft.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + allImages.length) % allImages.length;
    showImage();
});
navRight.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % allImages.length;
    showImage();
});