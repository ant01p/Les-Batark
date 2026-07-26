import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item', 'moreButton', 'lessButton'];
    static values = { pageSize: { type: Number, default: 4 } };

    connect() {
        this.visibleCount = this.pageSizeValue;
        this.render();
    }

    showMore() {
        this.visibleCount = Math.min(this.visibleCount + this.pageSizeValue, this.itemTargets.length);
        this.render();
    }

    showLess() {
        this.visibleCount = this.pageSizeValue;
        this.render();
    }

    render() {
        this.itemTargets.forEach((item, index) => {
            item.classList.toggle('d-none', index >= this.visibleCount);
        });

        this.moreButtonTarget.classList.toggle('d-none', this.visibleCount >= this.itemTargets.length);
        this.lessButtonTarget.classList.toggle('d-none', this.visibleCount <= this.pageSizeValue);
    }
}
