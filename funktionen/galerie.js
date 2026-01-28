fetch("datenbank/informationen/galerie-informationen.json")
.then(res => res.json())
.then(data => {
    const galerie = document.querySelector('.galerie');
    data.sort((a, b)=>b.jahr-a.jahr);
    data.forEach(jahrData => {
        const section = document.createElement('div');
        section.className = 'jahr-section';
        section.id = `Jahr-${jahrData.jahr}`;
        section.innerHTML = `<h2>${jahrData.jahr}</h2><div class=bilder></div>`;
        const bilderDiv = section.querySelector(".bilder");
        jahrData.bilder.forEach(bild => {
            const bildElement = document.createElement("img");
            bildElement.src = bild.src;
            bildElement.alt = bild.alt;
            bildElement.loading = "lazy";
            bildElement.onerror = () => bildElement.src = "datenbank/bilder/error.jpg";
            bilderDiv.appendChild(bildElement);
        });
        galerie.appendChild(section);
        
    });
});