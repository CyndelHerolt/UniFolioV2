import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import 'fontawesome';

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