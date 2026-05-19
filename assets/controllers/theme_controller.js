import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['sun', 'moon'];

    connect() {
        this.theme = localStorage.getItem('theme') ||
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'app_dark' : 'app_light');

        this.applyTheme();
    }

    toggle() {
        this.theme = this.theme === 'app_light' ? 'app_dark' : 'app_light';
        this.applyTheme();
    }

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);

        localStorage.setItem('theme', this.theme);

        if (this.theme === 'app_dark') {
            this.sunTarget.classList.remove('hidden');
            this.moonTarget.classList.add('hidden');
        } else {
            this.sunTarget.classList.add('hidden');
            this.moonTarget.classList.remove('hidden');
        }
    }
}
