<?php
// File: app/Views/layouts/layout_portal.php
// Includes portal header/footer and echoes $content

require __DIR__ . '/_header_portal.php';

echo $content ?? '<p class="alert alert-danger">Error: Page content could not be loaded.</p>';

require __DIR__ . '/_footer_portal.php';

?>