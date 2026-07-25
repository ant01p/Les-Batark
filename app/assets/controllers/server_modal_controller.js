import { Controller } from '@hotwired/stimulus';

/*
 * Vue agrandie des cartes serveur : toutes les cartes sont déjà présentes
 * dans le DOM (une par serveur, cf. server/_server_modal.html.twig) et
 * empilées dans une même cellule de grille. Naviguer entre serveurs ne fait
 * que basculer des classes CSS (is-active / is-enter-* / is-exit-*) sur ces
 * cartes existantes — jamais de HTML reconstruit ni de requête serveur.
 */
export default class extends Controller {
    static targets = ['grid', 'trigger', 'modal', 'overlay', 'prevButton', 'nextButton', 'closeButton', 'stage', 'card'];

    connect() {
        this.activeIndex = 0;
        this.isOpen = false;
        this.isAnimating = false;
        this.onKeydown = this.onKeydown.bind(this);
    }

    disconnect() {
        this.unlockScroll();
        document.removeEventListener('keydown', this.onKeydown);
    }

    open(event) {
        if (event.type === 'keydown') {
            event.preventDefault();
        }

        // Ignore les clics qui viennent des flèches/indicateurs du carrousel interne.
        if (event.target.closest('.carousel-control-prev, .carousel-control-next, .carousel-indicators')) {
            return;
        }

        const index = Number(event.params.index) || 0;

        this.activeIndex = index;
        this.cardTargets.forEach((card) => card.classList.remove('is-active', 'is-enter-left', 'is-enter-right', 'is-exit-left', 'is-exit-right'));
        this.cardTargets[index]?.classList.add('is-active');

        this.modalTarget.classList.add('is-open');
        this.gridTarget?.classList.add('is-dimmed');
        this.lockScroll();
        document.addEventListener('keydown', this.onKeydown);
        this.isOpen = true;

        window.requestAnimationFrame(() => this.closeButtonTarget.focus());
    }

    close() {
        if (!this.isOpen) {
            return;
        }

        this.modalTarget.classList.remove('is-open');
        this.gridTarget?.classList.remove('is-dimmed');
        this.unlockScroll();
        document.removeEventListener('keydown', this.onKeydown);
        this.isOpen = false;

        this.triggerTargets[this.activeIndex]?.focus();
    }

    prev() {
        this.navigate(-1);
    }

    next() {
        this.navigate(1);
    }

    onKeydown(event) {
        if (event.key === 'Escape') {
            this.close();
        } else if (event.key === 'ArrowRight') {
            this.next();
        } else if (event.key === 'ArrowLeft') {
            this.prev();
        }
    }

    navigate(step) {
        if (!this.isOpen || this.isAnimating) {
            return;
        }

        const total = this.cardTargets.length;
        const newIndex = (this.activeIndex + step + total) % total;
        const direction = step > 0 ? 'next' : 'prev';

        this.showCard(newIndex, direction);
    }

    showCard(newIndex, direction) {
        const cards = this.cardTargets;
        const oldCard = cards[this.activeIndex];
        const newCard = cards[newIndex];

        if (!newCard || newCard === oldCard) {
            return;
        }

        this.isAnimating = true;
        this.setNavDisabled(true);

        oldCard.classList.remove('is-active');
        newCard.classList.add(direction === 'next' ? 'is-enter-right' : 'is-enter-left');
        oldCard.classList.add(direction === 'next' ? 'is-exit-left' : 'is-exit-right');

        // Force le calcul de style avant de basculer vers l'état actif,
        // pour que la transition parte bien de la position d'entrée.
        void newCard.offsetWidth;

        window.requestAnimationFrame(() => {
            newCard.classList.add('is-active');
            newCard.classList.remove('is-enter-left', 'is-enter-right');
        });

        this.activeIndex = newIndex;

        window.clearTimeout(this.transitionTimer);
        this.transitionTimer = window.setTimeout(() => {
            cards.forEach((card) => {
                if (card !== newCard) {
                    card.classList.remove('is-active', 'is-enter-left', 'is-enter-right', 'is-exit-left', 'is-exit-right');
                }
            });
            this.isAnimating = false;
            this.setNavDisabled(false);
        }, 340);
    }

    setNavDisabled(disabled) {
        this.prevButtonTarget.disabled = disabled;
        this.nextButtonTarget.disabled = disabled;
    }

    lockScroll() {
        document.body.classList.add('server-modal-scroll-lock');
    }

    unlockScroll() {
        document.body.classList.remove('server-modal-scroll-lock');
    }
}
