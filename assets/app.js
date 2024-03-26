import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import './js/a11y.js';
import './js/tinymce.js';

// Initialisation de flatpickr sur l'élément dateRealisation
flatpickr("#trace_abstract_dateRealisation", {
    plugins: [monthSelectPlugin({
        shorthand: true, // true for 12/2020, false for December 2020
        dateFormat: "m-Y", // see https://flatpickr.js.org/formatting/
        altFormat: "F Y",
    })],
    locale: "fr",
});

// const valeurs = document.querySelectorAll('.criteres_valeurs-input');
// valeurs.forEach(valeur => {
//     const key = '<div class="col-4">' +
//         // '<label class="form-label" for="valeur">Valeur</label>' +
//         '<select name="valeurs" id="valeur" class="form-select">' +
//         '<option value="1">1</option>' +
//         '<option value="2">2</option>' +
//         '<option value="3">3</option>' +
//         '<option value="4">4</option>' +
//         '</select></div>';
//
//     valeur.closest("div").classList.add('d-flex')
//     valeur.closest("div").innerHTML += key;
// });