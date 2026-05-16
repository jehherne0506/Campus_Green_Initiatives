document.addEventListener('DOMContentLoaded', (e)=>{
    const confirmStatusModal = document.getElementById('confirmationToggleStatusModal');
    const confirmDeleteModal = document.getElementById('confirmationDeleteModal');
    const editModal = document.getElementById('editVoucherForm');

    /* Handle toggle status, edit and delete button */
    document.querySelector('.vouchers-container').addEventListener('click', (e)=>{
        const btn = e.target.closest('button');
        if(!btn){
            return;
        }

        const card = btn.closest('.voucher-card');
        const title = card.querySelector('.title').innerText;

        if (btn.classList.contains('delete-btn')) {
            const modalTitle = document.getElementById('confirmationDeleteVoucherTitle');
            if (modalTitle){
                const voucher = btn.closest('.voucher-card');
                const voucherId = voucher.dataset.id

                modalTitle.innerText = title;

                const confirmBtn = document.querySelector('#confirmationDeleteModal .btn-confirm');
                confirmBtn.dataset.id = voucherId;
            }
            confirmDeleteModal.classList.add('active');
        }

        if (btn.classList.contains('pause-btn')) {
            const modalTitle = document.getElementById('confirmationToggleStatusVoucherTitle');
            const modalAction = document.querySelectorAll('.toggleStatus');
            let actionWord = "";
            if (modalTitle && modalAction){
                const voucher = btn.closest('.voucher-card');
                const status = voucher.dataset.status;
                const voucherId = voucher.dataset.id

                if(status === "ACTIVE"){
                    actionWord = "Disable";
                } else{
                    actionWord = "Enable"
                }

                modalTitle.innerText = title;
                modalAction.forEach(action => action.innerText = actionWord);

                const confirmBtn = document.querySelector('#confirmationToggleStatusModal .btn-confirm');
                confirmBtn.dataset.id = voucherId;
                confirmBtn.dataset.status = status;
            }
            confirmStatusModal.classList.add('active');
        }

        if (btn.classList.contains('edit-btn')) {
            const id = card.dataset.id;
            const desc = card.querySelector('.description').innerText;
            const points = card.querySelector('.points').innerText.replace(' pts', '');
            const badge = card.querySelector('.badge')?.innerText ?? "";
            const logo = card.querySelector('.card-title img').getAttribute('src');

            // Fill Form
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editPoints').value = points;
            document.getElementById('editDescription').value = desc;
            document.getElementById('editBadge').value = badge;
            document.getElementById('editLogoPreview').src = logo;

            editModal.classList.add('active');
        }
    });

    document.querySelectorAll('.close-modal, .btn-secondary').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.blur-background').forEach(m => m.classList.remove('active'));
        });
    });

    editModal.addEventListener('submit', (e) => {
        e.preventDefault();
    
        const formData = new FormData(e.target);
        formData.append("action", "edit");

        fetch("voucher_action.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === "success"){
                const successModal = document.getElementById('successModal');
                successModal.querySelector('h2').innerText = "Voucher Updated Successfully";
                successModal.querySelector('p').innerText = "Changes have been saved to the student catalog.";
                successModal.classList.add('active');
            } else{
                const errorModal = document.getElementById('errorModal');
                errorModal.querySelector('h2').innerText = "Voucher Updated Unsuccessfully";
                errorModal.querySelector('p').innerText = "Please try again later as we faced an error.";
                errorModal.classList.add('active');
            }
        })
        .catch(err => {
            console.log(err);
        })
        editModal.classList.remove('active');
    });
});

function deleteVoucher(btn) {
    const voucherId = btn.dataset.id;
    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("voucher_id", voucherId);console.log(voucherId)

    fetch("voucher_action.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('confirmationDeleteModal').classList.remove('active');

        if(data.status === "success"){
            const successModal = document.getElementById('successModal');
            successModal.querySelector('h2').innerText = "Voucher Delete Successfully";
            successModal.querySelector('p').innerText = "The voucher will not be available to students anymore.";
            successModal.classList.add('active');
        } else{
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Voucher Delete Unsuccessfully";
            errorModal.querySelector('p').innerText = "Please try again later as we faced an error.";
            errorModal.classList.add('active');
        }
    })
}

function toggleVoucherStatus(btn) {
    const voucherId = btn.dataset.id;
    const currentStatus = btn.dataset.status;

    const newStatus = currentStatus === "ACTIVE" ? "INACTIVE" : "ACTIVE";

    const formData = new FormData();
    formData.append("action", "toggle_status");
    formData.append("voucher_id", voucherId);
    formData.append("status", newStatus);

    fetch("voucher_action.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('confirmationToggleStatusModal').classList.remove('active');

        if(data.status === "success"){
            const successModal = document.getElementById('successModal');
            successModal.querySelector('h2').innerText = "Voucher Updated Successfully";
            successModal.querySelector('p').innerText = "The voucher visibility has been updated successfully.";
            successModal.classList.add('active');
        } else{
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Voucher Updated Unsuccessfully";
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

const form = document.getElementById("createVoucherForm");
form.addEventListener("submit", (e)=>{
    e.preventDefault();
    
    const formData = new FormData(form);

    fetch("create_voucher.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success"){
            const successModal = document.getElementById('successModal');
            successModal.querySelector('h2').innerText = "Voucher Created Successfully";
            successModal.querySelector('p').innerText = "The voucher will now be available to be used by students.";
            successModal.classList.add('active');
        } else{
            const errorModal = document.getElementById('errorModal');
            errorModal.querySelector('h2').innerText = "Voucher Created Unsuccessfully";
            errorModal.querySelector('p').innerText = "Please try again later as we faced an error.";
            errorModal.classList.add('active');
        }
    })
    .catch(err => {
        console.log(err);
    })
})