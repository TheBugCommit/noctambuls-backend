import { Controller } from '@hotwired/stimulus';
import L from 'leaflet';

export default class extends Controller {
    static targets = ['map', 'lat', 'lng', 'accuracy', 'locationSource'];

    static values = {
        iconUrl: String,
        iconRetina: String,
    };

    connect() {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconUrl: this.iconUrlValue,
            iconRetinaUrl: this.iconRetinaValue,
            shadowUrl: '',
        });

        const latInput = this.latTarget;
        const lngInput = this.lngTarget;

        const initialLat = latInput.value ? parseFloat(latInput.value) : 41.8204;
        const initialLng = lngInput.value ? parseFloat(lngInput.value) : 1.5491;
        const zoomLevel = latInput.value ? 15 : 8;

        const map = L.map(this.mapTarget).setView([initialLat, initialLng], zoomLevel);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        let marker = null;

        if (latInput.value && lngInput.value) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }

        map.on('click', (e) => {
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);
            this.accuracyTarget.value = '0';
            this.locationSourceTarget.value = '1';

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    }
}
