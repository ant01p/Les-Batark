import { Controller } from '@hotwired/stimulus';

/*
 * Distribue les cartes commande dans de vraies colonnes DOM indépendantes
 * (une colonne = un conteneur flex séparé), pour qu'ouvrir/fermer une carte
 * ne recalcule jamais la position des autres. Contrairement à une grille
 * flex/grid classique ou au CSS multi-colonnes (column-count), qui
 * rééquilibrent/déplacent les éléments entre colonnes dès que la hauteur
 * totale du contenu change, chaque colonne ici est un conteneur DOM séparé :
 * seules les cartes suivantes de LA MÊME colonne sont poussées vers le bas.
 *
 * La distribution ne se recalcule que si le nombre de colonnes change (seuils
 * responsive, comme l'ancienne grille Bootstrap col-md-6/col-xl-4) — jamais
 * quand une carte change de hauteur (ouverture/fermeture du détail).
 */
export default class extends Controller {
    static targets = ['item'];

    connect() {
        this.items = [...this.itemTargets];
        this.currentColumnCount = null;
        this.onResize = this.onResize.bind(this);
        window.addEventListener('resize', this.onResize);
        this.render();
    }

    disconnect() {
        window.removeEventListener('resize', this.onResize);
        window.clearTimeout(this.resizeTimer);
    }

    onResize() {
        window.clearTimeout(this.resizeTimer);
        this.resizeTimer = window.setTimeout(() => this.render(), 150);
    }

    render() {
        const columnCount = this.getColumnCount();

        if (columnCount === this.currentColumnCount) {
            return;
        }

        this.currentColumnCount = columnCount;
        this.element.classList.toggle('is-multi-column', columnCount > 1);

        this.element.querySelectorAll(':scope > .order-cards-masonry-column').forEach((column) => {
            column.remove();
        });

        const columns = Array.from({ length: columnCount }, () => {
            const column = document.createElement('div');
            column.className = 'order-cards-masonry-column';
            this.element.appendChild(column);

            return column;
        });

        this.items.forEach((item, index) => {
            columns[index % columnCount].appendChild(item);
        });
    }

    getColumnCount() {
        if (window.innerWidth >= 1200) {
            return 3;
        }

        if (window.innerWidth >= 768) {
            return 2;
        }

        return 1;
    }
}
