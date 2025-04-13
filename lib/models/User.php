<?php
class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        $users = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        return $users;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function create($data)
    {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, phone, address) 
                                    VALUES (?, ?, ?, ?, ?, ?)");

        $role = isset($data['role']) ? $data['role'] : 'customer';
        $phone = isset($data['phone']) ? $data['phone'] : null;
        $address = isset($data['address']) ? $data['address'] : null;

        $stmt->bind_param("ssssss", $data['name'], $data['email'], $passwordHash, $role, $phone, $address);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $data['name'], $data['email'], $data['phone'], $data['id']);
        return $stmt->execute();
    }

    // Fungsi untuk update password
    public function updatePassword($id, $password)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->db->query("SELECT COUNT(*) as total FROM users");
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function authenticate($email, $password)
    {
        $user = $this->getByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    // Cek apakah email sudah digunakan
    public function isEmailExists($email)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
