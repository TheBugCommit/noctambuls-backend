import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';

export default class extends Controller {
    static values = {
        mode: String
    }

    connect() {
        const config = {
            //locale: Catalan,
        };

        if (this.modeValue === 'time') {
            config.enableTime = true;
            config.noCalendar = true;
            config.dateFormat = "H:i";
            config.time_24hr = true;
        } else {
            config.enableTime = false;
            config.dateFormat = "Y-m-d";
        }

        flatpickr(this.element, config);
    }
}
