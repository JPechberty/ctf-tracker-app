import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        endTime: Number,
        startTime: Number
    }

    connect() {
        this.update()
        this.interval = setInterval(() => this.update(), 1000)
    }

    disconnect() {
        clearInterval(this.interval)
    }

    update() {
        const now = Date.now()
        const end = this.endTimeValue * 1000
        const start = this.startTimeValue * 1000

        if (now < start) {
            // Challenge not started
            this.element.textContent = this.formatTime(start - now)
            this.element.dataset.state = 'upcoming'
        } else if (now >= end) {
            // Challenge ended
            this.element.textContent = 'TERMINE'
            this.element.classList.add('bg-danger', 'text-white')
            this.element.classList.remove('bg-dark')
            clearInterval(this.interval)
        } else {
            // Challenge active
            this.element.textContent = this.formatTime(end - now)
            this.element.dataset.state = 'active'
        }
    }

    formatTime(ms) {
        const h = Math.floor(ms / 3600000)
        const m = Math.floor((ms % 3600000) / 60000)
        const s = Math.floor((ms % 60000) / 1000)
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    }
}
