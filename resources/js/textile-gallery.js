/**
 * Galerie produktové stránky látky: hlavní fotka se přepíná podle barvy.
 *
 * - Klik na náhled barvy u fotky vymění fotku a pošle barvu kalkulačce
 *   (Livewire událost `textile-color-picked`).
 * - Výběr barvy v kroku „Barva“ v kalkulačce pošle `textile-color-changed`
 *   s adresou fotky a galerie ji ukáže.
 */
export function initTextileGallery(root = document) {
    const gallery = root.querySelector('[data-textile-gallery]');
    const main = gallery?.querySelector('[data-textile-main]');

    if (!gallery || !main) {
        return;
    }

    const swatches = [...gallery.querySelectorAll('[data-textile-color]')];

    const show = (name, image) => {
        if (image) {
            main.src = image;
        }
        swatches.forEach((swatch) => {
            const on = swatch.dataset.textileColor === name;
            swatch.classList.toggle('is-active', on);
            swatch.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    };

    swatches.forEach((swatch) => {
        swatch.setAttribute('aria-pressed', 'false');
        swatch.addEventListener('click', () => {
            show(swatch.dataset.textileColor, swatch.dataset.image);
            window.Livewire?.dispatch('textile-color-picked', { name: swatch.dataset.textileColor });
        });
    });

    window.addEventListener('textile-color-changed', (event) => {
        show(event.detail?.name, event.detail?.image);
    });
}
