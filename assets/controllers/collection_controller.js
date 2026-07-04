import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['collection'];
    static values = {
        allowAdd: Boolean,
        allowDelete: Boolean,
        prototype: String,
        index: Number,
        buttonAddHtml: String,
        buttonDeleteHtml: String,
    };

    connect() {
        this.indexValue = this.collectionTarget.children.length;

        if (this.allowAddValue) {
            this._addAddButton();
        }

        if (this.allowDeleteValue) {
            Array.from(this.collectionTarget.children).forEach((item) => {
                this._addDeleteButton(item);
            });
        }
    }

    addCollectionElement() {
        const newForm = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.indexValue++;

        const template = document.createElement('template');
        template.innerHTML = newForm.trim();
        const item = template.content.firstElementChild;

        this.collectionTarget.appendChild(item);

        if (this.allowDeleteValue) {
            this._addDeleteButton(item);
        }
    }

    _addAddButton() {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = this.buttonAddHtmlValue;
        const button = wrapper.firstElementChild;

        button.addEventListener('click', () => this.addCollectionElement());
        this.element.appendChild(button);
    }

    _addDeleteButton(item) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = this.buttonDeleteHtmlValue;
        const button = wrapper.firstElementChild;

        button.addEventListener('click', () => item.remove());

        const targetCell = item.querySelector('.action-cell');
        if (targetCell) {
            targetCell.appendChild(button);
        } else {
            item.appendChild(button);
        }
    }
}
