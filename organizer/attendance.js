const eventButtons = document.querySelectorAll(".all_events button");

eventButtons.forEach(btn => {
    btn.addEventListener('click', function(){
        document.querySelector(".all_events button.active").classList.remove("active");
        this.classList.add("active");

        const eventId = btn.dataset.id;
        fetchEventData(eventId);
    })
})

document.querySelector(".all_approvals").addEventListener('click', function(e){
    if(!e.target.matches(".btn-actions button")){
        return;
    }
    const btn = e.target;
    const approval = btn.closest(".approval_container");console.log(approval)
    const user_id = approval.dataset.userId;
    const event_id = approval.dataset.eventId;
    const action = btn.dataset.action;
    if(event_id !== null && action !== null){
        fetch("update_participant_attendance.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ user_id: user_id, event_id: event_id, action: action })
        }).then(response => response.json())
        .then(data => {
            if(data.status === "success"){
                fetchEventData(event_id);
            }
        })
    }
})

function fetchEventData(event_id){
    if(event_id !== null){
        fetch("get_event_data.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ event_id: event_id})
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === "success"){
                document.querySelector(".event_stats .registered h2").innerHTML = data.stats.total;
                document.querySelector(".event_stats .pending h2").innerHTML = data.stats.pending;
                document.querySelector(".event_stats .completed h2").innerHTML = data.stats.completed;

                const all_approvals = document.querySelector('.all_approvals');

                /* Remove previous content */
                all_approvals.innerHTML = "";
                console.log(data.pending_approval)
                data.pending_approval.forEach(approval => {
                    const approvalHtml = `
                        <div class="approval_container" data-event-id=${approval.event_id} data-user-id=${approval.user_id}>
                            <img src="${approval.proof_image}" alt="Student Photo Proof" />
                            <div class="approval_data">
                                <div class="approval_user_info">
                                    <img src=${approval.avatar} alt="User Avatar" />
                                    <h2>${approval.username}</h2>
                                    <p class="email">${approval.email}</p>
                                </div>
                                <p class="description">${approval.description}</p>
                            </div>       
                            <div class="btn-actions">
                                <button class="reject" data-action="reject">Reject</button>
                                <button class="approve" data-action="approve">Approve</button>
                            </div>
                        </div>
                    `;
                    all_approvals.insertAdjacentHTML('beforeend', approvalHtml);
                });

                const all_attendance = document.querySelector('.all-attendance');

                /* Remove previous content */
                all_attendance.innerHTML = "";
                data.all_approval.forEach(approval => {
                    const approvalHtml = `
                        <tr>
                            <td data-label="Student Name">${approval.username}</td>
                            <td data-label="Student Email">${approval.email}</td>
                            <td data-label="Status"><span class="status-${approval.attendance_status.toLowerCase()}">${approval.attendance_status}</span></td>
                        </tr>
                    `;
                    all_attendance.insertAdjacentHTML('beforeend', approvalHtml);
                })
            }
        })
    }
}
