<?php
/**
 * File: footer.php
 * Location: /public/includes/footer.php
 * 
 * Purpose:
 * Closes the main content area, outputs the site footer, and includes 
 * global JavaScript dependencies.
 * 
 * Architectural Role:
 * Completes the HTML document structure. By placing the <script> tag here, 
 * we ensure the DOM is fully parsed before JavaScript execution begins, 
 * eliminating the need for 'DOMContentLoaded' listeners for basic bindings.
 */
declare(strict_types=1);
?>
    </main>
    
    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> FitTrack. All rights reserved. Developed by Tim Sansom (STU205472) - Arden University.</p>
    </footer>
    
    <!-- Global application logic (theme toggling, mobile menu) -->
    <script src="/js/main.js"></script>
</body>
</html>