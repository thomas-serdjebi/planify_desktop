/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';


document.addEventListener('DOMContentLoaded', function () {

    // Activer tous les tooltips Bootstrap sur la page
    document.querySelectorAll('[title]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
    const selectAllBtn = document.getElementById('select-all-users');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const selectedCountEl = document.getElementById('selected-count');
    let allSelected = false;

    function updateSelectedCount() {
        const count = Array.from(checkboxes).filter(cb => cb.checked).length;
        selectedCountEl.textContent = `(${count})`;
    }

    // Mise à jour quand on clique sur le bouton "Tout sélectionner"
    selectAllBtn.addEventListener('click', function () {
        allSelected = !allSelected;
        checkboxes.forEach(cb => cb.checked = allSelected);

        selectAllBtn.textContent = allSelected ? 'Tout désélectionner' : 'Tout sélectionner';
        selectAllBtn.classList.toggle('btn-outline-danger', allSelected);

        updateSelectedCount();
    });

    // Mise à jour aussi si l'utilisateur sélectionne manuellement
    checkboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));

    // Initialiser le compteur au chargement
    updateSelectedCount();
});

