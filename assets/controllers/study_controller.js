import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        questions: Array,
    }
    static targets = ['kanji', 'reading', 'translation', 'progress', 'counter', 'flashcard']

    connect() {
        this.currentIndex = 0;
        this.total = this.questionsValue.length;
        this.showCurrentQuestion();
    }

    showCurrentQuestion() {
        const q = this.questionsValue[this.currentIndex];

        this.flashcardTarget.classList.remove('swap-active');

        this.kanjiTarget.textContent = q.kanji;
        this.readingTarget.textContent = q.reading;
        this.translationTarget.textContent = q.translation;

        this.counterTarget.textContent = `${this.currentIndex + 1} / ${this.total}`;
        this.progressTarget.value = this.currentIndex + 1;
        this.progressTarget.max = this.total;
    }

    next() {
        if (this.currentIndex < this.total - 1) {
            this.currentIndex++;
            this.showCurrentQuestion();
        }
    }

    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.showCurrentQuestion();
        }
    }

    flip() {
        this.flashcardTarget.classList.toggle('swap-active');
    }

    skip(event) {
        if (event) {
            event.preventDefault();
        }

        const currentQuestion = this.questions[this.currentIndex];
        if (!currentQuestion) return;

        const attemptData = {
            questionId: currentQuestion.id,
            isCorrect: false,
            isSkipped: true,
            askedKanji: currentQuestion.kanji,
            askedReading: currentQuestion.reading,
            askedTranslation: currentQuestion.translation,
            givenKanji: null,
            givenReading: null,
            givenTranslation: null
        };

        this.recordedAnswers.push(attemptData);

        this.resetQuestionInputs();

        this.currentIndex++;

        if (this.currentIndex < this.questions.length) {
            this.renderQuestion();
        } else {
            this.submitQuiz();
        }
    }
}
