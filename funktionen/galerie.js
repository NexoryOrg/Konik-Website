const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const lightboxClose = document.getElementById("lightbox-close");
const lightboxAlt = document.getElementById("lightbox-alt");
const lightboxJahr = document.getElementById("lightbox-jahr");
const navLeft = document.querySelector(".nav-left");
const navRight = document.querySelector(".nav-right");

let alleBilder = [];
let aktuellerIndex = 0;

fetch("datenbank/informationen/galerie-informationen.json")
.then(res => res.json())
.then(data => {
    const galerie = document.querySelector('.galerie');
    data.sort((a,b)=> b.jahr - a.jahr);

    data.forEach(jahrData => {
        const section = document.createElement("div");
        section.className = "jahr-section";
        section.innerHTML = `<h2>${jahrData.jahr}</h2><div class="bilder"></div>`;
        const bilderDiv = section.querySelector(".bilder");

        jahrData.bilder.forEach(bild => {
            const img = document.createElement("img");
            img.src = bild.src;
            img.alt = bild.alt;
            img.loading = "lazy";
            img.onerror = () => img.src = "datenbank/bilder/error.jpg";

            const index = alleBilder.length;
            alleBilder.push({ src: bild.src, alt: bild.alt, jahr: jahrData.jahr });

            img.addEventListener("click", () => {
                aktuellerIndex = index;
                zeigeBild();
                lightbox.classList.add("active");
            });

            bilderDiv.appendChild(img);
        });

        galerie.appendChild(section);
    });
});

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