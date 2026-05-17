import {Controller} from '@hotwired/stimulus'
import {Chart, BarController, BarElement, CategoryScale, LinearScale, Legend, Tooltip} from 'chart.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Legend, Tooltip)

export default class extends Controller {
    static values = {
        chartData: Object,
        monthLabels: Array,
    }

    connect() {
        const {series} = this.chartDataValue
        const labels = this.monthLabelsValue

        const datasets = series.map(s => ({
            label: s.name,
            data: s.data,
            backgroundColor: s.color,
        }))

        new Chart(this.element, {
            type: 'bar',
            data: {labels, datasets},
            options: {
                responsive: true,
                plugins: {
                    legend: {position: 'bottom'},
                    tooltip: {mode: 'index'},
                },
                scales: {
                    x: {stacked: true},
                    y: {stacked: true, beginAtZero: true, ticks: {precision: 0}},
                },
            },
        })
    }
}
