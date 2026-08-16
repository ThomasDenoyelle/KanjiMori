import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['canvas', 'results', 'dialog', 'preview'];
    currentInputId = null;
    isInitialized = false;

    connect() {
        this.tryInitCanvas();
        this.setupTouchEvents();
    }

    setupTouchEvents() {
        const canvas = this.canvasTarget;
        let lastX = null;
        let lastY = null;
        const MOVE_THRESHOLD = 3;

        const dispatchMouseEvent = (eventName, touchEvent) => {
            const touch = touchEvent.touches[0] || touchEvent.changedTouches[0];
            const rect = canvas.getBoundingClientRect();

            const mouseEvent = new MouseEvent(eventName, {
                clientX: touch.clientX,
                clientY: touch.clientY,
                screenX: touch.screenX,
                screenY: touch.screenY,
                button: 0,
                buttons: eventName === 'mouseup' ? 0 : 1,
                bubbles: true,
                cancelable: true,
                view: window
            });

            Object.defineProperty(mouseEvent, 'offsetX', { get: () => touch.clientX - rect.left });
            Object.defineProperty(mouseEvent, 'offsetY', { get: () => touch.clientY - rect.top });

            canvas.dispatchEvent(mouseEvent);
        };

        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            lastX = touch.clientX;
            lastY = touch.clientY;
            dispatchMouseEvent('mousedown', e);
        }, { passive: false });

        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            const dx = touch.clientX - lastX;
            const dy = touch.clientY - lastY;

            if (Math.hypot(dx, dy) < MOVE_THRESHOLD) return;

            lastX = touch.clientX;
            lastY = touch.clientY;
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

    tryInitCanvas() {
        if (this.isInitialized) return;

        if (typeof window.KanjiCanvas !== 'undefined') {
            window.KanjiCanvas.init(this.canvasTarget.id);
            this.isInitialized = true;
            console.log("KanjiCanvas initialisé avec succès !");
        } else {
            setTimeout(() => {
                this.tryInitCanvas();
            }, 200);
        }
    }

    open(event) {
        this.currentInputId = event.params.inputId;
        this.resultsTarget.innerHTML = '';

        const input = document.getElementById(this.currentInputId);
        if (input) {
            this.previewTarget.textContent = input.value;
        }

        this.dialogTarget.showModal();

        if (!this.isInitialized) {
            this.tryInitCanvas();
        }

        if (this.isInitialized) {
            window.KanjiCanvas.erase(this.canvasTarget.id);
        } else {
            console.error("KanjiCanvas n'a pas pu s'initialiser à temps.");
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
