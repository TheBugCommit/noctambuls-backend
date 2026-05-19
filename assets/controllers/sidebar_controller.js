import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'overlay'];

    toggle() {
        this.menuTarget.classList.toggle('-translate-x-full');

        if (this.hasOverlayTarget) {
            this.overlayTarget.classList.toggle('hidden');
        }
    }
}
