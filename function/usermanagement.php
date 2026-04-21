<?php

namespace Classes;

require_once "../conn/Database.php";

class UserManagement
{
    private $con;

    public function __construct($db)
    {
        $this->con = $db;
    }

    // ✅ CLEAN INPUT HANDLER
    private function input(array $data): array
    {
        return [
            'username' => $data['username'] ?? '',
            'password' => $data['password'] ?? '',
            'position' => $data['position'] ?? 'Staff',

            'firstname' => $data['firstname'] ?? '',
            'middlename' => $data['middlename'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'age' => (int) ($data['age'] ?? 0),

            'street' => $data['street'] ?? '',
            'barangay' => $data['barangay'] ?? '',
            'city' => $data['city'] ?? '',
            'province' => $data['province'] ?? '',
            'country' => $data['country'] ?? '',

            'email' => $data['email'] ?? '',
            'contactnumber' => $data['contactnumber'] ?? ''
        ];
    }

    // 🔥 ADD USER (TRANSACTION SAFE)
   public function addUser(array $data): array
{
    $d = $this->input($data);

    try {
        $this->con->beginTransaction();

        // Check username
        $stmt = $this->con->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$d['username']]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // ✅ Default: no void password
        $voidRaw = null;

        // ✅ ONLY generate if NOT staff
        if (strtolower($d['position']) !== 'staff') {
            $voidRaw = random_int(1000, 9999); // plain PIN
        }

        // Insert user (NO HASH for void_password)
        $this->con->prepare("
            INSERT INTO users (username, password, position, failed_attempts, void_password)
            VALUES (?, ?, ?, 0, ?)
        ")->execute([
            $d['username'],
            password_hash($d['password'], PASSWORD_BCRYPT), // keep login password hashed
            $d['position'],
            $voidRaw // ✅ store plain PIN
        ]);

        $userId = $this->con->lastInsertId();

        // Insert info
        $this->con->prepare("
            INSERT INTO users_info
            (user_id, firstname, middlename, lastname, age, street, barangay, city, province, country, email, contactnumber)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $userId,
            $d['firstname'],
            $d['middlename'],
            $d['lastname'],
            $d['age'],
            $d['street'],
            $d['barangay'],
            $d['city'],
            $d['province'],
            $d['country'],
            $d['email'],
            $d['contactnumber']
        ]);

        $this->con->commit();

        return [
            'success' => true,
            'message' => 'User added successfully',
            'void_pin' => $voidRaw // show in alert if needed
        ];

    } catch (\Throwable $e) {
        $this->con->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

    // 🔥 UPDATE USER (MERGED VERSION - no duplicate methods)
    public function updateUser(int $userId, array $data): array
    {
        if (!$userId) {
            return ['success' => false, 'message' => 'Invalid user ID'];
        }

        $d = $this->input($data);

        if (!$d['firstname'] || !$d['lastname'] || !$d['email']) {
            return ['success' => false, 'message' => 'Required fields missing'];
        }

        try {
            $this->con->beginTransaction();

            // Update users
            $this->con->prepare("
                UPDATE users SET position = ? WHERE id = ?
            ")->execute([$d['position'], $userId]);

            // Update info
            $this->con->prepare("
                UPDATE users_info SET 
                firstname=?, middlename=?, lastname=?, age=?, email=?, contactnumber=?, 
                street=?, barangay=?, city=?, province=?, country=?
                WHERE user_id=?
            ")->execute([
                        $d['firstname'],
                        $d['middlename'],
                        $d['lastname'],
                        $d['age'],
                        $d['email'],
                        $d['contactnumber'],
                        $d['street'],
                        $d['barangay'],
                        $d['city'],
                        $d['province'],
                        $d['country'],
                        $userId
                    ]);

            $this->con->commit();

            return ['success' => true, 'message' => 'User updated successfully'];

        } catch (\Throwable $e) {
            $this->con->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // 🔥 DELETE USER
    public function deleteUser(int $userId): array
    {
        if (!$userId) {
            return ['success' => false, 'message' => 'Invalid ID'];
        }

        $ok = $this->con->prepare("DELETE FROM users WHERE id = ?")
            ->execute([$userId]);

        return $ok
            ? ['success' => true, 'message' => 'Deleted successfully']
            : ['success' => false, 'message' => 'Delete failed'];
    }

    // 🔍 GET ALL
    public function getAllUsers(): array
    {
        return $this->con
            ->query("SELECT u.*, ui.* FROM users u 
                     LEFT JOIN users_info ui ON u.id = ui.user_id")
            ->fetchAll();
    }

    // 🔍 GET ONE
    public function getUserById(int $id)
    {
        $stmt = $this->con->prepare("
            SELECT u.*, ui.* FROM users u
            LEFT JOIN users_info ui ON u.id = ui.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    // 🔐 LOGIN SUPPORT
    public function getUserByUsername(string $username)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function updateFailedAttempts(int $userId, int $attempts): bool
    {
        return $this->con->prepare("
            UPDATE users SET failed_attempts = ?, last_attempt = NOW()
            WHERE id = ?
        ")->execute([$attempts, $userId]);
    }
    public function getPositionBadge($position)
    {
        return match ($position) {
            'Admin' => 'bg-danger',
            'Owner' => 'bg-dark',
            'Manager' => 'bg-warning text-dark',
            'Staff' => 'bg-primary',
            default => 'bg-secondary'
        };
    }

}