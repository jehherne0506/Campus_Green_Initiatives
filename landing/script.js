const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) { // Add active class if the element is on the screen
            entry.target.classList.add('active');
        }
    });
}, { threshold: 0.2 }); // Triggers when 20% of the card is visible

// .observe trigger to add "active" class if it trigger
document.querySelectorAll('.stats_card').forEach((card) => {
    observer.observe(card);
});

document.querySelectorAll('.events_card').forEach((card) => {
    observer.observe(card);
});

document.querySelectorAll('.steps_card').forEach((card) => {
    observer.observe(card);
});

document.querySelectorAll('.join_us').forEach((card) => {
    observer.observe(card);
});