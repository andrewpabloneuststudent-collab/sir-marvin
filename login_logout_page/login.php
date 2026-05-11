<?php
require_once __DIR__ . "/../conn/database.php";
// connection_links.php loaded in <head> below — must NOT be here (outputs HTML before login redirect headers)
require_once __DIR__ . "/../function/loginfunction.php";

use Classes\Project;

global $db;

$error_message = '';

// PROCESS LOGIN BEFORE ANY HTML OUTPUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($db) {
        $project = new Project($db);
        $error_message = $project->login($username, $password);
    } else {
        $error_message = "Database connection error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management System - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <!-- LEFT: Brand Panel -->
    <div class="login-left">

        <!-- Logo -->
        <div class="brand-logo">
            <div class="brand-icon"><i class="fas fa-pills"></i></div>
            <div class="brand-name">
                MMB'S DRUGSTORE
                <span>Pharmacy Management System</span>
            </div>
        </div>

        <!-- Hero -->
        <div class="login-hero">
            <h1>Manage your<br><em>pharmacy</em><br>smarter.</h1>
            <p>A comprehensive POS and inventory system built for modern healthcare operations.</p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-dot red"><i class="fas fa-cash-register"></i></div>
                    <div class="feature-text">
                        <strong>Point of Sale</strong>
                        <span>Fast, reliable checkout with discount support</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-dot teal"><i class="fas fa-boxes"></i></div>
                    <div class="feature-text">
                        <strong>Inventory Control</strong>
                        <span>Track stock levels and expiry dates in real-time</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-dot indigo"><i class="fas fa-chart-bar"></i></div>
                    <div class="feature-text">
                        <strong>Sales Analytics</strong>
                        <span>Daily and monthly performance at a glance</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT: Login Form -->
    <div class="login-right">
        <div class="login-card">

            <div class="login-card-header">
                <div class="welcome-tag"><i class="fas fa-shield-halved"></i> Authorized Access Only</div>
                <h2>Welcome back</h2>
                <p>Sign in to your account to continue</p>
            </div>

            <?php if ($error_message): ?>
            <div class="login-error">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    <div class="err-text">Login Failed</div>
                    <div class="err-sub"><?php echo htmlspecialchars($error_message); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="login-field">
                    <label for="username">Username</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" id="username" name="username"
                               placeholder="Enter your username" required autofocus
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" required>
                        <button type="button" class="toggle-pw" id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn-signin">
                    <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                </button>

            </form>

            <div class="secure-note">
                <i class="fas fa-lock"></i>
                Secured &amp; encrypted connection
            </div>

        </div>
    </div>

    <script src="js/hideunhidepassword.js"></script>
    <script>
        // Make toggle work with new IDs
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pw.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>

</body>
</html>