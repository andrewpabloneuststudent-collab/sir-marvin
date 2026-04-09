<?php
require_once __DIR__ . "/../conn/connection_links.php";
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
                    <form method="POST" action="">
                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control" id="floatingInput"
                                placeholder="Username" required>
                            <label for="floatingInput">Username</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Password" required>
                            <i class="fa fa-eye position-absolute" id="togglePassword"
                                style="top: 22.5px; right: 15px; cursor: pointer;"></i>
                            <label for="password">Password</label>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 mt-3">Login</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/alert.js"></script>
    <script src="../js/hideunhidepassword.js"></script>
</body>

</html>