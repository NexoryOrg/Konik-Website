const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const lightboxClose = document.getElementById("lightbox-close");
const lightboxAlt = document.getElementById("lightbox-alt");
const lightboxJahr = document.getElementById("lightbox-jahr");
const navLeft = document.querySelector(".nav-left");
const navRight = document.querySelector(".nav-right");

let alleBilder = [];
let aktuellerIndex = 0;

// Alle Bilder aus dem DOM sammeln
document.querySelectorAll('.jahr-section').forEach(section => {
    const jahr = section.querySelector('h2').textContent;
    section.querySelectorAll('img').forEach(img => {
        const index = alleBilder.length;
        alleBilder.push({ src: img.src, alt: img.alt, jahr: jahr });
        img.addEventListener('click', () => {
            aktuellerIndex = index;
            zeigeBild();
            lightbox.classList.add('active');
        });
    });
});

// Scroll für Zeitstrahl
const jahreSections = document.querySelectorAll('.jahr-section');
const punkte = document.querySelectorAll('.zeitpunkt');
window.addEventListener('scroll', () => {
    let index = 0;
    jahreSections.forEach((section, i) => {
        if(section.getBoundingClientRect().top < window.innerHeight / 2) index = i;
    });
    punkte.forEach(p => p.classList.remove('active'));
    punkte[index].classList.add('active');
});

// Lightbox-Funktion
function zeigeBild() {
    const bild = alleBilder[aktuellerIndex];
    if(!bild) return;
    lightboxImg.classList.remove("show");
    setTimeout(() => {
        lightboxImg.src = bild.src;
        lightboxImg.alt = bild.alt;
        lightboxAlt.textContent = bild.alt;
        lightboxJahr.textContent = bild.jahr;
        lightboxImg.classList.add("show");
    }, 50);
}

lightboxClose.addEventListener("click", () => lightbox.classList.remove("active"));
lightbox.addEventListener("click", e => { if(e.target === lightbox) lightbox.classList.remove("active"); });

document.addEventListener("keydown", e => {
    if(!lightbox.classList.contains("active")) return;
    if(e.key === "ArrowRight") { aktuellerIndex = (aktuellerIndex + 1) % alleBilder.length; zeigeBild(); }
    if(e.key === "ArrowLeft") { aktuellerIndex = (aktuellerIndex - 1 + alleBilder.length) % alleBilder.length; zeigeBild(); }
    if(e.key === "Escape") lightbox.classList.remove("active");
});

let startX = 0;
lightbox.addEventListener("touchstart", e => startX = e.touches[0].clientX);
lightbox.addEventListener("touchend", e => {
    const diff = startX - e.changedTouches[0].clientX;
    if(Math.abs(diff) > 50){
        if(diff > 0) aktuellerIndex = (aktuellerIndex + 1) % alleBilder.length;
        else aktuellerIndex = (aktuellerIndex - 1 + alleBilder.length) % alleBilder.length;
        zeigeBild();
    }
});

navLeft.addEventListener("click", () => {
    aktuellerIndex = (aktuellerIndex - 1 + alleBilder.length) % alleBilder.length;
    zeigeBild();
});
navRight.addEventListener("click", () => {
    aktuellerIndex = (aktuellerIndex + 1) % alleBilder.length;
    zeigeBild();
});