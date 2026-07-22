import { Controller } from '@hotwired/stimulus';

/*
 * Une instance par carte commande (data-controller sur .order-card) : chaque
 * carte s'ouvre/se ferme indépendamment. L'animation (hauteur, opacité,
 * décalage, rotation d'icône, libellé du bouton) est entièrement pilotée en
 * CSS via .order-collapse.is-open et [aria-expanded] (voir admin.css) — ce
 * contrôleur ne fait que basculer un attribut + une classe, sans mesurer de
 * hauteur, pour rester fluide quel que soit le nombre d'articles.
 */
export default class extends Controller {
    static targets = ['collapse', 'button'];

    toggle() {
        const expanded = this.buttonTarget.getAttribute('aria-expanded') === 'true';
        this.buttonTarget.setAttribute('aria-expanded', String(!expanded));
        this.collapseTarget.classList.toggle('is-open', !expanded);
    }
}
