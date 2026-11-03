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

// Úvodní slider: posuv přes CSS transform (vždy, i v Chromu s reduced-motion).
const heroRoot = document.querySelector('[data-hero-root]');
const heroSlider = heroRoot?.querySelector('[data-hero-slider]');

if (heroRoot && heroSlider) {
    const INTERVAL_MS = 6000;
    const DURATION_MS = 600;
    const DRAG_RATIO = 0.2;
    const FLICK_VELOCITY = 0.45;
    const canHover = window.matchMedia('(hover: hover)').matches;

    const slides = [...heroSlider.querySelectorAll('[data-hero-slide]')];
    const copies = [...heroRoot.querySelectorAll('[data-hero-copy]')];
    const copyTrack = heroRoot.querySelector('[data-hero-copy-track]');
    const dots = [...heroSlider.querySelectorAll('[data-hero-dot]')];
    const prevBtn = heroSlider.querySelector('[data-hero-prev]');
    const nextBtn = heroSlider.querySelector('[data-hero-next]');
    const count = slides.length;

    let index = 0;
    let timer = null;
    let hovering = false;
    let allowHoverPause = false;
    let animating = false;
    let pendingDelta = 0;
    let pendingAbs = null;
    let drag = null;

    const wrap = (i) => ((i % count) + count) % count;

    const setInstant = (on) => {
        heroRoot.classList.toggle('is-hero-instant', on);
    };

    const flush = () => {
        void heroRoot.offsetWidth;
    };

    const setX = (el, percent) => {
        el.style.transform = `translate3d(${percent}%, 0, 0)`;
    };

    const pairX = (i, percent) => {
        setX(slides[i], percent);
        setX(copies[i], percent);
    };

    const markActive = (active) => {
        slides.forEach((slide, i) => {
            const on = i === active;
            slide.classList.toggle('pointer-events-none', !on);
            slide.setAttribute('aria-hidden', on ? 'false' : 'true');
        });
        copies.forEach((copy, i) => {
            const on = i === active;
            copy.classList.toggle('pointer-events-none', !on);
            copy.setAttribute('aria-hidden', on ? 'false' : 'true');
        });
        dots.forEach((dot, i) => {
            const on = i === active;
            dot.classList.toggle('bg-ink', on);
            dot.classList.toggle('scale-125', on);
            dot.classList.toggle('bg-white/70', !on);
            if (on) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
    };

    const preloadSlide = (slideEl) => {
        slideEl?.querySelectorAll('source[srcset], img[src]').forEach((node) => {
            const url = node.getAttribute('srcset') || node.getAttribute('src');
            if (url) {
                const probe = new Image();
                probe.src = url;
            }
        });
    };

    setInstant(true);
    slides.forEach((_, i) => pairX(i, i === 0 ? 0 : 100));
    flush();
    setInstant(false);
    markActive(0);
    slides.slice(1).forEach(preloadSlide);

    const stopTimer = () => {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    const startTimer = () => {
        stopTimer();
        if (document.hidden || animating || drag) {
            return;
        }
        if (allowHoverPause && hovering) {
            return;
        }
        timer = setInterval(() => requestGo(index + 1, 1), INTERVAL_MS);
    };

    const runQueued = () => {
        if (pendingAbs !== null && pendingAbs !== index) {
            const next = pendingAbs;
            const dir = next > index ? 1 : -1;
            pendingAbs = null;
            pendingDelta = 0;
            animateTo(next, dir);
            return;
        }
        pendingAbs = null;
        if (pendingDelta !== 0) {
            const dir = pendingDelta > 0 ? 1 : -1;
            pendingDelta -= dir;
            animateTo(wrap(index + dir), dir);
            return;
        }
        startTimer();
    };

    const finishAnim = (to) => {
        index = to;
        setInstant(true);
        slides.forEach((_, i) => {
            if (i !== to) {
                pairX(i, 100);
            }
        });
        flush();
        setInstant(false);
        markActive(to);
        animating = false;
        runQueued();
    };

    const afterTransition = (el, onDone) => {
        let done = false;
        const settle = (event) => {
            if (event && event.target !== el) {
                return;
            }
            if (done) {
                return;
            }
            done = true;
            onDone();
        };
        el.addEventListener('transitionend', settle, { once: true });
        window.setTimeout(() => settle(null), DURATION_MS + 80);
    };

    const animateTo = (next, dir) => {
        const to = wrap(next);
        if (to === index || animating) {
            return;
        }

        preloadSlide(slides[to]);
        stopTimer();
        animating = true;

        const from = index;
        const incomingStart = dir > 0 ? 100 : -100;
        const outgoingEnd = dir > 0 ? -100 : 100;

        // Nejdřív snímek položit bez animace, po překreslení zapnout CSS transition.
        setInstant(true);
        pairX(to, incomingStart);
        flush();
        setInstant(false);
        flush();
        pairX(from, outgoingEnd);
        pairX(to, 0);
        afterTransition(slides[to], () => finishAnim(to));
    };

    const requestGo = (next, dir) => {
        const to = wrap(next);
        if (to === index && !animating) {
            return;
        }
        if (animating) {
            pendingAbs = null;
            pendingDelta += dir > 0 ? 1 : -1;
            return;
        }
        animateTo(to, dir);
    };

    const goAbs = (to) => {
        const target = wrap(to);
        if (target === index && !animating) {
            return;
        }
        const dir = target > index ? 1 : -1;
        if (animating) {
            pendingDelta = 0;
            pendingAbs = target;
            return;
        }
        animateTo(target, dir);
    };

    prevBtn?.addEventListener('click', () => requestGo(index - 1, -1));
    nextBtn?.addEventListener('click', () => requestGo(index + 1, 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => goAbs(i)));

    if (canHover) {
        heroSlider.addEventListener('mouseenter', () => {
            hovering = true;
            if (allowHoverPause) {
                stopTimer();
            }
        });
        heroSlider.addEventListener('mouseleave', () => {
            hovering = false;
            allowHoverPause = true;
            startTimer();
        });
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopTimer();
        } else {
            startTimer();
        }
    });

    const bindSwipe = (el) => {
        if (!el) {
            return;
        }

        el.addEventListener(
            'touchstart',
            (event) => {
                if (animating || event.touches.length !== 1 || event.target.closest('button, a')) {
                    return;
                }
                const touch = event.changedTouches[0];
                drag = {
                    id: touch.identifier,
                    startX: touch.clientX,
                    startY: touch.clientY,
                    startT: event.timeStamp,
                    axis: null,
                    neighbor: null,
                    pct: 0,
                };
                stopTimer();
            },
            { passive: true },
        );

        el.addEventListener(
            'touchmove',
            (event) => {
                if (!drag) {
                    return;
                }
                const touch =
                    [...event.changedTouches].find((item) => item.identifier === drag.id) || event.touches[0];
                if (!touch) {
                    return;
                }
                const dx = touch.clientX - drag.startX;
                const dy = touch.clientY - drag.startY;
                if (drag.axis === null) {
                    if (Math.abs(dx) < 8 && Math.abs(dy) < 8) {
                        return;
                    }
                    drag.axis = Math.abs(dx) > Math.abs(dy) ? 'x' : 'y';
                    if (drag.axis === 'y') {
                        drag = null;
                        startTimer();
                        return;
                    }
                    setInstant(true);
                }
                if (drag.axis !== 'x') {
                    return;
                }
                event.preventDefault();
                const width = heroSlider.offsetWidth || 1;
                const pct = (dx / width) * 100;
                const dir = pct > 0 ? -1 : 1;
                drag.neighbor = wrap(index + dir);
                drag.pct = pct;
                pairX(index, pct);
                pairX(drag.neighbor, (pct > 0 ? -100 : 100) + pct);
            },
            { passive: false },
        );

        el.addEventListener(
            'touchend',
            (event) => {
                if (!drag) {
                    return;
                }
                const ended = drag;
                drag = null;
                if (ended.axis !== 'x') {
                    setInstant(false);
                    pairX(index, 0);
                    startTimer();
                    return;
                }

                const touch =
                    [...event.changedTouches].find((item) => item.identifier === ended.id) || event.changedTouches[0];
                const dx = touch.clientX - ended.startX;
                const elapsed = Math.max(16, event.timeStamp - ended.startT);
                const commit = Math.abs(ended.pct) >= DRAG_RATIO * 100 || Math.abs(dx) / elapsed >= FLICK_VELOCITY;
                const neighbor = ended.neighbor;
                const rest = ended.pct > 0 ? -100 : 100;

                flush();
                setInstant(false);
                flush();

                if (!commit) {
                    animating = true;
                    pairX(index, 0);
                    if (neighbor !== null && neighbor !== index) {
                        pairX(neighbor, rest);
                    }
                    window.setTimeout(() => {
                        animating = false;
                        startTimer();
                    }, DURATION_MS + 80);
                    return;
                }

                const dir = ended.pct < 0 ? 1 : -1;
                const to = wrap(index + dir);
                preloadSlide(slides[to]);
                animating = true;
                pairX(index, dir > 0 ? -100 : 100);
                pairX(to, 0);
                afterTransition(slides[to], () => finishAnim(to));
            },
            { passive: true },
        );
    };

    bindSwipe(heroSlider);
    bindSwipe(copyTrack);

    startTimer();
}

// B2B formulář je zatím jen vzhled — nic se neodesílá.
const b2bForm = document.querySelector('[data-b2b-form]');
const b2bMessage = document.querySelector('[data-b2b-message]');

if (b2bForm && b2bMessage) {
    b2bForm.addEventListener('submit', (event) => {
        event.preventDefault();
        // DOPLNIT: napojení na backend
        b2bMessage.classList.remove('hidden');
    });
}
