import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        timeout: Number
    }

    connect() {
        this.enforceStackLimit();

        requestAnimationFrame(() => {
            this.element.classList.remove('translate-x-full', 'opacity-0');
        });

        if (this.timeoutValue) {
            this.timer = setTimeout(() => {
                this.close();
            }, this.timeoutValue);
        }
    }

    close() {
        this.element.classList.add('translate-x-full', 'opacity-0');

        setTimeout(() => {
            this.element.remove();
        }, 500);
    }

    enforceStackLimit() {
        const container = this.element.parentElement;
        const flashes = Array.from(container.querySelectorAll('[data-controller="flash"]'));

        if (flashes.length > 3) {
            const overLimit = flashes.length - 3;
            for (let i = 0; i < overLimit; i++) {
                const oldestFlash = this.application.getControllerForElementAndIdentifier(flashes[i], 'flash');
                if (oldestFlash) {
                    oldestFlash.close();
                } else {
                    flashes[i].remove();
                }
            }
        }
    }
}
