import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['canvas', 'results', 'dialog', 'preview'];
    currentInputId = null;
    isInitialized = false;

    connect() {
    }

    open(event) {
        this.currentInputId = event.params.inputId;
        this.resultsTarget.innerHTML = '';

        const input = document.getElementById(this.currentInputId);
        if (input) {
            this.previewTarget.textContent = input.value;
        }

        this.dialogTarget.showModal();

        if (typeof window.KanjiCanvas !== 'undefined') {
            if (!this.isInitialized) {
                window.KanjiCanvas.init(this.canvasTarget.id);
                this.isInitialized = true;
            }
            window.KanjiCanvas.erase(this.canvasTarget.id);
        } else {
            console.error("La librairie KanjiCanvas n'est pas chargée.");
        }
    }

    erase() {
        if (this.isInitialized) {
            window.KanjiCanvas.erase(this.canvasTarget.id);
            this.resultsTarget.innerHTML = '';
        }
    }

    deleteLast() {
        if (this.isInitialized) {
            window.KanjiCanvas.deleteLast(this.canvasTarget.id);
        }
    }

    recognize() {
        if (!this.isInitialized) return;

        this.resultsTarget.innerHTML = '<span class="loading loading-spinner text-primary"></span>';

        try {
            const candidates = window.KanjiCanvas.recognize(this.canvasTarget.id);
            this.resultsTarget.innerHTML = '';

            if (!candidates || candidates.length === 0) {
                this.resultsTarget.innerHTML = '<span class="text-sm text-base-content/50 mt-2 font-medium">Aucun kanji reconnu...</span>';
                return;
            }

            const bestMatch = candidates[0];
            this.select(bestMatch);

        } catch (error) {
            console.warn("Erreur interceptée :", error);
            this.resultsTarget.innerHTML = '<span class="text-sm text-warning mt-2 font-medium">Dessine quelque chose avant d\'analyser !</span>';
        }
    }

    select(char) {
        const input = document.getElementById(this.currentInputId);
        if (input) {
            input.value += char;
            input.dispatchEvent(new Event('input', { bubbles: true }));

            this.previewTarget.textContent = input.value;
        }

        this.erase();
    }

    backspace() {
        const input = document.getElementById(this.currentInputId);

        if (input && input.value.length > 0) {
            input.value = input.value.slice(0, -1);

            input.dispatchEvent(new Event('input', { bubbles: true }));

            this.previewTarget.textContent = input.value;
        }
    }
}
