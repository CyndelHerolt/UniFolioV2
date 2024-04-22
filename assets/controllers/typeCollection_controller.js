import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input']

    connect() {
        const liensContainer = document.getElementById('trace_lien_contenu')
        if (liensContainer) {
            const liens = liensContainer.children
// ajouter la classe new_lien aux liens
            for (let i = 0; i < liens.length; i++) {
                liens[i].classList.add('existing_lien')
            }

// pour chaque lien, ajouter un bouton supprimer
            const liensDiv = document.querySelectorAll('.existing_lien')
            liensDiv.forEach(lien => {
                lien.innerHTML += (
                    '<button type="button" class="btn btn-danger delete-lien" data-action="typeCollection#removeExistingLien"> Supprimer </button>'
                )
                const button = lien.querySelector('.delete-lien');
                lien.appendChild(button);
            })
        }

        const videosContainer = document.getElementById('trace_video_contenu')
        if (videosContainer) {
            const videos = videosContainer.children
// ajouter la classe new_video aux videos
            for (let i = 0; i < videos.length; i++) {
                videos[i].classList.add('existing_video')
            }

// pour chaque video, ajouter un bouton supprimer
            const videosDiv = document.querySelectorAll('.existing_video')
            videosDiv.forEach(video => {
                video.innerHTML += (
                    '<button type="button" class="btn btn-danger delete-video" data-action="typeCollection#removeExistingVideo"> Supprimer </button>'
                )
                const button = video.querySelector('.delete-video');
                video.appendChild(button);
            })
        }
    }

    addImage(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#trace_image_contenu').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.image_trace').length;
        console.log(index)
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        formGroup.classList.add('image_trace_div');
        formGroup.classList.add('new_img');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-image" data-action="typeCollection#removeImage">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-image');
        formGroup.appendChild(button);
        const addButtonImg = document.querySelector('.add-image');
        // ajouter le nouveau formGroup dans la target input

        this.inputTarget.appendChild(formGroup);

        // addButtonImg.parentNode.insertBefore(formGroup, addButtonImg);
    }

    removeImage(event) {
        event.currentTarget.closest('.image_trace_div').remove();
    }

    addLien(event) {
// Récupération du prototype
        const prototype = document.querySelector('#trace_lien_contenu').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.lien_trace').length;
        console.log(index)
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        formGroup.classList.add('lien_trace_div');
        formGroup.classList.add('new_lien');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-lien" data-action="typeCollection#removeLien">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-lien');
        formGroup.appendChild(button);
        const addButtonLien = document.querySelector('.add-lien');
        // ajouter le nouveau formGroup dans la target input

        this.inputTarget.appendChild(formGroup);

        // addButtonImg.parentNode.insertBefore(formGroup, addButtonImg);
    }

    removeLien(event) {
        event.currentTarget.closest('.lien_trace_div').remove();
    }

    removeExistingLien(event) {
        event.currentTarget.closest('.existing_lien').remove();
    }

    addPdf(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#trace_pdf_contenu').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.pdf_trace').length;
        console.log(index)
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        formGroup.classList.add('pdf_trace_div');
        formGroup.classList.add('new_pdf');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-pdf" data-action="typeCollection#removePdf">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-pdf');
        formGroup.appendChild(button);
        const addButtonPdf = document.querySelector('.add-pdf');
        // ajouter le nouveau formGroup dans la target input

        this.inputTarget.appendChild(formGroup);

        // addButtonImg.parentNode.insertBefore(formGroup, addButtonImg);
    }

    removePdf(event) {
        event.currentTarget.closest('.pdf_trace_div').remove();
    }

    addVideo(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#trace_video_contenu').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.video_trace').length;
        console.log(index)
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        formGroup.classList.add('video_trace_div');
        formGroup.classList.add('new_video');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-video" data-action="typeCollection#removeVideo">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-video');
        formGroup.appendChild(button);
        const addButtonVideo = document.querySelector('.add-video');
        // ajouter le nouveau formGroup dans la target input

        this.inputTarget.appendChild(formGroup);

        // addButtonImg.parentNode.insertBefore(formGroup, addButtonImg);
    }

    removeVideo(event) {
        event.currentTarget.closest('.video_trace_div').remove();
    }

    removeExistingVideo(event) {
        event.currentTarget.closest('.existing_video').remove();
    }
}