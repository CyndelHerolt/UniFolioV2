document.addEventListener('turbo:load', function () {
    // Obtenez l'URL de la page actuelle
    const currentUrl = new URL(window.location.href);

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