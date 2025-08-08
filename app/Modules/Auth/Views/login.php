<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SFMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* Custom styles for login page */
        body {
            /* Path relative to the public directory */
            background-image: url('/sfms_project/public/images/login-bg.jpg');
            /* Replace with your image path */
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            /* Keeps background fixed during scroll */
            height: 100vh;
            /* Full viewport height */
            display: flex;
            align-items: center;
            /* Vertical centering */
            justify-content: center;
            /* Horizontal centering */
        }

        .login-card {
            max-width: 450px;
            /* Max width of the login box */
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            /* Slightly transparent white background */
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .login-card h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-floating>label {
            padding-left: 2.5rem;
            /* Make space for icon */
        }

        .form-floating .form-control-icon {
            position: absolute;
            z-index: 2;
            display: block;
            width: 2.375rem;
            height: calc(3.5rem + 2px);
            line-height: 3.5rem;
            text-align: center;
            pointer-events: none;
            color: #6c757d;
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: .75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h1><i class="bi bi-building"></i> SFMS Login</h1>

        <?php
        // Get error from session (assuming it's set there by controller on failure)
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']); // Clear after reading
        ?>
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="/sfms_project/public/login" method="POST">
            <?php if (isset($_csrf_token)): // Check if the variable was passed ?>
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token); ?>">
            <?php else: ?>
                <?php error_log("CSRF token missing in view: login.php - Form will likely fail."); ?>
                <p class="error">Security token is missing. Please refresh.</p>
            <?php endif; ?>

            <div class="form-floating mb-3">
                <span class="form-control-icon"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control ps-5" id="username" name="username" placeholder="Username"
                    required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <span class="form-control-icon"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control ps-5" id="password" name="password" placeholder="Password"
                    required>
                <label for="password">Password</label>
            </div>

            <div class="d-grid">
                <button class="btn btn-primary btn-lg" type="submit">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>