const images = document.querySelectorAll(".images img");
const lightbox = document.getElementById("lightbox");
const lightboxImage = document.getElementById("lightbox-image");
const description = document.getElementById("description");
const imageYear = document.getElementById("image-year");
const closeBtn = document.getElementById("close");
const nextBtn = document.querySelector(".next");
const prevBtn = document.querySelector(".prev");

const yearSections = document.querySelectorAll('.year-section');
const dots = document.querySelectorAll('.history-dot');

let currentIndex = 0;

const lazyImages = document.querySelectorAll('img[data-src]');
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                obs.unobserve(img);
            }
        });
    }, { rootMargin: '200px' });
    lazyImages.forEach(img => io.observe(img));
} else {
    lazyImages.forEach(img => img.src = img.dataset.src);
}

if (images.length && lightbox && lightboxImage) {

    images.forEach((img, index) => {
        img.addEventListener("click", () => {
            currentIndex = index;
            updateLightbox();
            lightbox.classList.add("active");
            document.body.style.overflow = "hidden";
        });
    });

    function updateLightbox() {
        lightboxImage.style.opacity = 0;

        setTimeout(() => {
            lightboxImage.src = images[currentIndex].src;
            description.textContent = images[currentIndex].alt || "";

            const section = images[currentIndex].closest('.year-section');
            const year = section ? section.querySelector('h2').textContent : "";
            imageYear.textContent = year;

            lightboxImage.style.opacity = 1;
        }, 150);
    }

    function closeLightbox() {
        lightbox.classList.remove("active");
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
        if (!lightbox.classList.contains("active")) return;

        if (e.key === "Escape") closeLightbox();
        if (e.key === "ArrowRight" && nextBtn) nextBtn.click();
        if (e.key === "ArrowLeft" && prevBtn) prevBtn.click();
    });

    let startX = 0;

    lightboxImage.addEventListener("touchstart", e => {
        startX = e.touches[0].clientX;
    });

    lightboxImage.addEventListener("touchend", e => {
        const endX = e.changedTouches[0].clientX;

        if (startX - endX > 50 && nextBtn) nextBtn.click();
        if (endX - startX > 50 && prevBtn) prevBtn.click();
    });
}

dots.forEach(dot => {
    dot.addEventListener('click', () => {
        const year = dot.dataset.year;
        const target = document.getElementById('year-' + year);

        if (target) {
            const offset = 120;
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    });
});

function updateActiveDot() {
    let current = 0;
    yearSections.forEach((section, index) => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= window.innerHeight * 0.4) {
            current = index;
        }
    });

    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[current]) {
        dots[current].classList.add('active');
        const container = document.querySelector('.history-box');
        if (container && container.scrollHeight > container.clientHeight) {
            dots[current].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (window.innerWidth <= 768) {
            dots[current].scrollIntoView({ behavior: 'smooth', inline: 'center' });
        }
    }
}

window.addEventListener('scroll', updateActiveDot);
window.addEventListener('resize', updateActiveDot);
updateActiveDot();
