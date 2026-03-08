window.addEventListener('DOMContentLoaded', () => {
    const imgs = document.querySelectorAll('img.timeline-img[data-src]');
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
        imgs.forEach(i => io.observe(i));
    } else {
        imgs.forEach(i => i.src = i.dataset.src);
    }
});
