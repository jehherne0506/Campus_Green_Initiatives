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
    const selectedLocation = document.getElementById("locationFilter").value;
    const selectedFilter = document.getElementById("statusFilter")?.value || null;

    const events_card = document.querySelectorAll(".detailed-event-card");

    events_card.forEach(card => {
        const card_category = card.getAttribute('data-category');
        const card_date = card.getAttribute('data-date');
        const card_location = card.getAttribute('data-location');
        const card_status = card.getAttribute('data-status');

        const match_category = selectedCategory === "All" || selectedCategory === card_category;
        const match_date = selectedDate === "" || selectedDate === card_date;
        const match_location = selectedLocation === "All" || selectedLocation === card_location;
        const match_status = (selectedFilter === null || selectedFilter === "All") ? true : card_status === selectedFilter;
        
        if(match_category && match_date && match_location && match_status){
            card.style.display = "block";
        } else{
            card.style.display = "none";
        }
    });
}

function resetFilter(){
    const selectedCategory = document.getElementById("categoryFilter");
    const selectedDate = document.getElementById("dateFilter");
    const selectedLocation = document.getElementById("locationFilter");
    const statusFilter = document.getElementById("statusFilter") || null;

    selectedCategory.value = "All";
    selectedDate.value = "";
    selectedLocation.value = "All";
    if(statusFilter){
        statusFilter.value = "All";
    }

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

let registerEventId = '';
let registerEventName = '';

function openConfirmationModal(button){
    const eventCard = button.closest('.detailed-event-card');
    const eventName = eventCard.querySelector('h4').textContent;
    const eventId = eventCard.getAttribute('data-event_id');
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "block";
    registerEventId = eventId;
    registerEventName = eventName;
    document.getElementById("confirmationEventName").innerHTML = registerEventName;
}

function closeConfirmationModal(){
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "none";
}

function participateEvent(){
    if(registerEventId){
        fetch("join_event.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ event_id: registerEventId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === "success"){
                openSuccessModal();
            } else{
                openErrorModal();
            }
        })
    } else{
        openErrorModal();
    }
}

function openSuccessModal(){
    const successModal = document.getElementById("successRegistrationModal");
    successModal.style.display = "block";
    document.getElementById("successEventName").innerHTML = registerEventName;
}

function closeSuccessModal(){
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "none";
    const successModal = document.getElementById("successRegistrationModal");
    successModal.style.display = "none";
    window.location.reload();
}

function openErrorModal(){
    const errorModal = document.getElementById("errorRegistrationModal");
    errorModal.style.display = "block";
    document.getElementById("errorEventName").innerHTML = registerEventName;
}

function closeErrorModal(){
    const confirmationModal = document.getElementById("confirmationRegistrationModal");
    confirmationModal.style.display = "none";
    const errorModal = document.getElementById("errorRegistrationModal");
    errorModal.style.display = "none";
}