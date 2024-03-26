import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['inputValeur'];

    connect() {

    }

    addValeur(event) {
        // Récupération du prototype
        const prototype = document.querySelector('#critere_valeurs').dataset.prototype;
        // Récupération du nombre de champs contenu
        const index = document.querySelectorAll('.criteres_valeurs').length;
        // si index = 4, on ne peut plus ajouter de critères
        if (index >= 4) {
            return;
        }
        // Création du nouveau champ contenu
        const newForm = prototype.replace(/__name__/g, index);
        // Ajout du nouveau champ contenu au formulaire
        const formGroup = document.createElement('div');
        // Injection du prototype et des btns ds le nouveau bloc
        formGroup.classList.add('critere_valeurs_div');
        formGroup.classList.add('new_critere_valeur');
        formGroup.innerHTML = newForm;
        formGroup.innerHTML += (
            '<div class="col-6">' +
            '<label class="form-label" for="valeur">Label</label>\n' +
            '<input type="text" id="valeur" class="form-control">' +
            '<button type="button" class="btn btn-danger delete-critere-valeur" data-action="critereCollection#removeValeur">' +
            'Supprimer' +
            '</button>' +
            '</div>'
        )

        const button = formGroup.querySelector('.delete-critere-valeur');
        formGroup.appendChild(button);

        this.inputValeurTarget.appendChild(formGroup);
    }

    removeValeur(event) {
        event.currentTarget.closest('.critere_valeurs_div').remove();
    }
}