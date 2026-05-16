function toggleUsers() {
    const tableBody = document.getElementById('volunteer-body');
    const viewBtn = document.getElementById('view-all-btn');

    if (tableBody.classList.contains('limit-view')) {
        tableBody.classList.remove('limit-view');
        viewBtn.innerText = "Show Less";
    } else {
        tableBody.classList.add('limit-view');
        viewBtn.innerText = "View All";
    }
}

document.addEventListener("DOMContentLoaded", function(){
    const eventSwitch = document.getElementById("event-switch");

    eventSwitch.addEventListener("change", function(){
        const selectedEventID = this.value;
        
        if(selectedEventID){
            if (selectedEventID === "all") {
                updateDashboardUI(initialDashboardState);
            } else{
                fetch("retrieveEventImpact.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ event_id: selectedEventID})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === "success"){
                        updateDashboardUI(data);
                    }
                })
            }
        }
    })
})

function updateDashboardUI(data){
    document.getElementById("val-registered").innerText = data.registered;
    document.getElementById("val-attendance").innerText = data.attendanceRate + "%";
    document.getElementById("val-hours").innerText = Number(data.volunteerHours).toFixed(2);
    document.getElementById("val-impact").innerText = data.impactScore;

    let treePercentage = Math.min(100, Math.round((data.treesPlanted / 100) * 100));
    let wastePercentage = Math.min(100, Math.round((data.wasteCollected / 500) * 100));

    treePercentage = treePercentage || 0; 
    wastePercentage = wastePercentage || 0;

    document.getElementById("label-trees").innerText = `Trees Planted (${treePercentage}%)`;
    document.getElementById("bar-trees").style.width = `${treePercentage}%`;

    document.getElementById("label-waste").innerText = `Waste Collected (${wastePercentage}%)`;
    document.getElementById("bar-waste").style.width = `${wastePercentage}%`;

    const tbody = document.getElementById("volunteer-body");
    tbody.innerHTML = "";
    if (!data.participantResult || data.participantResult.length === 0) {
        tbody.innerHTML = "<tr><td colspan='4' style='text-align:center;'>No participants found for this event.</td></tr>";
    }

    let registered = 0;
    let pending = 0;
    let present = 0;
    let absent = 0;

    data.participantResult.forEach(row => {
        registered ++;
        let hours = 0;
        if (row.attendance_status === 'PRESENT') {
            const start = new Date(`1970-01-01T${row.time_start}Z`).getTime();
            const end = new Date(`1970-01-01T${row.time_end}Z`).getTime();
            hours = Math.round(((end - start) / 3600000) * 10) / 10;
            present ++;
        } else if(row.attendance_status === 'ABSENT'){
            absent ++;
        } else if(row.attendance_status === 'PENDING'){
            pending ++;
        }

        const joinedDate = new Date(row.date_joined);
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        const formattedDate = joinedDate.toLocaleDateString('en-GB', options);

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="name">${row.username}</td>
            <td>${formattedDate}</td>
            <td>${hours}</td>
            <td>
                <span class="status-${row.attendance_status.toLowerCase()}">
                    ${row.attendance_status}
                </span>
            </td>
        `;
        tbody.appendChild(tr);
    });

    const barRegistered = document.querySelector('.bar-green');
    const barPending = document.querySelector('.bar-orange');
    const barPresent = document.querySelector('.bar-green-mid');
    const barAbsent = document.querySelector('.bar-red');

    barRegistered.setAttribute('data-value', registered);
    barPending.setAttribute('data-value', pending);
    barPresent.setAttribute('data-value', present);
    barAbsent.setAttribute('data-value', absent);
    refreshBarChartVisuals();
}

document.addEventListener('DOMContentLoaded', () => {
    const bars = document.querySelectorAll('.bar');

    bars.forEach(bar => {
        bar.addEventListener('click', (e) => {
            // Prevent the document click listener from firing
            e.stopPropagation();

            const isShowing = bar.classList.contains('show-number');

            // Reset all bars first
            bars.forEach(b => b.classList.remove('show-number'));

            // Toggle current bar if it wasn't already showing
            if (!isShowing) {
                bar.classList.add('show-number');
            }
        });
    });

    // Close tooltip when clicking anywhere else
    document.addEventListener('click', () => {
        bars.forEach(b => b.classList.remove('show-number'));
    });
});

document.addEventListener('DOMContentLoaded', () => {
    refreshBarChartVisuals();
});

function refreshBarChartVisuals(){
    const bars = document.querySelectorAll('.bar');

    let maxValue = 0;

    // Find max value
    bars.forEach(bar => {

        const value = parseInt(bar.getAttribute('data-value'));

        if(value > maxValue){
            maxValue = value;
        }

    });

    // Apply heights + tooltip
    bars.forEach(bar => {

        const value = parseInt(bar.getAttribute('data-value'));

        const heightPercent = (maxValue > 0) ? (value / maxValue) * 100 : 0;

        bar.style.height = heightPercent + "%";

        const tooltip = bar.querySelector('.tooltip');

        tooltip.innerHTML = 
        bar.nextElementSibling.innerText +
        "<br>" + value + " students";

    });
}