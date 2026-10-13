import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['collectionContainer'];
    static values = {
        index: Number,
        prototype: String
    };

    connect() {
        this.indexValue = this.collectionContainerTarget.children.length;
    }

    addItem(event) {
        event.preventDefault();

        const newFormHtml = this.prototypeValue.replace(/__name__/g, this.indexValue);

        const template = document.createElement('template');
        template.innerHTML = newFormHtml.trim();
        const newItem = template.content.firstElementChild;

        this.collectionContainerTarget.appendChild(newItem);

        this.indexValue++;
    }

    removeItem(event) {
        event.preventDefault();

        const item = event.target.closest('.collection-item');
        if (item) {
            item.remove();
        }
    }
}
