/**
 * Component: Dropdown (Select)
 * 
 * Handles toggling, selection, and click-outside behavior.
 */

document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(function(dropdown) {
        const wrapper = dropdown.querySelector('.form-select-wrapper');
        const menu = dropdown.querySelector('.form-select-menu');
        const items = dropdown.querySelectorAll('.form-select-item');
        const valueDisplay = dropdown.querySelector('.form-control');

        if (!wrapper || !menu) return;

        // Toggle menu
        wrapper.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other dropdowns first
            dropdowns.forEach(d => {
                if (d !== dropdown) d.classList.remove('dropdown--open');
            });

            dropdown.classList.toggle('dropdown--open');
        });

        // Item selection
        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Update value
                if (valueDisplay) {
                    valueDisplay.textContent = this.textContent.replace('(Selected)', '').trim();
                }

                // Update active state in menu
                items.forEach(i => i.classList.remove('form-select-item--selected'));
                this.classList.add('form-select-item--selected');

                // Close menu
                dropdown.classList.remove('dropdown--open');
            });
        });
    });

    // Close all dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(d => d.classList.remove('dropdown--open'));
    });
});




