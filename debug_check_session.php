<?php
// debug_check_session.php
session_start();

if (isset($_SESSION['debug_user'])) {
    echo "<h1>✅ Session persist test PASSED!</h1>";
    echo "<p>User ID: " . $_SESSION['debug_user'] . "</p>";
    echo "<p>Your hosting handles sessions correctly.</p>";
    echo "<a href='login.php'>Go to real Login</a>";
} else {
    echo "<h1>❌ Session persist test FAILED.</h1>";
    echo "<p>The session data was lost between pages.</p>";
}
?>
