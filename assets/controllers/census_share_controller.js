import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['qrImage', 'linkInput', 'copyBtn'];

    static values = {
        title: String,
        copiedText: String,
        copyError: String
    };

    copyLink() {
        const input = this.linkInputTarget;
        const btn = this.copyBtnTarget;

        input.select();
        input.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(input.value).then(() => {
            const originalText = btn.innerText;

            btn.innerText = this.copiedTextValue;
            btn.classList.add("btn-success");
            btn.classList.remove("btn-primary");

            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.add("btn-primary");
                btn.classList.remove("btn-success");
            }, 2000);
        }).catch(err => {
            console.error(this.copyErrorValue, err);
        });
    }

    downloadQR() {
        const qrSrc = this.qrImageTarget.src;
        const a = document.createElement('a');

        a.href = qrSrc;

        const safeTitle = this.titleValue.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        a.download = `qr_cens_${safeTitle}.png`;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}
