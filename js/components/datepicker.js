/**
 * Component: Datepicker
 * 
 * Handles toggling, simple calendar navigation, and date selection.
 */

document.addEventListener('DOMContentLoaded', function() {
    const datepickers = document.querySelectorAll('.datepicker');

    datepickers.forEach(function(datepicker) {
        const wrapper = datepicker.querySelector('.form-control-wrapper');
        const calendar = datepicker.querySelector('.datepicker-calendar');
        const valueDisplay = datepicker.querySelector('.form-control');
        
        if (!wrapper || !calendar) return;

        // Toggle calendar
        wrapper.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other datepickers or dropdowns
            document.querySelectorAll('.datepicker--open, .dropdown--open').forEach(el => {
                if (el !== datepicker) el.classList.remove('datepicker--open', 'dropdown--open');
            });

            datepicker.classList.toggle('datepicker--open');
        });

        // Basic Selection logic (Showcase only)
        const days = calendar.querySelectorAll('.calendar-day:not(.calendar-day--outside)');
        days.forEach(function(day) {
            day.addEventListener('click', function(e) {
                e.stopPropagation();
                
                const selectedDate = this.textContent.padStart(2, '0') + '/02/2026';
                if (valueDisplay) {
                    if (valueDisplay.tagName === 'INPUT') {
                        valueDisplay.value = selectedDate;
                    } else {
                        valueDisplay.textContent = selectedDate;
                    }
                }

                days.forEach(d => d.classList.remove('calendar-day--selected'));
                this.classList.add('calendar-day--selected');

                datepicker.classList.remove('datepicker--open');
            });
        });
    });

    // Close on click outside
    document.addEventListener('click', function() {
        datepickers.forEach(d => d.classList.remove('datepicker--open'));
    });
});




