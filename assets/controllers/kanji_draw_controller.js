import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['canvas', 'results', 'dialog', 'preview'];
    currentInputId = null;
    isInitialized = false;

    connect() {
        this.setupTouchEvents();
    }

    setupTouchEvents() {
        const canvas = this.canvasTarget;

        const dispatchMouseEvent = (eventName, touchEvent) => {
            const touch = touchEvent.touches[0] || touchEvent.changedTouches[0];
            const rect = canvas.getBoundingClientRect();

            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;

            const mouseEvent = new PointerEvent(eventName, {
                clientX: touch.clientX,
                clientY: touch.clientY,
                bubbles: true,
                cancelable: true,
                view: window,
                pointerType: 'touch',
                isPrimary: true
            });

            mouseEvent.layerX = (touch.clientX - rect.left) * scaleX;
            mouseEvent.layerY = (touch.clientY - rect.top) * scaleY;

            Object.defineProperty(mouseEvent, 'offsetX', { get: () => (touch.clientX - rect.left) * scaleX });
            Object.defineProperty(mouseEvent, 'offsetY', { get: () => (touch.clientY - rect.top) * scaleY });

            canvas.dispatchEvent(mouseEvent);
        };

        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            dispatchMouseEvent('mousedown', e);
        }, { passive: false });

        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            dispatchMouseEvent('mousemove', e);
        }, { passive: false });

        canvas.addEventListener('touchend', (e) => {
            e.preventDefault();
            dispatchMouseEvent('mouseup', e);
        }, { passive: false });

        canvas.addEventListener('touchcancel', (e) => {
            e.preventDefault();
            dispatchMouseEvent('mouseup', e);
        }, { passive: false });
    }

    open(event) {
        this.currentInputId = event.params.inputId;
        this.resultsTarget.innerHTML = '';

        const input = document.getElementById(this.currentInputId);
        if (input) {
            this.previewTarget.textContent = input.value;
        }

        this.dialogTarget.showModal();

        setTimeout(() => {
            if (typeof window.KanjiCanvas !== 'undefined') {
                window.KanjiCanvas.init(this.canvasTarget.id);
                this.isInitialized = true;
                window.KanjiCanvas.erase(this.canvasTarget.id);
            } else {
                console.error("La librairie KanjiCanvas n'est pas chargée.");
            }
        }, 50);
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
