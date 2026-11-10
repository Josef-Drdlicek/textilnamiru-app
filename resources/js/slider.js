/**
 * Sdílený slider pro úvodní hero i galerie.
 *
 * Markup: kořen [data-slider] obsahuje [data-slider-slide], volitelně
 * [data-slider-prev], [data-slider-next], [data-slider-dot], [data-slider-counter],
 * [data-slider-pause]. Další skupiny prvků, které se mají přepínat společně se
 * snímky (např. texty hera), se předají v `options.linked`.
 *
 * Přístupnost: neaktivní snímky dostanou `inert` + `aria-hidden`; autoplay se
 * nespustí při prefers-reduced-motion, pauzuje při hoveru a klávesnicovém fokusu
 * a jde vypnout tlačítkem pauzy (WCAG 2.2.2). Po ruční změně snímku se odpočet
 * intervalu spustí znovu. Šipky vlevo/vpravo přepínají snímky.
 */
export function createSlider(root, options = {}) {
    const {
        autoplay = false,
        interval = 6000,
        effect = 'slide', // 'slide' | 'fade'
        linked = [], // další seznamy prvků přepínané spolu se snímky
        counter = root.querySelector('[data-slider-counter]'), // může ležet i mimo kořen (figcaption)
        swipeThreshold = 40,
    } = options;

    const slides = [...root.querySelectorAll('[data-slider-slide]')];
    const count = slides.length;

    if (count < 2) {
        return null;
    }

    const dots = [...root.querySelectorAll('[data-slider-dot]')];
    const prevBtn = root.querySelector('[data-slider-prev]');
    const nextBtn = root.querySelector('[data-slider-next]');
    const pauseBtn = root.querySelector('[data-slider-pause]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let index = 0;
    let timer = null;
    let playing = autoplay && !reducedMotion;
    let paused = false; // dočasná pauza (hover, fokus, skrytá karta)

    const wrap = (i) => ((i % count) + count) % count;

    const setGroup = (elements, active, direction) => {
        elements.forEach((el, i) => {
            const on = i === active;
            el.classList.toggle('is-active', on);
            el.classList.toggle('is-before', !on && i < active);
            el.classList.toggle('is-after', !on && i > active);
            el.toggleAttribute('inert', !on);
            el.setAttribute('aria-hidden', on ? 'false' : 'true');
            if (direction !== 0) {
                el.dataset.sliderDirection = direction > 0 ? 'forward' : 'backward';
            }
        });
    };

    const render = (direction = 0) => {
        setGroup(slides, index, direction);
        linked.forEach((group) => setGroup(group, index, direction));

        dots.forEach((dot, i) => {
            const on = i === index;
            dot.classList.toggle('is-active', on);
            if (on) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });

        if (counter) {
            counter.textContent = `${index + 1} / ${count}`;
        }
    };

    const preloadNext = () => {
        const img = slides[wrap(index + 1)].querySelector('img[loading="lazy"]');
        if (img) {
            img.loading = 'eager';
        }
    };

    const goTo = (next, direction = next > index ? 1 : -1) => {
        const target = wrap(next);
        if (target === index) {
            return;
        }
        index = target;
        render(direction);
        preloadNext();
    };

    const stop = () => {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        stop();
        if (!playing || paused || document.hidden) {
            return;
        }
        timer = setInterval(() => goTo(index + 1, 1), interval);
    };

    const renderPauseButton = () => {
        if (!pauseBtn) {
            return;
        }
        pauseBtn.hidden = reducedMotion || !autoplay;
        pauseBtn.setAttribute('aria-pressed', playing ? 'false' : 'true');
        pauseBtn.setAttribute('aria-label', playing ? 'Pozastavit automatické přepínání' : 'Spustit automatické přepínání');
        pauseBtn.querySelectorAll('[data-when]').forEach((icon) => {
            icon.hidden = icon.dataset.when !== (playing ? 'playing' : 'paused');
        });
    };

    // Ruční změna snímku: odpočet začíná znovu, aby snímek nezmizel hned po kliknutí.
    const userGoTo = (next, direction) => {
        goTo(next, direction);
        start();
    };

    prevBtn?.addEventListener('click', () => userGoTo(index - 1, -1));
    nextBtn?.addEventListener('click', () => userGoTo(index + 1, 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => userGoTo(i)));

    pauseBtn?.addEventListener('click', () => {
        playing = !playing;
        renderPauseButton();
        start();
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            userGoTo(index - 1, -1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            userGoTo(index + 1, 1);
        }
    });

    const pause = () => {
        paused = true;
        stop();
    };
    const resume = () => {
        paused = false;
        start();
    };

    root.addEventListener('mouseenter', pause);
    root.addEventListener('mouseleave', resume);
    root.addEventListener('focusin', (event) => {
        // Klik myší nechá fokus na tlačítku, pauzu proto vyvolá jen klávesnicový fokus.
        if (event.target.matches(':focus-visible')) {
            pause();
        }
    });
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) {
            resume();
        }
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

    // Swipe: vodorovné tažení prstem přepne snímek, svislé nechá stránku scrollovat.
    let touch = null;
    root.addEventListener(
        'touchstart',
        (event) => {
            if (event.touches.length !== 1 || event.target.closest('button, a')) {
                return;
            }
            touch = { x: event.touches[0].clientX, y: event.touches[0].clientY };
        },
        { passive: true },
    );
    root.addEventListener(
        'touchend',
        (event) => {
            if (!touch) {
                return;
            }
            const dx = event.changedTouches[0].clientX - touch.x;
            const dy = event.changedTouches[0].clientY - touch.y;
            touch = null;
            if (Math.abs(dx) < swipeThreshold || Math.abs(dx) < Math.abs(dy)) {
                return;
            }
            userGoTo(dx < 0 ? index + 1 : index - 1, dx < 0 ? 1 : -1);
        },
        { passive: true },
    );

    root.classList.add(`slider-${effect}`, 'is-ready');
    render();
    renderPauseButton();
    preloadNext();
    start();

    return { goTo, stop };
}
