<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'SFMS Admin'; ?> - SFMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* Minimal extra styles */
        body {
            background-color: #f8f9fa; /* Light grey background */
        }
        .navbar { margin-bottom: 20px; }
        .flash-message { margin-top: 15px; }
         /* Ensure dropdowns work with dark navbar */
        .navbar-dark .dropdown-menu { background-color: #343a40; }
        .navbar-dark .dropdown-item { color: rgba(255,255,255,.55); }
        .navbar-dark .dropdown-item:hover, .navbar-dark .dropdown-item:focus { color: #fff; background-color: rgba(255,255,255,.15); }
         .error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
         .success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem;}
        .select2-container .select2-selection--single { height: calc(1.5em + .75rem + 2px); /* Match Bootstrap default height */ }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.5; padding-left: .75rem; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow { height: calc(1.5em + .75rem); } /* Adjust arrow position */
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/sfms_project/public/admin/dashboard">SFMS Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false) ? 'active' : ''; ?>" href="/sfms_project/public/admin/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/students') !== false) ? 'active' : ''; ?>" href="/sfms_project/public/admin/students"><i class="bi bi-person-badge"></i> Students</a>
        </li>
         <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false) ? 'active' : ''; ?>" href="/sfms_project/public/admin/users"><i class="bi bi-people-fill"></i> Users</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/fees') !== false) ? 'active' : ''; ?>" href="#" id="feesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
           <i class="bi bi-receipt-cutoff"></i> Fees Mgmt
          </a>
          <ul class="dropdown-menu" aria-labelledby="feesDropdown">
            <li><a class="dropdown-item" href="/sfms_project/public/admin/fees/categories">Fee Categories</a></li>
            <li><a class="dropdown-item" href="/sfms_project/public/admin/fees/structures">Fee Structures</a></li>
             <li><a class="dropdown-item" href="/sfms_project/public/admin/fees/discount-types">Discount Types</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/sfms_project/public/admin/fees/invoices">View Invoices</a></li>
            <li><a class="dropdown-item" href="/sfms_project/public/admin/fees/invoices/generate">Generate Invoices</a></li>
             <li><a class="dropdown-item" href="/sfms_project/public/admin/payments/proofs">Verify Proofs</a></li>
          </ul>
        </li>
         <li class="nav-item">
          <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false) ? 'active' : ''; ?>"
              href="/sfms_project/public/admin/reports"><i class="bi bi-graph-up"></i> Reports</a>
          </li>
          <?php // Only show Settings to Admin ?>
          <?php if ($this->hasRole('Admin')): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false) ? 'active' : ''; ?>"
                href="/sfms_project/public/admin/settings"><i class="bi bi-sliders"></i> Settings</a>
            </li>
          <?php endif; ?>
          </ul>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
             <span class="navbar-text me-2">
                 Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!
             </span>
          </li>
         <li class="nav-item">
             <a class="nav-link" href="/sfms_project/public/logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
         </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">

    <?php $flash_success = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']); ?>
    <?php $flash_error = $_SESSION['flash_error'] ?? null; unset($_SESSION['flash_error']); ?>
    <?php if ($flash_error): ?><div class="alert alert-danger flash-message"><?php echo $flash_error; ?></div><?php endif; ?>
    <?php if ($flash_success): ?><div class="alert alert-success flash-message"><?php echo $flash_success; ?></div><?php endif; ?>
    <?php // Note: $dbError passed to specific views will need separate display logic if still desired ?>