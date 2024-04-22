document.addEventListener('turbo:load', function () {
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
});