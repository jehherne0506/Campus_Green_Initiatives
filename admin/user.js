function openEditUserModal(id, username, email) {
    const modal = document.getElementById("editUserModal");
    
    // Fill the input fields with existing user data
    document.getElementById("edit_user_id").value = id;
    document.getElementById("edit_username").value = username;
    document.getElementById("edit_email").value = email;
    
    // Clear the password field every time we open the modal for a new user
    document.getElementById("edit_password").value = "";

    // Show the modal
    modal.style.display = "block";
}

function closeEditUserModal() {
    document.getElementById("editUserModal").style.display = "none";
}

// Create User Modal functions
function openCreateUserModal() {
    document.getElementById("createUserModal").style.display = "block";
}

function closeCreateUserModal() {
    document.getElementById("createUserModal").style.display = "none";
}

window.addEventListener('click', function(event){
    const modals = ['deleteModal', 'createUserModal', 'editUserModal', 'banModal', 'unbanModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if(event.target === modal) modal.style.display = 'none';
    });
});


// Filtering Logic
function filterTable() {
    const input = document.getElementById("userSearch");
    const filter = input.value.toLowerCase();
    const table = document.querySelector(".user-table");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        const idCell = tr[i].getElementsByTagName("td")[0];
        const detailsCell = tr[i].getElementsByTagName("td")[1];
        
        if (idCell && detailsCell) {
            const idText = idCell.textContent || idCell.innerText;
            const detailsText = detailsCell.textContent || detailsCell.innerText;
            
            if (idText.toLowerCase().indexOf(filter) > -1 || 
                detailsText.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

// Note: These need to be globally accessible for the HTML 'onchange' attributes
window.filterRoles = function() {
    const searchQuery = document.getElementById("userSearch").value.toLowerCase();
    const roleQuery = document.getElementById("roleFilter").value.toLowerCase();
    const table = document.querySelector(".user-table");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        const detailsCell = tr[i].getElementsByTagName("td")[1];
        const roleCell = tr[i].getElementsByTagName("td")[2];
        
        if (detailsCell && roleCell) {
            const detailsText = detailsCell.textContent.toLowerCase();
            const roleText = roleCell.textContent.toLowerCase();
            const matchesSearch = detailsText.indexOf(searchQuery) > -1;
            const matchesRole = (roleQuery === "all" || roleText.includes(roleQuery));

            tr[i].style.display = (matchesSearch && matchesRole) ? "" : "none";
        }
    }
};

window.filterStatus = function() {
    const searchQuery = document.getElementById("userSearch").value.toLowerCase();
    const roleQuery = document.getElementById("roleFilter").value.toLowerCase();
    const statusQuery = document.getElementById("statusFilter").value.toLowerCase();
    const table = document.querySelector(".user-table");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        const detailsCell = tr[i].getElementsByTagName("td")[1];
        const roleCell = tr[i].getElementsByTagName("td")[2];
        const statusCell = tr[i].getElementsByTagName("td")[3];

        if (detailsCell && roleCell && statusCell) {
            const detailsText = detailsCell.textContent.toLowerCase();
            const roleText = roleCell.textContent.toLowerCase();
            const statusText = statusCell.textContent.toLowerCase();

            const matchesSearch = detailsText.indexOf(searchQuery) > -1;
            const matchesRole = (roleQuery === "all" || roleText.includes(roleQuery));
            const matchesStatus = (statusQuery === "all" || statusText.includes(statusQuery));

            tr[i].style.display = (matchesSearch && matchesRole && matchesStatus) ? "" : "none";
        }
    }
};

function openDeleteModal(userId, username, email) {
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('deleteUserInfo').innerHTML = `<strong>${username}</strong> (<small>${email}</small>)`;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Optional: close modal if clicked outside content
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if(event.target == modal){
        closeDeleteModal();
    }
}

function openBanModal(userId, username){
    document.getElementById('ban_user_id').value = userId;
    document.getElementById('banUserInfo').innerHTML = `<strong>${username}</strong>`;
    document.getElementById('banModal').style.display = 'flex';
}

function closeBanModal(){
    document.getElementById('banModal').style.display = 'none';
}

function openUnbanModal(userId, username){
    document.getElementById('unban_user_id').value = userId;
    document.getElementById('unbanUserInfo').innerHTML = `<strong>${username}</strong>`;
    document.getElementById('unbanModal').style.display = 'flex';
}

function closeUnbanModal(){
    document.getElementById('unbanModal').style.display = 'none';
}