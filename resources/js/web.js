// Chování veřejného webu: mobilní navigace, odhalování sekcí, návod k zaměření, slidery.
import { createSlider } from './slider.js';

// Mobilní menu
const navToggle = document.querySelector('[data-nav-toggle]');
const navMenu = document.querySelector('[data-nav-menu]');

if (navToggle && navMenu) {
    const setOpen = (open) => {
        navMenu.classList.toggle('hidden', !open);
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    navToggle.addEventListener('click', () => setOpen(navMenu.classList.contains('hidden')));

    // Po kliknutí na odkaz menu zavřít
    navMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));

    // Esc zavře menu a vrátí fokus na tlačítko
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !navMenu.classList.contains('hidden')) {
            setOpen(false);
            navToggle.focus();
        }
    });
}

// Jemné odhalení sekcí při scrollu (respektuje prefers-reduced-motion — bez
// preference přechod nedefinuje CSS, třída je pak neškodná)
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 },
    );

    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
} else {
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('is-visible'));
}

// Poptávkový formulář: volba „zaměřím si svépomocí“ rozbalí návod
const diyRadios = document.querySelectorAll('[data-measurement-radio]');
const diyInfo = document.querySelector('[data-measurement-info]');

if (diyRadios.length && diyInfo) {
    const sync = () => {
        const diy = document.querySelector('[data-measurement-radio][value="diy"]');
        diyInfo.classList.toggle('hidden', !(diy && diy.checked));
    };

    diyRadios.forEach((radio) => radio.addEventListener('change', sync));
    sync();
}

// Úvodní hero: snímky a k nim svázané texty se přepínají společně.
const hero = document.querySelector('[data-hero]');

if (hero) {
    createSlider(hero, {
        autoplay: true,
        interval: 6000,
        effect: 'fade',
        linked: [[...hero.querySelectorAll('[data-hero-copy]')]],
    });
}

// Ostatní galerie (např. stránka vypínaného podhledu): prolínání, bez autoplay.
document.querySelectorAll('[data-gallery]').forEach((gallery) =>
    createSlider(gallery, {
        effect: 'fade',
        counter: gallery.closest('figure')?.querySelector('[data-slider-counter]') ?? undefined,
    }),
);
