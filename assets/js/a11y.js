// document.addEventListener('turbo:load', function () {
//     let contrastButton = document.querySelector('.contrast-view');
//     let contrastView = localStorage.getItem('contrastView') === 'true';
//
//     // Appliquer l'état de la vue contrastée lors du chargement de la page
//     if (contrastView) {
//         document.documentElement.style.setProperty('--primary-color', '#424242');
//         document.documentElement.style.setProperty('--primary-color-dark', '#000000');
//         document.documentElement.style.setProperty('--secondary-color', '#ffda48');
//         contrastButton.textContent = 'Vue normale';
//     } else {
//         document.documentElement.style.setProperty('--primary-color', '#9a68da');
//         document.documentElement.style.setProperty('--primary-color-dark', '#7D4ABE');
//         document.documentElement.style.setProperty('--secondary-color', '#FFE483');
//         contrastButton.textContent = 'Vue contrastée';
//     }
//
//     contrastButton.addEventListener('click', function () {
//         if (contrastView === true) {
//             contrastView = false;
//             localStorage.setItem('contrastView', 'false');
//             document.documentElement.style.setProperty('--primary-color', '#9a68da');
//             document.documentElement.style.setProperty('--primary-color-dark', '#7D4ABE');
//             document.documentElement.style.setProperty('--secondary-color', '#FFE483');
//             contrastButton.textContent = 'Vue contrastée';
//         } else {
//             contrastView = true;
//             localStorage.setItem('contrastView', 'true');
//             document.documentElement.style.setProperty('--primary-color', '#424242');
//             document.documentElement.style.setProperty('--primary-color-dark', '#000000');
//             document.documentElement.style.setProperty('--secondary-color', '#ffda48');
//             contrastButton.textContent = 'Vue normale';
//         }
//     });
// });