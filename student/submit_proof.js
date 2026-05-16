document .getElementById('photo').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const uploadBox = document.querySelector('.upload-box');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            uploadBox.style.padding = "10px";
            uploadBox.querySelector('.icon-circle').style.display = 'none';
            uploadBox.querySelector('strong').textContent = "Change Photo";
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = "";
        preview.style.display = 'none';
    }
})

const form = document.querySelector("#proofForm");
form.addEventListener("submit", (e)=>{
    e.preventDefault();

    const formData = new FormData(form);

    fetch("save_proof.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success"){
            const successSubmitProofModal = document.getElementById('successSubmitProofModal');
            successSubmitProofModal.querySelector('h2').innerText = "Proof Submitted Successfully";
            successSubmitProofModal.querySelector('p').innerText = "Your points will be automatically added to your account once your attendance is approved.";
            successSubmitProofModal.classList.add('active');
        } else{
            const errorSubmitProofModal = document.getElementById('errorSubmitProofModal');
            errorSubmitProofModal.querySelector('h2').innerText = "Proof Submitted Insuccessfully";
            errorSubmitProofModal.querySelector('p').innerText = "Please try again later as we faced an error.";
            errorSubmitProofModal.classList.add('active');
        }
    })
    .catch(err => {
        console.log(err);
    })
})

function closeSuccessModal(){
    const successSubmitProofModal = document.querySelector("#successSubmitProofModal");
    successSubmitProofModal.classList.remove("active");
    window.location.href = "my_events.php";
}

function closeErrorModal(){
    const errorSubmitProofModal = document.querySelector("#errorSubmitProofModal");
    errorSubmitProofModal.classList.remove("active");
    window.location.href = "my_events.php";
}