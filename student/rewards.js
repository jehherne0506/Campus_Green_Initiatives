let voucher_id = "";
let voucher_title = "";
let voucher_points = 0;

const allRedeemButton = document.querySelectorAll(".redeem-btn");

allRedeemButton.forEach(btn => {
    btn.addEventListener("click", ()=>{
        const reward = btn.closest(".reward-card");
        voucher_id = reward.dataset.id;
        voucher_title = reward.dataset.title;
        voucher_points = reward.dataset.points;

        document.querySelector("#confirmationVoucherTitle").innerHTML = voucher_title;
        document.querySelector("#confirmationVoucherPoints").innerHTML = voucher_points;
        const confirmationRedeemModal = document.querySelector("#confirmationRedeemModal");
        confirmationRedeemModal.classList.add("active");
    })
})

function closeConfirmationModal(){
    const confirmationRedeemModal = document.querySelector("#confirmationRedeemModal");
    confirmationRedeemModal.classList.remove("active");
}

function closeSuccessModal(){
    const successRedeemModal = document.querySelector("#successRedeemModal");
    successRedeemModal.classList.remove("active");
    window.location.reload();
}

function closeErrorModal(){
    const errorRedeemModal = document.querySelector("#errorRedeemModal");
    errorRedeemModal.classList.remove("active");
}

function redeemVoucher(){
    fetch("redeemVoucher.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            voucher_id: voucher_id
        })
    })
    .then(response => response.json())
    .then(data => {
        const confirmationRedeemModal = document.querySelector("#confirmationRedeemModal");
        confirmationRedeemModal.classList.remove("active");

        if(data.status === "success"){
            const successRedeemModal = document.querySelector("#successRedeemModal");
            document.querySelector("#successVoucherTitle").innerHTML = voucher_title;
            successRedeemModal.classList.add("active");
        } else{
            const errorRedeemModal = document.querySelector("#errorRedeemModal");
            document.querySelector("#errorVoucherTitle").innerHTML = voucher_title;
            errorRedeemModal.classList.add("active");
        }
    })
}