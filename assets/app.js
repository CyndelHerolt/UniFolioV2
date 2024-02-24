import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import 'fontawesome';
// import tinymce from "tinymce";

// import monthSelectPlugin from "flatpickr/dist/plugins/monthSelect";

// Initialisation de flatpickr sur l'élément dateRealisation
flatpickr("#trace_abstract_dateRealisation", {
    plugins: [monthSelectPlugin({
        shorthand: true, // true for 12/2020, false for December 2020
        dateFormat: "m-Y", // see https://flatpickr.js.org/formatting/
        altFormat: "F Y",
    })],
    locale: "fr",
});
document.addEventListener('turbo:load', function() {
    // Obtenez l'URL de la page actuelle
    const currentUrl = new URL(window.location.href);
    console.log(currentUrl.pathname);

    // Vérifiez si l'URL correspond à la route 'app_trace_new'
    if (currentUrl.pathname === '/etudiant/trace/new') {
        // Si c'est le cas, initialisez TinyMCE
        if (window.tinymce) {
            tinymce.remove();
            tinymce.init({
                selector: '#trace_abstract_description',
                plugins: 'lists, table, codesample, link',
                toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent link | codesample',
                menubar: 'edit view format table tools',
                language: 'fr_FR',
            });
        }
    }
});