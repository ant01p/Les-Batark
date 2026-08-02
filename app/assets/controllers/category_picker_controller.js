import { Controller } from '@hotwired/stimulus';

/*
 * Modifier/supprimer une catégorie (Boutique admin) : plus de boutons Modifier/Suppr.
 * par catégorie, les boutons "Modifier une catégorie" / "Supprimer une catégorie"
 * activent un mode (catégories mises en valeur en CSS via .admin-category-btn--*-mode),
 * et le clic suivant sur une catégorie déclenche l'action au lieu de filtrer les
 * produits (comportement normal du bouton hors mode).
 */
export default class extends Controller {
    static targets = ['category', 'editButton', 'deleteButton', 'hint'];

    connect() {
        this.mode = null;
    }

    toggleEdit(event) {
        event.preventDefault();
        this.setMode(this.mode === 'edit' ? null : 'edit');
    }

    toggleDelete(event) {
        event.preventDefault();
        this.setMode(this.mode === 'delete' ? null : 'delete');
    }

    pick(event) {
        if (!this.mode) {
            return;
        }

        event.preventDefault();

        const category = event.currentTarget;

        if (this.mode === 'edit') {
            window.location.assign(category.dataset.editUrl);
        } else {
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById(category.dataset.deleteModal)).show();
        }

        this.setMode(null);
    }

    setMode(mode) {
        this.mode = mode;
        this.editButtonTarget.classList.toggle('active', mode === 'edit');
        this.deleteButtonTarget.classList.toggle('active', mode === 'delete');
        this.hintTarget.textContent = mode === 'edit'
            ? 'Cliquez sur la catégorie à modifier…'
            : mode === 'delete'
                ? 'Cliquez sur la catégorie à supprimer…'
                : '';

        this.categoryTargets.forEach((category) => {
            category.classList.toggle('admin-category-btn--edit-mode', mode === 'edit');
            category.classList.toggle('admin-category-btn--delete-mode', mode === 'delete');
        });
    }
}
