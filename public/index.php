<?php
// --- Now $_ENV variables are available ---
session_start(); // Start the session
// Near top of public/index.php
require __DIR__ . '/../vendor/autoload.php'; // Composer Autoloader

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // Point to project root
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    error_log("Could not load .env file: " . $e->getMessage());
}


// Enable basic error reporting for development
ini_set('display_errors', 1);
// ... rest of the file

// Enable basic error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// --- Router Setup (using nikic/fast-route) ---

// 1. Create the dispatcher
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // Define Application Routes:
    // $r->addRoute('HTTP_METHOD', '/route/pattern', 'Handler');
    // The 'Handler' can be anything: a controller action string, a closure, etc.

    // Homepage Route (GET request)
    $r->addRoute('GET', '/', 'HomeController@index'); // Example: Call index method on HomeController

    // Login Routes
    $r->addRoute('GET', '/login', 'AuthController@showLoginForm'); // Show login form
    $r->addRoute('POST', '/login', 'AuthController@login');       // Process login form submission

    // Logout Route
    $r->addRoute('GET', '/logout', 'AuthController@logout');

    // Dashboard Route (Example)
    $r->addRoute('GET', '/admin/dashboard', 'DashboardController@index');
    $r->addRoute('GET', '/admin/users', 'UserManagementController@index');
    $r->addRoute('GET', '/admin/users/create', 'UserManagementController@create');  // Show create form
    $r->addRoute('POST', '/admin/users', 'UserManagementController@store');       // Process create form
    $r->addRoute('GET', '/admin/users/edit/{id:\d+}', 'UserManagementController@edit');    // Show edit form
    $r->addRoute('POST', '/admin/users/update/{id:\d+}', 'UserManagementController@update'); // Process edit form
    $r->addRoute('POST', '/admin/users/delete/{id:\d+}', 'UserManagementController@delete'); // <-- ADD THIS LINE Process delete
    $r->addRoute('GET', '/admin/fees/categories', 'FeeCategoryController@index');     // List categories
    $r->addRoute('GET', '/admin/fees/categories/create', 'FeeCategoryController@create');  // Show create form
    $r->addRoute('POST', '/admin/fees/categories', 'FeeCategoryController@store');   // Process create form
    $r->addRoute('GET', '/admin/fees/categories/edit/{id:\d+}', 'FeeCategoryController@edit'); // <-- Ensure this exists
    $r->addRoute('POST', '/admin/fees/categories/update/{id:\d+}', 'FeeCategoryController@update'); // <-- Add this line
    $r->addRoute('POST', '/admin/fees/categories/delete/{id:\d+}', 'FeeCategoryController@delete'); // <-- Add this line
    // --- Fee Structure Routes ---
    $r->addRoute('GET', '/admin/fees/structures', 'FeeStructureController@index');     // List structures
    $r->addRoute('GET', '/admin/fees/structures/create', 'FeeStructureController@create');  // Show create form
    $r->addRoute('POST', '/admin/fees/structures', 'FeeStructureController@store');   // Process create form
    $r->addRoute('GET', '/admin/fees/structures/edit/{id:\d+}', 'FeeStructureController@edit'); // <-- Ensure this exists
    $r->addRoute('POST', '/admin/fees/structures/update/{id:\d+}', 'FeeStructureController@update'); // <-- Ensure this exists
    $r->addRoute('POST', '/admin/fees/structures/delete/{id:\d+}', 'FeeStructureController@delete'); // <-- Ensure this exists
    // --- Student Management Routes ---
    $r->addRoute('GET', '/admin/students', 'StudentController@index');     // List students
    $r->addRoute('GET', '/admin/students/create', 'StudentController@create');  // Show create form
    $r->addRoute('POST', '/admin/students', 'StudentController@store');   // Process create form
    $r->addRoute('GET', '/admin/students/edit/{id:\d+}', 'StudentController@edit'); // <-- Ensure exists
    $r->addRoute('POST', '/admin/students/update/{id:\d+}', 'StudentController@update'); // <-- Add if missing
    $r->addRoute('POST', '/admin/students/delete/{id:\d+}', 'StudentController@delete'); // <-- Add if missing
    // --- Fee Invoice Generation & Listing Routes ---
    $r->addRoute('GET', '/admin/fees/invoices', 'InvoiceController@index'); // List generated invoices
    $r->addRoute('GET', '/admin/fees/invoices/generate', 'InvoiceGenerationController@showGenerateForm'); // Show generation form
    $r->addRoute('POST', '/admin/fees/invoices/generate', 'InvoiceGenerationController@processGeneration'); // Process generation
    $r->addRoute('GET', '/admin/fees/invoices/view/{id:\d+}', 'InvoiceController@view'); // <-- ADD THIS LINE View invoice details
    // --- Payment Recording Routes ---
    // Show form pre-filled for a specific invoice
    $r->addRoute('GET', '/admin/payments/record/{invoice_id:\d+}', 'PaymentController@showRecordForm');
    // Process the payment form submission
    $r->addRoute('POST', '/admin/payments', 'PaymentController@store');
    // --- Reporting Routes ---
    $r->addRoute('GET', '/admin/reports', 'ReportController@index'); // Reports menu
    $r->addRoute('GET', '/admin/reports/fee-collection', 'ReportController@showFeeCollectionReport'); // Fee Collection Report
    $r->addRoute('GET', '/admin/reports/outstanding-dues', 'ReportController@showOutstandingDuesReport'); // Outstanding Dues Report
    $r->addRoute('GET', '/admin/reports/student-ledger', 'ReportController@showStudentLedger'); // Student Ledger
    $r->addRoute('GET', '/admin/reports/defaulters', 'ReportController@showDefaulterReport'); 
    $r->addRoute('GET', '/admin/reports/fee-collection-summary', 'ReportController@showFeeCollectionSummary'); 
    $r->addRoute('GET', '/admin/reports/audit-log', 'ReportController@showAuditLogReport'); 
    $r->addRoute('GET', '/admin/reports/ageing', 'ReportController@showAgeingReport');
    $r->addRoute('GET', '/admin/reports/transaction-detail', 'ReportController@showTransactionDetailReport');
    $r->addRoute('POST', '/admin/reports/trigger-reminders', 'ReportController@triggerReminders');

    // --- Admin Settings Route --- ADD THIS ---
    $r->addRoute(['GET', 'POST'], '/admin/settings', 'SettingsController@index');

    // --- Student/Parent Portal Routes ---
    $r->addRoute('GET', '/portal/dashboard', 'PortalDashboardController@index');
    $r->addRoute('GET', '/portal/fees', 'PortalFeeController@index');
    $r->addRoute('GET', '/portal/invoices/view/{id:\d+}', 'PortalInvoiceController@view');
    // Add Profile Routes
    $r->addRoute('GET', '/portal/profile', 'PortalProfileController@show'); // <-- ADD
    $r->addRoute('POST', '/portal/profile/update', 'PortalProfileController@updateDetails'); // <-- ADD
    $r->addRoute('POST', '/portal/profile/change-password', 'PortalProfileController@updatePassword'); // <-- ADD


    $r->addRoute('POST', '/admin/students/{id:\d+}/add-guardian', 'StudentController@addGuardianLink'); // <-- ADD
    $r->addRoute('POST', '/admin/students/remove-guardian', 'StudentController@removeGuardianLink');
    // Add Offline Payment/Proof Routes
    $r->addRoute('GET', '/portal/payments/offline/{invoice_id:\d+}', 'PortalPaymentController@showOfflinePaymentInstructions'); // <-- ADD
    $r->addRoute('POST', '/portal/payments/upload-proof', 'PortalPaymentController@handleProofUpload'); // <-- ADD
    $r->addRoute('GET', '/admin/payments/proofs', 'PaymentController@listPendingProofs'); // <-- ADD List pending proofs
    $r->addRoute('GET', '/admin/payments/proofs/view-file/{proof_id:\d+}', 'PaymentController@viewProofFile'); // <-- ADD Securely view uploaded file
    $r->addRoute('POST', '/admin/payments/proofs/reject', 'PaymentController@rejectProof'); // <-- ADD Handle rejection
    // --- Refund Routes ---
    $r->addRoute('GET', '/admin/payments/{payment_id:\d+}/refund', 'PaymentController@showRefundForm'); // <-- ADD: Show refund form for a specific payment
    $r->addRoute('POST', '/admin/payments/refund', 'PaymentController@processRefund'); // <-- ADD: Process the refund form submission
    // --- Discount Type Management Routes ---
    $r->addRoute('GET', '/admin/fees/discount-types', 'DiscountTypeController@index');     // List discount types
    $r->addRoute('GET', '/admin/fees/discount-types/create', 'DiscountTypeController@create');  // Show create form
    $r->addRoute('POST', '/admin/fees/discount-types', 'DiscountTypeController@store');   // Process create form
    $r->addRoute('GET', '/admin/fees/discount-types/edit/{id:\d+}', 'DiscountTypeController@edit'); // Show edit form
    $r->addRoute('POST', '/admin/fees/discount-types/update/{id:\d+}', 'DiscountTypeController@update'); // Process edit form
    $r->addRoute('POST', '/admin/fees/discount-types/toggle/{id:\d+}', 'DiscountTypeController@toggleStatus'); // Process toggle active
    // --- Invoice Discount Application Routes ---
    $r->addRoute('POST', '/admin/invoices/{invoice_id:\d+}/discounts/add', 'InvoiceDiscountController@addDiscount'); // <-- ADD
    $r->addRoute('POST', '/admin/invoices/discounts/remove', 'InvoiceDiscountController@removeDiscount'); // <-- ADD


    // Add more routes here as needed...
    // Example: Fee Management Routes (Protected by Auth Middleware later)
    // $r->addRoute('GET', '/fees/structures', 'FeeStructureController@index');
    // $r->addRoute('GET', '/fees/structures/create', 'FeeStructureController@create');
    // $r->addRoute('POST', '/fees/structures', 'FeeStructureController@store');

});

// 2. Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// 3. Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// 4. Remove the base path if project is in a subdirectory
// Adjust '/sfms_project/public' if your base path is different!
// IMPORTANT: Ensure your web server (Apache .htaccess) is configured
// to route all requests to this index.php file for clean URLs.
$basePath = '/sfms_project/public';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
// Ensure URI starts with a slash
$uri = '/' . ltrim($uri, '/');


// 5. Dispatch the request
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 6. Handle the route dispatch result
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found";
        // You could require a dedicated 404 view here
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        http_response_code(405);
        echo "405 - Method Not Allowed. Allowed methods: " . implode(', ', $allowedMethods);
        // You could require a dedicated 405 view here
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1]; // e.g., 'AuthController@showLoginForm'
        $vars = $routeInfo[2];    // Route parameters

        // --- Call the handler ---
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $methodName) = explode('@', $handler);

            // Construct the full class name including the namespace
            // Assuming controllers are in App\Modules\[ModuleName]\Controllers
            // This needs refinement if modules are structured differently
            // For now, we hardcode the Auth module path for this example:
            if (strpos($controllerName, 'Auth') === 0) {
                $controllerClass = "App\\Modules\\Auth\\Controllers\\" . $controllerName;
            }
            // Add this block for the Dashboard controller
            else if (strpos($controllerName, 'Dashboard') === 0) {
                $controllerClass = "App\\Modules\\Dashboard\\Controllers\\" . $controllerName;
            }
            // Add this block for UserManagement controller
            else if (strpos($controllerName, 'UserManagement') === 0) {
                $controllerClass = "App\\Modules\\UserManagement\\Controllers\\" . $controllerName;
            }
            // Add this block for FeeCategory controller
            else if (strpos($controllerName, 'FeeCategory') === 0) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            }
            else if (strpos($controllerName, 'FeeStructure') === 0) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            } 
            else if (strpos($controllerName, 'Student') === 0) {
                $controllerClass = "App\\Modules\\StudentManagement\\Controllers\\" . $controllerName;
            }
            // Add these blocks for Invoice controllers
            else if (strpos($controllerName, 'InvoiceGeneration') === 0) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            } 
            else if (strpos($controllerName, 'Invoice') === 0 && !in_array($controllerName, ['InvoiceGenerationController', 'InvoiceDiscountController'])) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            }
            // Add this block for InvoiceDiscount controller
            else if (strpos($controllerName, 'InvoiceDiscount') === 0) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            }
            // Add this block for Payment controller
            else if (strpos($controllerName, 'Payment') === 0) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            }
            // Add this block for DiscountType controller
            else if (strpos($controllerName, 'DiscountType') === 0) {
                $controllerClass = "App\\Modules\\FeeManagement\\Controllers\\" . $controllerName;
            }
            else if (strpos($controllerName, 'Report') === 0) {
                $controllerClass = "App\\Modules\\Reporting\\Controllers\\" . $controllerName;
            }
            // --- ADD Portal Controllers ---
            else if (strpos($controllerName, 'PortalDashboard') === 0) {
                $controllerClass = "App\\Modules\\Portal\\Controllers\\" . $controllerName;
            } 
            else if (strpos($controllerName, 'PortalFee') === 0) {
                $controllerClass = "App\\Modules\\Portal\\Controllers\\" . $controllerName;
            }
            else if (strpos($controllerName, 'PortalInvoice') === 0) {
                $controllerClass = "App\\Modules\\Portal\\Controllers\\" . $controllerName;
            }
            else if (strpos($controllerName, 'PortalProfile') === 0) {
                $controllerClass = "App\\Modules\\Portal\\Controllers\\" . $controllerName;
            }
            else if (strpos($controllerName, 'PortalPayment') === 0) {
                $controllerClass = "App\\Modules\\Portal\\Controllers\\" . $controllerName;
            }
            else if (strpos($controllerName, 'Settings') === 0) {
                // Assuming you create app/Modules/Admin/Controllers/
                $controllerClass = "App\\Modules\\Admin\\Controllers\\" . $controllerName;
            }
            // Add similar checks or a better mechanism for other controllers/modules
            // else if (strpos($controllerName, 'Home') === 0) {
            //     $controllerClass = "App\\Controllers\\" . $controllerName; // Example if HomeController is not in a Module subdir
            // }
            else {
                http_response_code(500);
                echo "Error: Cannot determine module for controller $controllerName";
                exit;
            }


            if (class_exists($controllerClass)) {
                $controller = new $controllerClass(); // Instantiate the controller

                if (method_exists($controller, $methodName)) {
                    // Call the method, passing route variables if any
                    // A more advanced setup might use dependency injection
                    try {
                        $controller->$methodName($vars);
                    } catch (Exception $e) {
                        // Basic error handling
                        http_response_code(500);
                        error_log("Error executing controller method: " . $e->getMessage());
                        echo "An internal server error occurred.";
                    }
                } else {
                    // Method not found in controller
                    http_response_code(500);
                    echo "Error: Method $methodName not found in $controllerName";
                    error_log("Error: Method $methodName not found in controller $controllerClass");
                }
            } else {
                // Controller class not found
                http_response_code(500);
                echo "Error: Controller $controllerClass not found";
                error_log("Error: Controller class $controllerClass not found. Check namespace/path.");
            }
        } elseif (is_callable($handler)) {
            // Handle Closures if you use them as route handlers
            try {
                call_user_func($handler, $vars);
            } catch (Exception $e) {
                http_response_code(500);
                error_log("Error executing closure handler: " . $e->getMessage());
                echo "An internal server error occurred.";
            }
        } else {
            // Invalid handler defined
            http_response_code(500);
            echo "Error: Invalid handler specified for route";
            error_log("Error: Invalid handler specified in routing definition.");
        }
        break; // End of FOUND case
}

?>