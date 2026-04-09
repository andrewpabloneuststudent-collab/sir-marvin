<?php

namespace Classes;

// PDO DB
require_once "../conn/Database.php";

class UserManagement
{
    // Users table properties
    public int $id;
    public string $username;
    public string $password;
    public string $position;
    public int $failed_attempts = 0;
    public ?string $last_attempt = null;

    // Users_info table properties
    public string $firstname;
    public string $middlename;
    public string $lastname;
    public int $age;
    public string $street;
    public string $barangay;
    public string $city;
    public string $province;
    public string $country;
    public string $email;
    public string $contactnumber;

    private $con;
    private string $response;

    public function __construct($db)
    {
        $this->con = $db;
    }

    public function getPost()
    {
        // Initialize user properties from POST data
        if (!empty($_POST)) {
            // Users table data
            $this->username = $_POST['username'];
            $this->password = $_POST['password'];
            $this->position = $_POST['position'];

            // Users_info table data
            $this->firstname = $_POST['firstname'];
            $this->middlename = $_POST['middlename'];
            $this->lastname = $_POST['lastname'];
            $this->age = (int) ($_POST['age'] ?? 0);
            $this->street = $_POST['street'];
            $this->barangay = $_POST['barangay'];
            $this->city = $_POST['city'];
            $this->province = $_POST['province'];
            $this->country = $_POST['country'];
            $this->email = $_POST['email'];
            $this->contactnumber = $_POST['contactnumber'];
        }
    }

    public function addUser()
    {
        if (isset($_POST['addUser'])) {
            $this->getPost();

            // Check if username already exists
            $stmt = $this->con->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$this->username]);
            if ($stmt->fetch()) {
                $this->response = "Username already exists";
                return false;
            }

            // Hash password
            $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

            // Insert into users table
            $stmt = $this->con->prepare("INSERT INTO users (username, password, position, failed_attempts) VALUES (?, ?, ?, ?)");
            $stmtUser = $stmt->execute([
                $this->username,
                $hashedPassword,
                $this->position,
                0
            ]);

            if (!$stmtUser) {
                $this->response = "Failed to create user";
                return false;
            }

            // Get the last inserted user id
            $userId = $this->con->lastInsertId();

            // Insert into users_info table
            $stmt = $this->con->prepare("INSERT INTO users_info (user_id, firstname, middlename, lastname, age, street, barangay, city, province, country, email, contactnumber) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmtInfo = $stmt->execute([
                $userId,
                $this->firstname,
                $this->middlename,
                $this->lastname,
                $this->age,
                $this->street,
                $this->barangay,
                $this->city,
                $this->province,
                $this->country,
                $this->email,
                $this->contactnumber
            ]);

            if ($stmtInfo) {
                $this->response = "Success";
                return true;
            } else {
                // Delete the user if info insert failed
                $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $this->response = "Failed to add user information";
                return false;
            }
        }
        return false;
    }

    /**
     * Create a new user account with validation
     * Reusable method that can be called from any form
     * 
     * @param array $data POST data with username, password, firstname, lastname, email, position, etc.
     * @return array ['success' => bool, 'message' => string]
     */
    public function createUserAccount($data)
    {
        $response = ['success' => false, 'message' => ''];
        
        try {
            // Validate required fields
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $firstname = $data['firstname'] ?? '';
            $lastname = $data['lastname'] ?? '';
            
            if (empty($username) || empty($password) || empty($firstname) || empty($lastname)) {
                $response['message'] = 'Username, Password, First Name, and Last Name are required';
                return $response;
            }
            
            // Check if username already exists
            $stmt = $this->con->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $response['message'] = 'Username already exists';
                return $response;
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $position = $data['position'] ?? 'Staff';
            
            // Insert into users table first
            $stmt = $this->con->prepare("INSERT INTO users (username, password, position, failed_attempts) VALUES (?, ?, ?, 0)");
            $insert1 = $stmt->execute([$username, $hashedPassword, $position]);
            
            if (!$insert1) {
                $response['message'] = 'Failed to create user account';
                return $response;
            }
            
            $userId = $this->con->lastInsertId();
            
            // Collect users_info fields
            $middlename = $data['middlename'] ?? '';
            $age = intval($data['age'] ?? 0);
            $email = $data['email'] ?? '';
            $contactnumber = $data['contactnumber'] ?? '';
            $street = $data['street'] ?? '';
            $barangay = $data['barangay'] ?? '';
            $city = $data['city'] ?? '';
            $province = $data['province'] ?? '';
            $country = $data['country'] ?? '';
            
            // Insert into users_info table
            $stmt = $this->con->prepare("INSERT INTO users_info (user_id, firstname, middlename, lastname, age, email, contactnumber, street, barangay, city, province, country) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $insert2 = $stmt->execute([
                $userId,
                $firstname,
                $middlename,
                $lastname,
                $age,
                $email,
                $contactnumber,
                $street,
                $barangay,
                $city,
                $province,
                $country
            ]);
            
            if ($insert2) {
                $response['success'] = true;
                $response['message'] = 'Account created successfully';
            } else {
                $response['message'] = 'Account created but could not save details';
            }
            
        } catch (Exception $e) {
            $response['message'] = 'Database Error: ' . $e->getMessage();
        }
        
        return $response;
    }

    /**
     * Update an existing user account
     * Reusable method that can be called from any form
     * 
     * @param int $userId User ID to update
     * @param array $data POST data with firstname, lastname, email, position, etc.
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUserAccount($userId, $data)
    {
        $response = ['success' => false, 'message' => ''];
        
        try {
            // Validate user ID
            if (empty($userId)) {
                $response['message'] = 'User ID is required';
                return $response;
            }
            
            // Validate required fields
            $firstname = $data['firstname'] ?? '';
            $lastname = $data['lastname'] ?? '';
            $email = $data['email'] ?? '';
            
            if (empty($firstname) || empty($lastname) || empty($email)) {
                $response['message'] = 'First Name, Last Name, and Email are required';
                return $response;
            }
            
            // Collect all fields
            $middlename = $data['middlename'] ?? '';
            $age = intval($data['age'] ?? 0);
            $position = $data['position'] ?? 'Staff';
            $contactnumber = $data['contactnumber'] ?? '';
            $street = $data['street'] ?? '';
            $barangay = $data['barangay'] ?? '';
            $city = $data['city'] ?? '';
            $province = $data['province'] ?? '';
            $country = $data['country'] ?? '';
            
            // Update users table
            $stmt = $this->con->prepare("UPDATE users SET position = ? WHERE id = ?");
            $update1 = $stmt->execute([$position, $userId]);
            
            if (!$update1) {
                $response['message'] = 'Failed to update user account';
                return $response;
            }
            
            // Update users_info table
            $stmt = $this->con->prepare("UPDATE users_info SET firstname = ?, middlename = ?, lastname = ?, age = ?, email = ?, contactnumber = ?, street = ?, barangay = ?, city = ?, province = ?, country = ? WHERE user_id = ?");
            
            $update2 = $stmt->execute([
                $firstname,
                $middlename,
                $lastname,
                $age,
                $email,
                $contactnumber,
                $street,
                $barangay,
                $city,
                $province,
                $country,
                $userId
            ]);
            
            if ($update2) {
                $response['success'] = true;
                $response['message'] = 'Account updated successfully';
            } else {
                $response['message'] = 'Failed to update user details';
            }
            
        } catch (Exception $e) {
            $response['message'] = 'Database Error: ' . $e->getMessage();
        }
        
        return $response;
    }

    public function getAllUsers()
    {
        if (!$this->con) {
            return [];
        }
        $stmt = $this->con->prepare('SELECT u.*, ui.* FROM users u LEFT JOIN users_info ui ON u.id = ui.user_id');
        $stmt->execute();
        return $stmt->fetchAll(); // control this data so the system isnt overloaded with data, maybe add pagination in the future
    }

    public function getUserById($id)
    {
        if (!$this->con) {
            return null;
        }
        $stmt = $this->con->prepare('SELECT u.*, ui.* FROM users u LEFT JOIN users_info ui ON u.id = ui.user_id WHERE u.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateUser($userId)
    {
        if (!$userId) {
            $this->response = "Invalid user ID";
            return false;
        }

        $this->getPost();

        // Update users table
        $stmt = $this->con->prepare("UPDATE users SET position = ? WHERE id = ?");
        $stmtUser = $stmt->execute([
            $this->position,
            $userId
        ]);

        if (!$stmtUser) {
            $this->response = "Failed to update user: " . implode(", ", $stmt->errorInfo());
            return false;
        }

        // Update users_info table
        $stmt = $this->con->prepare("UPDATE users_info SET firstname = ?, middlename = ?, lastname = ?, age = ?, street = ?, barangay = ?, city = ?, province = ?, country = ?, email = ?, contactnumber = ? WHERE user_id = ?");

        $stmtInfo = $stmt->execute([
            $this->firstname,
            $this->middlename,
            $this->lastname,
            $this->age,
            $this->street,
            $this->barangay,
            $this->city,
            $this->province,
            $this->country,
            $this->email,
            $this->contactnumber,
            $userId
        ]);

        if ($stmtInfo) {
            $this->response = "Success";
            return true;
        } else {
            $this->response = "Failed to update user information: " . implode(", ", $stmt->errorInfo());
            return false;
        }
    }

    public function deleteUser($userId)
    {
        if (!$userId) {
            $this->response = "Invalid user ID";
            return false;
        }

        // Delete from users table (users_info will cascade delete due to foreign key)
        $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$userId]);

        if ($result) {
            $this->response = "Success";
            return true;
        } else {
            $this->response = "Failed to delete user";
            return false;
        }
    }

    public function updateFailedAttempts($userId, $attempts)
    {
        $stmt = $this->con->prepare("UPDATE users SET failed_attempts = ?, last_attempt = NOW() WHERE id = ?");
        return $stmt->execute([$attempts, $userId]);
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->con->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($message)
    {
        $this->response = $message;
    }
}
?>