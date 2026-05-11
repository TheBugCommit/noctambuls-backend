import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // 1. Interceptem l'enviament del formulari
        this.element.addEventListener('submit', (event) => {
            // checkValidity() retorna false si falta algun 'required' o algun tipus és incorrecte
            if (!this.element.checkValidity()) {
                event.preventDefault(); // Aturem l'enviament al servidor
                event.stopPropagation();

                // Busquem TOTS els camps invàlids del formulari
                const invalidFields = this.element.querySelectorAll(':invalid');

                invalidFields.forEach(field => {
                    // Apliquem la classe vermella de DaisyUI segons el tipus d'element
                    if (field.tagName === 'TEXTAREA') {
                        field.classList.add('textarea-error');
                    } else {
                        field.classList.add('input-error');
                    }
                });

                // (Opcional) Posem el focus al primer camp que falla perquè l'usuari ho vegi ràpid
                if (invalidFields.length > 0) {
                    invalidFields[0].focus();
                }
            }
        });

        // 2. Traiem el color vermell tan bon punt l'usuari ho arregli (escrigui)
        this.element.addEventListener('input', (event) => {
            if (event.target.validity.valid) {
                event.target.classList.remove('input-error', 'textarea-error');
            }
        });
    }
}
