import { Controller } from '@hotwired/stimulus';

/*
 * Repli de la colonne de nav des pages admin (tablette/PC, la colonne est de
 * toute façon masquée sous 768px). L'état replié/déplié est purement piloté
 * en CSS via .admin-shell--nav-collapsed (voir admin.css) ; ce contrôleur ne
 * fait que basculer la classe et se souvenir du choix d'une page à l'autre
 * (navigation classique, pas de SPA) via localStorage.
 */
const STORAGE_KEY = 'admin-nav-collapsed';

export default class extends Controller {
    static targets = ['shell', 'toggleButton', 'toggleLabel', 'toggleIcon'];

    connect() {
        this.apply(window.localStorage.getItem(STORAGE_KEY) === '1');
    }

    toggle() {
        const collapsed = !this.shellTarget.classList.contains('admin-shell--nav-collapsed');
        window.localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        this.apply(collapsed);
    }

    apply(collapsed) {
        this.shellTarget.classList.toggle('admin-shell--nav-collapsed', collapsed);
        this.toggleButtonTarget.setAttribute('aria-expanded', String(!collapsed));
        this.toggleLabelTarget.textContent = collapsed ? 'Afficher le menu' : 'Masquer le menu';
        this.toggleIconTarget.classList.toggle('bi-layout-sidebar-inset', !collapsed);
        this.toggleIconTarget.classList.toggle('bi-layout-sidebar', collapsed);
    }
}
