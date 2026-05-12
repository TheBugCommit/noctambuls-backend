import { Controller } from '@hotwired/stimulus'
import L from 'leaflet'

export default class extends Controller {
    static values = { markers: Array, iconUrl: String }

    connect() {
        const map = L.map(this.element)

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
        }).addTo(map)

        const icon = L.icon({
            iconUrl: this.iconUrlValue,
            iconSize: [25, 41],
            iconAnchor: [12, 41],
        })

        const bounds = []

        for (const { lat, lng, label } of this.markersValue) {
            L.marker([lat, lng], { icon }).bindPopup(label).addTo(map)
            bounds.push([lat, lng])
        }

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30] })
        } else {
            map.setView([41.8204, 1.5491], 8)
        }
    }
}
