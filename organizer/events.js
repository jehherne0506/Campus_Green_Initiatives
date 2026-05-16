/**
 * UI & MODAL CONTROLS
 */
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function toggleSidebarVisibility() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

function showStatus(type, title, message) {
    document.getElementById('successTitle').innerText = title;
    document.getElementById('successMsg').innerText = message;
    openModal('successModal');
}

/**
 * EVENT MANAGEMENT LOGIC
 */

function openCreateModal() {
    const form = document.getElementById('eventDataForm');
    form.reset();
    document.getElementById('eventId').value = ""; 
    document.getElementById('modalTitle').innerText = "Create New Event";
    document.querySelector('.btn-submit-event').innerText = "Create Event";
    openModal('eventFormModal');
}

function openEditModal(eventData) {
    document.getElementById('eventId').value = eventData.event_id;
    document.getElementById('eventTitle').value = eventData.title;
    document.getElementById('eventDescription').value = eventData.description;
    document.getElementById('eventLocation').value = eventData.location;
    document.getElementById('eventDate').value = eventData.event_date;
    document.getElementById('eventCategory').value = eventData.category_id;

    document.getElementById('start').value = eventData.time_start;
    document.getElementById('end').value = eventData.time_end;

    document.getElementById("prevEventImage").src = eventData.event_image;
    document.getElementById("prevEventImage").style.display = "block";
    
    document.getElementById('eventMaxParticipants').value = eventData.max_participants;

    document.getElementById('modalTitle').innerText = "Edit Event Details";
    document.querySelector('.btn-submit-event').innerText = "Save Changes";
    
    
    openModal('eventFormModal');
}

// MAIN SUBMIT LOGIC
document.getElementById('eventDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('eventId', document.getElementById('eventId').value);
    formData.append('title', document.getElementById('eventTitle').value);
    formData.append('description', document.getElementById('eventDescription').value);
    formData.append('location', document.getElementById('eventLocation').value);
    formData.append('date', document.getElementById('eventDate').value);
    formData.append('start', document.getElementById('start').value);
    formData.append('end', document.getElementById('end').value);
    formData.append('max_participants', document.getElementById('eventMaxParticipants').value);
    formData.append('category', document.getElementById('eventCategory').value);
    const fileInput = document.getElementById('eventImage');
    const file = fileInput.files[0]; // get selected file
    formData.append('event_image', file);

    // Ensure the filename here matches your PHP file
    fetch('process_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            closeModal('eventFormModal');
            window.location.reload(); 
        } else {
            alert("Database Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("System Error: Ensure the file 'process_event.php' exists.");
    });
});

function confirmDelete(id) {
    document.getElementById('pendingDeleteId').value = id;
    openModal('deleteConfirmModal');
}

function executeDelete() {
    const id = document.getElementById('pendingDeleteId').value;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('eventId', id);

    fetch('process_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeModal('deleteConfirmModal');
        if(data.status === 'success') {
            // Use your existing success modal to show it's gone
            showStatus('success', 'Deleted!', 'The event has been removed from the database.');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("System Error: Could not reach the server.");
    });
}

function openImpactModal(id) {
    document.getElementById('impactEventId').value = id;
    
    // Find the specific card to grab current values
    const card = document.getElementById(`event-${id}`);
    const currentTrees = card.querySelector('.stat-number:nth-child(1)')?.innerText || "";
    const currentWaste = card.querySelector('.stat-number:nth-child(2)')?.innerText || "";
    document.getElementById('actualTrees').value = parseInt(currentTrees) || "";
    document.getElementById('actualWaste').value = parseFloat(currentWaste) || "";
    openModal('setImpactModal');
}


document.getElementById('impactDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'set_impact');
    formData.append('eventId', document.getElementById('impactEventId').value);
    formData.append('trees_planted', document.getElementById('actualTrees').value);
    formData.append('waste_collected', document.getElementById('actualWaste').value);

    fetch('process_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            closeModal('setImpactModal');
            showStatus('success', 'Stats Recorded!', 'Thank you for tracking your environmental impact.');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("System Error: Could not save impact data.");
    });
});