<?php
    include "../conn.php";
    include("../auth.php");

    // Check if the user is an ORGANIZER
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        header("Location: ../login/login.php");
        exit();
    }

    $events_data = [];
    // Get the current logged-in user's ID from the session
    $user_id = $_SESSION['user_id']; 

    try {
        // We filter by organizer_id so they only see their own eco-events
        $sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY event_date DESC";
        
        // Using a prepared statement for security
        $stmt = mysqli_prepare($db_connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);

        if(!$result){
            throw new Exception("Fail to fetch organizer events");
        }

        while($row = mysqli_fetch_assoc($result)){
            $events_data[] = $row;
        }

        $sql_categories = "SELECT * FROM categories";
        $categories_result = mysqli_query($db_connect, $sql_categories);

        $categories = [];

        if ($categories_result) {
            while ($row = mysqli_fetch_assoc($categories_result)) {
                $categories[] = $row;
            }
        }

    } catch(Exception $e) {
        error_log("Organizer failed to fetch events: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoStudent Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="events.css">
<body>
<img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php"><img src="../assets/images/home.png" alt="Home Icon">Dashboard</a>
            </li>
            <li class="active_nav">
                <a href="events.php"><img src="../assets/images/event.png" alt="Calendar Icon">Event Management</a>
            </li>
            <li>
                <a href="attendance.php"><img src="../assets/images/group.png" alt="Participants Icon">Attendance Approval</a>
            </li>
            <li>
                <a href="report.php"><img src="../assets/images/business-report.png" alt="Report Icon">Impact Report</a>
            </li>
            <li>
                <a href="profile.php"><img src="../assets/images/user.png" alt="User Icon">Profile</a>
            </li>
            <li class="signout-item">
                <a href="../logout.php"><img src="../assets/images/logout.png" alt="Sign Out Icon">Sign Out</a>
            </li>
        </ul>
    </aside>

    <div class="main-wrapper">
        <header class="top-header">
            <div class="hamburger-menu" onclick="toggleSidebarVisibility()">
                 <img src="../assets/images/hamburger-menu.png" alt="Hamburger Menu" />
            </div>
            <div class="logo-icon">
                <img src="../assets/images/logo-black.png" alt="EcoRiseAPU Logo" />
            </div>
            <div class="user-profile">
                <div class="user-text">
                    <strong><?php echo htmlspecialchars($username) ?></strong>
                    <small>ORGANIZER</small>
                </div>
                <img src="<?php echo htmlspecialchars($avatar) ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

    <div class="dashboard-container">
            <header class="main-header">
                <div class="page-heading">
                    <h1>Event Management</h1>
                    <p>Manage and track your environmental initiatives.</p>
                </div>
                <button class="btn-create" onclick="openCreateModal()">
                    <span class="plus-icon">+</span> Create Event
                </button>
            </header>

        <div id="eventFormModal" class="blur-background" style="display: none;">
            <form class="event-form-content" id="eventDataForm">
                <div class="modal-header">
                    <h1 id="modalTitle">Edit Event</h1>
                    <span class="close-modal" onclick="closeModal('eventFormModal')">&times;</span>
                </div>
                
                <input type="hidden" id="eventId">
                
                <div class="form-grid">
                    <div class="input-field">
                        <label>Event Title</label>
                        <input type="text" id="eventTitle" required>
                    </div>
                    <div class="input-field">
                        <label>Location</label>
                        <input type="text" id="eventLocation" required>
                    </div>
                    <div class="input-field">
                        <label>Date</label>
                        <input type="date" id="eventDate" required>
                    </div>
                    <div class="input-field">
                        <label>Category</label>
                        <select id="eventCategory">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-field">
                        <label>Start Time:</label>
                        <input type="time" id="start" name="start" required>
                    </div>
                    <div class="input-field">
                        <label>End Time:</label>
                        <input type="time" id="end" name="end" required>
                    </div>
                </div>

                <div class="info-item image-field">
                    <label>Cover Image</label>
                    <div class="image-input">
                        <input type="file" id="eventImage" accept="image/*">   
                        <img src="" id="prevEventImage" alt="Previous Image" style="display: none" />
                    </div> 
                </div>

                <div class="info-item">
                    <label>Max Participants</label>
                    <input type="number" id="eventMaxParticipants" name="max_participants" min="1" placeholder="1" required>
                </div>


                <div class="info-item">
                    <label for="eventDescription">Description</label>
                    <textarea id="eventDescription" rows="3" required></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-submit-event">Save Event Changes</button>
                </div>
            </form>
        </div>

        <div id="successModal" class="blur-background" style="display: none;">
            <div class="modal-card">
                <img src="./../assets/images/tick.png" alt="Success" class="status-icon" />
                <h2 id="successTitle">Success!</h2>
                <p id="successMsg">Action completed.</p>
                <button class="btn-confirm success" onclick="closeModal('successModal')">OK</button>
            </div>
        </div>
    </div>

    <div id="setImpactModal" class="blur-background" style="display: none;">
        <form class="event-form-content" id="impactDataForm">
            <div class="modal-header">
                <h1 id="impactModalTitle">Record Final Impact</h1>
                <span class="close-modal" onclick="closeModal('setImpactModal')">&times;</span>
            </div>
            
            <input type="hidden" id="impactEventId">
            
            <p style="margin-bottom: 20px; color: var(--text-muted); font-size: 0.9rem;">
                Enter the final achievements for this completed event. Once saved, these stats will be locked.
            </p>
            
            <div class="form-grid">
                <div class="input-field">
                    <label for="actualTrees">Total Trees Planted</label>
                    <input type="number" id="actualTrees" min="0" placeholder="e.g. 50">
                </div>
                <div class="input-field">
                    <label for="actualWaste">Total Waste Collected (kg)</label>
                    <input type="number" id="actualWaste" min="0" step="0.1" placeholder="e.g. 12.5">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-submit-event">Save Impact Stats</button>
            </div>
        </form>
    </div>
    <script src="header.js"></script>
    <script src="events.js"></script>



<div class="dashboard-container">
    <?php if (empty($events_data)): ?>
        <p style="text-align: center; color: var(--text-muted); margin-top: 50px;">No events found.</p>
    <?php else: ?>
        <?php foreach ($events_data as $event): ?>
            <article class="event-card" id="event-<?php echo $event['event_id']; ?>">
                <div class="event-image">
                    <img src="<?php echo !empty($event['event_image']) ? htmlspecialchars($event['event_image']) : 'event_default.jpg'; ?>" alt="Event">
                </div>
                
                <?php 
                    $status = strtoupper($event['status']);
                    $bannerClass = 'banner-pending';
                    if($status == 'REJECTED') $bannerClass = 'banner-rejected';
                    if($status == 'APPROVED' || $status == 'COMPLETED') $bannerClass = 'banner-approved';
                ?>
                <div class="status-ribbon <?php echo $bannerClass; ?>"><?php echo $status; ?></div>

                <div class="card-content">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                    </div>
                    
                    <div class="card-actions">
                        <?php if ($status === 'APPROVED' || $status === 'COMPLETED'): ?>
                            <button class="icon-btn" aria-label="View Participants" onclick="window.location.href='attendance.php?event_id=<?php echo $event['event_id']; ?>'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </button>
                        <?php endif; ?>

                        <?php if ($status === 'PENDING'): ?>
                            <button class="icon-btn edit" onclick='openEditModal(<?php echo json_encode($event); ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>
                                </svg>
                            </button>
                        <?php endif; ?>

                        <button class="icon-btn delete" onclick="confirmDelete(<?php echo $event['event_id']; ?>)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="deleteConfirmModal" class="blur-background" style="display: none;">
                    <div class="modal-card">
                        <div style="text-align: center;">
                            <div style="font-size: 50px; color: #ef4444; margin-bottom: 20px;">⚠️</div>
                            <h2 style="color: var(--text-main); margin-bottom: 10px;">Are you sure?</h2>
                            <p style="color: var(--text-muted); margin-bottom: 30px;">
                                This action cannot be undone. This event will be permanently removed from the database.
                            </p>
                            
                            <input type="hidden" id="pendingDeleteId">
                            
                            <div style="display: flex; gap: 15px; justify-content: center;">
                                <button class="btn-confirm" style="background-color: #64748b;" onclick="closeModal('deleteConfirmModal')">Cancel</button>
                                <button class="btn-confirm" style="background-color: #ef4444;" onclick="executeDelete()">Delete Permanently</button>
                            </div>
                        </div>
                    </div>
                </div>
                    
                <hr class="divider">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>LOCATION</label>
                            <div class="info-value"><?php echo htmlspecialchars($event['location']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>DATE</label>
                            <div class="info-value"><?php echo htmlspecialchars($event['event_date']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>TIME</label>
                            <div class="info-value"><?php echo htmlspecialchars($event['time_start']) . " - " . htmlspecialchars($event['time_end']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>MAX PARTICIPANTS</label>
                            <div class="info-value"><?php echo htmlspecialchars($event['max_participants']); ?></div>
                        </div>
                    </div>
                    
                    <?php 
                        $current_date = date('Y-m-d'); 
                    ?>
                    <?php if ($status === 'COMPLETED'): ?>
                        <div class="impact-mini-section">
                            <div class="impact-badge">
                                <i class="fas fa-leaf"></i> FINAL IMPACT
                            </div>
                            
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Trees Planted</label>
                                    <div class="impact-val-row">
                                        <strong><?php echo (int)($event['trees_planted'] ?? 0); ?></strong>
                                        <span>Saplings</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <label>Waste Collected</label>
                                    <div class="impact-val-row">
                                        <strong><?php echo number_format(($event['waste_collected'] ?? 0), 1); ?></strong>
                                        <span>KG</span>
                                    </div>
                                </div>
                            </div>

                            <div class="impact-footer">
                                <button type="button" class="link-update" onclick="openImpactModal(<?php echo $event['event_id']; ?>)">
                                    Update Stats
                                </button>
                            </div>
                        </div>
                    <?php elseif($status === 'APPROVED' && $current_date > $event['event_date']): ?>
                        <button class="btn-impact" onclick="openImpactModal(<?php echo $event['event_id']; ?>)">
                            Set Final Impact Stats
                        </button>
                    <?php elseif ($status === 'REJECTED'): ?>
                        <div style="margin-top: 15px; padding: 12px; text-align: center; color: #d32f2f; font-weight: 600; border: 1px solid #ffcdd2; border-radius: 20px; background: #ffebee;">
                            Event Rejected: Modifications Disabled
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 15px; padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem; border: 1px solid #eee; border-radius: 20px;">
                            Impact stats available after completion.
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
<script src="header.js"></script>
</html>