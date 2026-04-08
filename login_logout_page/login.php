<?php
require_once __DIR__ . "/../function/loginfunction.php";

use Classes\Project;

global $db;

$error_message = '';

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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="modal show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100">Login</h5>
                </div>
                <div class="modal-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="" id="loginForm">
                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control" id="username" required autocomplete="off" value="">
                            <label for="username">Username</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" id="password" name="password" class="form-control" autocomplete="off" value="">
                            <label for="password">Password</label>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 mt-3">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Force clear fields on page load to fight browser autofill
        window.onload = function() {
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
        };
    </script>
</body>

</html>