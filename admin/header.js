function toggleSidebarVisibility() {

    const sidebar = document.querySelector(".sidebar");
    const closeBtn = document.querySelector(".close-sidebar-btn");

    sidebar.classList.toggle("active");
    closeBtn.classList.toggle("active");

}