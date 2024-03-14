import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['inputSoft', 'inputHard', 'inputLangue', 'inputReseau', 'inputExperience', 'inputExperienceActivite', 'inputFormation']

    connect() {
        console.log('connected');
    }


    addSoftSkill(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_soft_skills').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.soft_skills').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('soft_skills_div');
        formGroup.classList.add('new_soft_skill');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-soft-skill" data-action="cvCollection#removeSoftSkill">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-soft-skill');        formGroup.appendChild(button);
        const addButtonSoft = document.querySelector('.add-soft-skill');        // ajouter le nouveau formGroup dans la target input

        this.inputSoftTarget.appendChild(formGroup);
    }

    removeSoftSkill(event) {
        event.currentTarget.closest('.soft_skills_div').remove();
    }


    addHardSkill(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_hard_skills').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.hard_skills').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('hard_skills_div');
        formGroup.classList.add('new_hard_skill');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-hard-skill" data-action="cvCollection#removeHardSkill">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-hard-skill');        formGroup.appendChild(button);
        const addButtonHard = document.querySelector('.add-hard-skill');        // ajouter le nouveau formGroup dans la target input

        this.inputHardTarget.appendChild(formGroup);
    }

    removeHardSkill(event) {
        event.currentTarget.closest('.hard_skills_div').remove();
    }


    addLangue(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_langues').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.langues').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('langues_div');
        formGroup.classList.add('new_langue');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-langue" data-action="cvCollection#removeLangue">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-langue');        formGroup.appendChild(button);
        const addButtonLangue = document.querySelector('.add-langue');        // ajouter le nouveau formGroup dans la target input

        this.inputLangueTarget.appendChild(formGroup);
    }

    removeLangue(event) {
        event.currentTarget.closest('.langues_div').remove();
    }

    addReseau(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_reseaux').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.reseaux').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('reseaux_div');
        formGroup.classList.add('new_reseau');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-reseau" data-action="cvCollection#removeReseau">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-reseau');        formGroup.appendChild(button);
        const addButtonReseau = document.querySelector('.add-reseau');        // ajouter le nouveau formGroup dans la target input

        this.inputReseauTarget.appendChild(formGroup);
    }

    removeReseau(event) {
        event.currentTarget.closest('.reseaux_div').remove();
    }


    addExperience(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_experiences').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.experiences').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('experiences_div');
        formGroup.classList.add('new_experience');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<div data-cvCollection-target="inputExperienceActivite" class="cv-input">' +
            '</div>' +
            '<button type="button" class="btn btn-primary add-experience-activite" data-action="cvCollection#addExperienceActivite">' +
            'Ajouter une activité' +
            '</button>'
        )
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-experience" data-action="cvCollection#removeExperience">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-experience');
        formGroup.appendChild(button);
        const addButtonExp = document.querySelector('.add-experience');        // ajouter le nouveau formGroup dans la target input

        this.inputExperienceTarget.appendChild(formGroup);
    }

    addExperienceActivite(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_experiences_0_activites').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.experience-activite').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('experience_activite_div');
        formGroup.classList.add('new_experience_activite');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-experience-activite" data-action="cvCollection#removeExperienceActivite">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-experience-activite');        formGroup.appendChild(button);
        const addButtonExperienceActivite = document.querySelector('.add-experience-activite');        // ajouter le nouveau formGroup dans la target input

        this.inputExperienceActiviteTarget.appendChild(formGroup);
    }

    removeExperienceActivite(event) {
        event.currentTarget.closest('.reseaux_div').remove();
    }

    removeExperience(event) {
        event.currentTarget.closest('.experiences_div').remove();
    }

    addFormation(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#cv_formations').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.formations').length;
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('formations_div');
        formGroup.classList.add('new_formation');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<button type="button" class="btn btn-danger delete-formation" data-action="cvCollection#removeFormation">' +
            'Supprimer' +
            '</button>'
        )

        const button = formGroup.querySelector('.delete-formation');
        formGroup.appendChild(button);
        const addButtonFormation = document.querySelector('.add-formation');        // ajouter le nouveau formGroup dans la target input

        this.inputFormationTarget.appendChild(formGroup);
    }

    removeFormation(event) {
        event.currentTarget.closest('.formations_div').remove();
    }
}