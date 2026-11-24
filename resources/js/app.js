// Potvrzení před odesláním formuláře: data-confirm="…" na formuláři nebo na tlačítku.
// Místo inline onsubmit/onclick, kde by text s apostrofem (např. jméno z poptávky)
// mohl vyskočit z JS řetězce a spustit cizí kód.
document.addEventListener('submit', (event) => {
    const message = event.submitter?.dataset.confirm ?? event.target.dataset.confirm;

    if (message && ! window.confirm(message)) {
        event.preventDefault();
    }
});