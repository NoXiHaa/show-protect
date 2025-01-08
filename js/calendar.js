class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.initializeCalendar();
        this.bindEvents();
    }

    initializeCalendar() {
        this.updateCalendarDisplay();
    }

    updateCalendarDisplay() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        document.getElementById('currentMonth').innerHTML = 
            `Tháng ${month + 1} năm ${year}`;
        
        this.renderSolarCalendar(year, month);
        this.renderLunarCalendar(year, month);
        this.updateSpecialDays(year, month);
    }

    renderSolarCalendar(year, month) {
        const solarDays = document.getElementById('solarDays');
        solarDays.innerHTML = '';

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startingDay = firstDay.getDay();
        
        // Thêm ngày trống đầu tháng
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'day empty';
            solarDays.appendChild(emptyDay);
        }

        // Thêm các ngày trong tháng
        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'day';
            dayElement.textContent = day;
            solarDays.appendChild(dayElement);

            const today = new Date();
            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                dayElement.classList.add('today');
            }
        }
    }

    renderLunarCalendar(year, month) {
        // Tạm thời hiển thị giống lịch dương
        // Sẽ cập nhật sau khi thêm thư viện chuyển đổi âm lịch
        const lunarDays = document.getElementById('lunarDays');
        lunarDays.innerHTML = '';

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startingDay = firstDay.getDay();

        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'day empty';
            lunarDays.appendChild(emptyDay);
        }

        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'day';
            // Tạm thời hiển thị ngày dương
            dayElement.textContent = day;
            lunarDays.appendChild(dayElement);
        }
    }

    updateSpecialDays(year, month) {
        const specialDaysList = document.getElementById('specialDaysList');
        specialDaysList.innerHTML = '';
        
        // Thêm các ngày đặc biệt (sẽ cập nhật sau)
        const specialDays = [
            { date: '1/1', name: 'Tết Dương lịch' },
            { date: '30/4', name: 'Giải phóng miền Nam' },
            { date: '1/5', name: 'Quốc tế Lao động' }
        ];

        specialDays.forEach(day => {
            const [d, m] = day.date.split('/');
            if (parseInt(m) - 1 === month) {
                const div = document.createElement('div');
                div.textContent = `${day.date}: ${day.name}`;
                specialDaysList.appendChild(div);
            }
        });
    }

    bindEvents() {
        document.getElementById('prevMonth').addEventListener('click', () => {
            const newDate = new Date(this.currentDate);
            newDate.setMonth(this.currentDate.getMonth() - 1);
            this.currentDate = newDate;
            this.updateCalendarDisplay();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            const newDate = new Date(this.currentDate);
            newDate.setMonth(this.currentDate.getMonth() + 1);
            this.currentDate = newDate;
            this.updateCalendarDisplay();
        });

        // Xử lý dark mode
        document.getElementById('darkmode-toggle').addEventListener('change', (e) => {
            document.body.setAttribute('data-theme', e.target.checked ? 'dark' : 'light');
        });
    }
}

// Khởi tạo calendar khi trang web load xong
document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
}); 