import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['tab', 'panel']

    switch(event) {
        const name = event.params.name
        this.tabTargets.forEach(t =>
            t.classList.toggle('tab-active', t.dataset.chartTabsNameParam === name)
        )
        this.panelTargets.forEach(p => {
            const isActive = p.dataset.chartTabsPanelName === name
            p.classList.toggle('hidden', !isActive)
            if (isActive) {
                p.querySelectorAll('[data-controller]').forEach(el => {
                    el.dispatchEvent(new CustomEvent('chart-tabs:activated'))
                })
            }
        })
    }
}
