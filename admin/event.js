// Function to toggle filter visibility on mobile
function toggleMobileFilters() {
    const filterGroup = document.getElementById('filterGroup');
    const arrow = document.getElementById('filter-arrow');
    
    // Toggle the class that shows/hides the menu
    filterGroup.classList.toggle('show-on-mobile');
    
    // Flip the arrow upside down when open
    if (filterGroup.classList.contains('show-on-mobile')) {
        arrow.style.transform = 'rotate(180deg)';
    } else {
        arrow.style.transform = 'rotate(0deg)';
    }
}

function filterEvents(){
    const selectedCategory = document.getElementById("categoryFilter").value;
    const selectedDate = document.getElementById("dateFilter").value;

    const events_card = document.querySelectorAll(".detailed-event-card");

    events_card.forEach(card => {
        const card_category = card.getAttribute('data-category');
        const card_date = card.getAttribute('data-date');

        const match_category = selectedCategory === "All" || selectedCategory === card_category;
        const match_date = selectedDate === "" || selectedDate === card_date;

        if(match_category && match_date){
            card.style.display = "";
        } else{
            card.style.display = "none";
        }
    });
}

function resetFilter(){
    const selectedCategory = document.getElementById("categoryFilter");
    const selectedDate = document.getElementById("dateFilter");

    selectedCategory.value = "All";
    selectedDate.value = "";

    filterEvents();
}

function fetchFilterOptions(){
    const events_card = document.querySelectorAll(".detailed-event-card");
    let categories_set = new Set();
    let location_set = new Set();

    events_card.forEach(card => {
        const card_category = card.getAttribute('data-category');
        const card_location = card.getAttribute('data-location');

        categories_set.add(card_category);
        location_set.add(card_location);
    });

    const category_filter = document.getElementById("categoryFilter");
    const location_filter = document.getElementById("locationFilter");

    categories_set.forEach(category => {
        const new_option = document.createElement("option");
        new_option.value = category;
        new_option.textContent = category; // The text the user actually sees
        category_filter.appendChild(new_option);
    });

    location_set.forEach(location => {
        const new_option = document.createElement("option");
        new_option.value = location;
        new_option.textContent = location;
        location_filter.appendChild(new_option);
    })
}

document.addEventListener("DOMContentLoaded", fetchFilterOptions);

let registerEventName = '';

function openConfirmationModal(button){
    const eventCard = button.parentElement;
    const eventName = eventCard.querySelector('h4').textContent;
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "block";
    registerEventName = eventName;
    document.getElementById("confirmationEventName").innerHTML = registerEventName;
}

function closeConfirmationModal(){
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "none";
}

function processRegistration(){
    const successModal = document.getElementById("successRegistrationModal");
    successModal.style.display = "block";
    document.getElementById("successEventName").innerHTML = registerEventName;
}

function closeSuccessModal(){
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "none";
    const successModal = document.getElementById("successRegistrationModal");
    successModal.style.display = "none";
}

function updateEventStatus(eventId, newStatus) {
    if (!confirm(`Are you sure you want to ${newStatus} this event?`)) return;

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('status', newStatus);

    fetch('update_event_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`.detailed-event-card[data-id="${eventId}"]`);
            if(card) {
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        alert("System Error: Could not reach the server.");
    });
}
