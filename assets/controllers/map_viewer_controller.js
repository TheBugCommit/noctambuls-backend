import { Controller } from '@hotwired/stimulus'
import L from 'leaflet'

export default class extends Controller {
    static values = { lat: Number, lng: Number, label: String, iconUrl: String }

    connect() {
        const map = L.map(this.element).setView([this.latValue, this.lngValue], 14)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
        }).addTo(map)
        const icon = L.icon({
            iconUrl: this.iconUrlValue,
            iconSize: [25, 41],
            iconAnchor: [12, 41],
        })
        L.marker([this.latValue, this.lngValue], { icon })
            .bindPopup(this.labelValue)
            .addTo(map)
            .openPopup()
    }
}
