<?php
// events.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'database.php';

$pageTitle = 'Campus Events';
getHeader($pageTitle);

// Handle event deletion (admin only)
if (isset($_GET['delete']) && isAdmin()) {
    $eventId = intval($_GET['delete']);
    if (deleteEvent($eventId)) {
        $_SESSION['message'] = 'Event deleted successfully.';
        $_SESSION['message_type'] = 'success';
        header("Location: events.php");
        exit();
    }
}

// Handle event addition (admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event']) && isAdmin()) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $location = trim($_POST['location']);
    
    if (addEvent($title, $description, $date, $location)) {
        $_SESSION['message'] = 'Event added successfully!';
        $_SESSION['message_type'] = 'success';
        header("Location: events.php");
        exit();
    } else {
        $_SESSION['message'] = 'Error adding event.';
        $_SESSION['message_type'] = 'error';
    }
}

$events = getUpcomingEvents(20);
?>

<main class="container page-content">
    <div class="page-header d-flex justify-between align-center mb-4">
        <div>
            <h1><i class="fas fa-calendar-alt text-primary"></i> Campus Events</h1>
            <p class="text-muted">Upcoming events, workshops, and activities.</p>
        </div>
        
        <?php if (isAdmin()): ?>
            <button onclick="document.getElementById('addEventModal').style.display='block'" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Event
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($events)): ?>
        <div class="card p-5 text-center">
            <div class="text-muted mb-3"><i class="fas fa-calendar-times fa-3x"></i></div>
            <h3>No upcoming events</h3>
            <p class="mb-4">Check back later for updates.</p>
        </div>
    <?php else: ?>
        <div class="events-list">
            <?php foreach ($events as $event): ?>
                <div class="card mb-4 event-card">
                    <div class="card-body d-flex gap-4">
                        <div class="event-date-box text-center p-3 rounded bg-light" style="min-width: 100px;">
                            <?php 
                                $date = new DateTime($event['event_date']);
                            ?>
                            <div class="text-uppercase text-danger fw-bold" style="font-size: 0.9rem;"><?php echo $date->format('M'); ?></div>
                            <div class="display-4 fw-bold text-dark" style="line-height: 1;"><?php echo $date->format('d'); ?></div>
                            <div class="text-muted small"><?php echo $date->format('D'); ?></div>
                        </div>
                        
                        <div class="event-details flex-grow-1">
                            <h3 class="card-title mb-2 text-primary"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="event-meta mb-3 d-flex gap-3 text-muted">
                                <span><i class="far fa-clock"></i> <?php echo $date->format('h:i A'); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                        
                        <?php if (isAdmin()): ?>
                            <div class="event-actions">
                                <a href="?delete=<?php echo $event['id']; ?>" 
                                   class="btn btn-sm btn-outline text-danger"
                                   onclick="return confirm('Delete this event?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Add Event Modal (Simple implementation) -->
<div id="addEventModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; padding: 20px;">
    <div class="modal-content card m-auto" style="max-width: 500px; padding: 0;">
        <div class="card-header d-flex justify-between align-center">
            <h3>Add New Event</h3>
            <button onclick="document.getElementById('addEventModal').style.display='none'" class="btn btn-sm text-muted"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label>Event Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Date & Time</label>
                    <input type="datetime-local" name="date" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="form-group mb-4">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="d-flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('addEventModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" name="add_event" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Minor CSS for this page specifically */
.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--input-bg);
    color: var(--text-main);
    margin-top: 5px;
}
.gap-4 { gap: 1.5rem; }
.gap-3 { gap: 1rem; }
.gap-2 { gap: 0.5rem; }
.fw-bold { font-weight: 700; }
.display-4 { font-size: 2rem; }
.bg-light { background: var(--bg-body); }
.justify-end { justify-content: flex-end; }
</style>

<?php getFooter(); ?>
