// import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
window.bootstrap = bootstrap; // Attacher bootstrap à window
import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import './js/a11y.js';
import './js/tinymce.js';
import './js/theme.js';


// Le reste de votre code...

// une fois que tout est chargé
document.addEventListener('DOMContentLoaded', (event) => {
    // Initialisation de flatpickr sur l'élément dateRealisation
    flatpickr("#trace_abstract_dateRealisation", {
        plugins: [monthSelectPlugin({
            shorthand: true, // true for 12/2020, false for December 2020
            dateFormat: "m-Y", // see https://flatpickr.js.org/formatting/
            altFormat: "F Y",
        })],
        locale: "fr",
    });

    // Importation dynamique de _tooltip.js
    import('./js/_tooltip.js').then(module => {
        // Le module _tooltip.js est maintenant chargé et exécuté.
        console.log('Tooltip module loaded successfully');
    }).catch(error => {
        console.error('Error loading the tooltip module:', error);
    });
});

// si il y a un turbo rechargement de la page
document.addEventListener('turbo:load', (event) => {
    // Initialisation de flatpickr sur l'élément dateRealisation
    flatpickr("#trace_abstract_dateRealisation", {
        plugins: [monthSelectPlugin({
            shorthand: true, // true for 12/2020, false for December 2020
            dateFormat: "m-Y", // see https://flatpickr.js.org/formatting/
            altFormat: "F Y",
        })],
        locale: "fr",
    });
});

