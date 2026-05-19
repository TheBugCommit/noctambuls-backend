import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = { message: String }

    confirm(event) {
        if (this.confirmed) {
            return;
        }

        event.preventDefault();

        const modal = document.getElementById('custom_confirm_modal');
        const messageEl = document.getElementById('custom_confirm_modal_message');
        const confirmBtn = document.getElementById('custom_confirm_modal_confirm');
        const cancelBtn = document.getElementById('custom_confirm_modal_cancel');

        if (!modal) {
            if (window.confirm(this.messageValue)) {
                this.executeAction();
            }
            return;
        }

        messageEl.innerText = this.messageValue;

        const onConfirm = () => {
            this.confirmed = true;
            modal.close();
            this.executeAction();
        };

        const onCancel = () => {
            modal.close();
        };

        const cleanup = () => {
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
        };

        modal.addEventListener('close', cleanup, { once: true });
        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);

        modal.showModal();
    }

    executeAction() {
        if (this.element.tagName === 'FORM') {
            this.element.requestSubmit();
        } else {
            this.element.click();
        }
    }
}
