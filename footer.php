<?php
// footer.php - Common footer
?>
    <script>
        // Display messages if any
        <?php if (isset($_SESSION['message'])): ?>
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.style.display = 'none';
                }
            }, 5000);
        <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        endif; ?>
    </script>
</body>
</html>
