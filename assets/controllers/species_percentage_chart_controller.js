import { Controller } from '@hotwired/stimulus'
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from 'chart.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip)

export default class extends Controller {
    static values = { labels: Array, data: Array, colors: Array }

    #chart = null

    connect() {
        this.element.addEventListener('chart-tabs:activated', () => this.#init())
    }

    disconnect() {
        this.#chart?.destroy()
    }

    #init() {
        if (this.#chart) return
        this.#chart = new Chart(this.element, {
            type: 'bar',
            data: {
                labels: this.labelsValue,
                datasets: [{
                    data:            this.dataValue,
                    backgroundColor: this.colorsValue,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => `${ctx.parsed.y}%` } },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: v => `${v}%` },
                    },
                },
            },
        })
    }
}
