<?php

namespace Classes;

// PDO DB
require_once __DIR__ . "/../conn/database.php"; //yours is Database.php

global $db; // Make $db accessible


class Project
{
    public int $username;
    public string $password;
    private $con;
    private string $response;

    public function __construct($db)
    {
        $this->con = $db;
    }

    public function login(string $username, string $password)
    {
        // 🔍 GET USER
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // ✅ USER EXISTS
        if ($user) {
            // 🔓 PLAIN TEXT VERIFY (For Development)
            if ($password === $user['password']) {

                // 🔐 SESSION
                session_start();
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['position'] = $user['position'];

                // 🔁 REDIRECT BASED ON ROLE
                if ($user['position'] === 'owner') {
                    header('Location: /' . basename(dirname(__DIR__)) . '/ownerpage/dashboard.php');
                    exit;
                } elseif ($user['position'] === 'admin') {
                    header('Location: /' . basename(dirname(__DIR__)) . '/adminpage/dashboard.php');
                    exit;
                } elseif ($user['position'] === 'staff') {
                    header('Location: /' . basename(dirname(__DIR__)) . '/staffpos/dashboard.php');
                    exit;
                }
            } else {
                return "Invalid username or password";
            }
        } else {
            return "Invalid username or password";
        }
    }
    private function responseSQL($stmt)
    {
        if ($stmt) {
            $this->response = "Success";
        } else {
            $this->response = "Failed";
        }
    }
}
?>