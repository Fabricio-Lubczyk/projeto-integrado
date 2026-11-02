document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const slides = [...carousel.querySelectorAll('[data-slide]')];
    const controls = carousel.querySelector('[data-carousel-controls]');
    if (slides.length < 2 || !controls) return;

    const dots = [...controls.querySelectorAll('[data-slide-to]')];
    let current = 0;
    const show = (index) => {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => { slide.hidden = i !== current; });
        dots.forEach((dot, i) => dot.setAttribute('aria-pressed', String(i === current)));
        controls.querySelector('[data-carousel-status]').textContent = `${current + 1} / ${slides.length}`;
    };
    controls.hidden = false;
    controls.querySelector('[data-prev]').addEventListener('click', () => show(current - 1));
    controls.querySelector('[data-next]').addEventListener('click', () => show(current + 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));
    carousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            show(current + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });
});
