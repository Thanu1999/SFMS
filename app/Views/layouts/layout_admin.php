<?php
// This layout expects $pageTitle and $content variables to be set

// Include Header
require __DIR__ . '/_header_admin.php';

// Output the main content passed from the controller/view loader
echo $content ?? '<p class="error">Error: Page content could not be loaded.</p>';

// Include Footer
require __DIR__ . '/_footer_admin.php';

?>