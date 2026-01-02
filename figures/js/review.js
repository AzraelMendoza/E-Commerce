 document.addEventListener("DOMContentLoaded", function() {
    // Select all elements with the 'expandable-wrapper' class
    const expandableSections = document.querySelectorAll('.expandable-wrapper');

    expandableSections.forEach(wrapper => {
        // Find the button specifically associated with THIS wrapper
        const btn = wrapper.parentElement.querySelector('.toggle-expand-btn');
        
        if (!btn) return; // Skip if no button is found

        // Set the height threshold (700px)
        const threshold = 700;

        // Check if content is short enough to hide the button
        if (wrapper.scrollHeight <= threshold) {
            btn.style.display = 'none';
        }

        btn.addEventListener('click', function() {
            wrapper.classList.toggle('expanded');
            
            // Toggle text based on state
            if (wrapper.classList.contains('expanded')) {
                btn.textContent = 'Show Less';
            } else {
                btn.textContent = 'Show All Reviews';
                // Smooth scroll back to the top of the section
                wrapper.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});