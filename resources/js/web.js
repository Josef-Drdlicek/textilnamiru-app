// Chování veřejného webu: mobilní navigace, odhalování sekcí, návod k zaměření.

// Mobilní menu
const navToggle = document.querySelector('[data-nav-toggle]');
const navMenu = document.querySelector('[data-nav-menu]');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
        const open = navMenu.classList.toggle('hidden') === false;
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Po kliknutí na kotvu menu zavřít
    navMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            navMenu.classList.add('hidden');
            navToggle.setAttribute('aria-expanded', 'false');
        });
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
