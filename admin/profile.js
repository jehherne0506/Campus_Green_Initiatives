const avatarInput = document.querySelector("#avatar_input");
avatarInput.addEventListener("change", (e)=>{
    const file = e.target.files[0];
    if(file){
        const avatarPreview = document.querySelector(".profile-preview");
        
        // Create temporary url for image
        const reader = new FileReader();
        reader.onload = function(event) {
            if (avatarPreview) {
                avatarPreview.src = event.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
})

const removeAvatar = document.querySelector("#remove_avatar");
removeAvatar.addEventListener("click", (e)=>{
    e.preventDefault();
    const avatarPreview = document.querySelector(".profile-preview");
    avatarPreview.src = "../assets/uploads/default_user_icon.webp";
    document.querySelector("#remove_avatar_flag").value = "1";
})

const basicForm = document.querySelector("#basic_form");
basicForm.addEventListener("submit", (e)=>{
    e.preventDefault();

    const formData = new FormData(basicForm);

    fetch("../update_basic_profile.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success"){
            const successModal = document.getElementById('successModal');
            successModal.querySelector('h2').innerText = "Profile Update Successfully";
            successModal.querySelector('p').innerText = "The profile will is fully updated.";
            successModal.classList.add('active');
        } else if(data.status === "duplicate"){
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Email is used by a different account";
            errorModal.querySelector('p').innerText = "Please try again with a different email.";
            errorModal.classList.add('active');
        } else{
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Profile Update Unsuccessfully";
            errorModal.querySelector('p').innerText = "Please try again later as we faced an error.";
            errorModal.classList.add('active');
        }
    })
})

const securityForm = document.querySelector("#security_form");
securityForm.addEventListener("submit", (e)=>{
    e.preventDefault();

    const current_password = document.querySelector("#current_password").value;
    const new_password = document.querySelector("#new_password").value;
    const confirm_password = document.querySelector("#confirm_password").value;

    if(new_password !== confirm_password){
        const errorModal = document.getElementById('errorModal');
        errorModal.querySelector('h2').innerText = "Profile Update Unsuccessfully";
        errorModal.querySelector('p').innerText = "Please ensure the new password and confirm password are identical.";
        errorModal.classList.add('active');
        return;
    }

    fetch("../update_security_profile.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            current_password: current_password,
            new_password: new_password
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success"){
            const successModal = document.getElementById('successModal');
            successModal.querySelector('h2').innerText = "Profile Update Successfully";
            successModal.querySelector('p').innerText = "The profile will is fully updated.";
            successModal.classList.add('active');
        } else if(data.status === "validate"){
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Your Password is Incorrect";
            errorModal.querySelector('p').innerText = "Please try again with the correct password.";
            errorModal.classList.add('active');
        } else{
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Profile Update Unsuccessfully";
            errorModal.querySelector('p').innerText = "Please try again later as we faced an error.";
            errorModal.classList.add('active');
        }
    })
})

const deleteButton = document.querySelector("#dlt-account");
deleteButton.addEventListener("click", ()=>{
    const confirmationModal = document.querySelector("#confirmationDeleteModal");
    confirmationModal.classList.add("active");
})

function deleteAccount(){
    fetch("../delete_profile.php", {
        method: "POST",
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success"){
            const successModal = document.getElementById('successModal');
            successModal.querySelector('h2').innerText = "Profile Delete Successfully";
            successModal.querySelector('p').innerText = "Your account will be unavailable from now on.";
            successModal.classList.add('active');
        } else{
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Profile Delete Unsuccessfully";
            errorModal.querySelector('p').innerText = "Please try again later as we faced an error.";
            errorModal.classList.add('active');
        }
    })
}

function closeCompleteModal(btn){
    const modal = btn.closest(".blur-background");
    modal.classList.remove("active");
    window.location.reload();
}