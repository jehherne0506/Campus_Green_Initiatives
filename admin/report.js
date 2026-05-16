document.addEventListener('DOMContentLoaded', () => {
    const bars = document.querySelectorAll('.bar');

    bars.forEach(bar => {
        bar.addEventListener('click', (e) => {
            // Prevent the document click listener from firing
            e.stopPropagation();

            const isShowing = bar.classList.contains('show-number');

            // Reset all bars first
            bars.forEach(b => b.classList.remove('show-number'));

            // Toggle current bar if it wasn't already showing
            if (!isShowing) {
                bar.classList.add('show-number');
            }
        });
    });

    // Close tooltip when clicking anywhere else
    document.addEventListener('click', () => {
        bars.forEach(b => b.classList.remove('show-number'));
    });
});

// Placeholder for your sidebar function
function toggleSidebarVisibility() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', () => {
    refreshBarChartVisuals();
});

function refreshBarChartVisuals(){
    const bars = document.querySelectorAll('.bar');

    let maxValue = 0;

    // Find max value
    bars.forEach(bar => {

        const value = parseInt(bar.dataset.value);

        if(value > maxValue){
            maxValue = value;
        }

    });

    // Apply heights + tooltip
    bars.forEach(bar => {

        const value = parseInt(bar.dataset.value);

        const heightPercent = maxValue > 0 ? (value / maxValue) * 100 : 0;

        bar.style.height = heightPercent + "%";

        const tooltip = bar.querySelector('.tooltip');

        tooltip.innerHTML = 
        bar.nextElementSibling.innerText +
        "<br>" + value;

    });
}