<?php
class Address
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $addresses = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $addresses[] = $row;
            }
        }

        return $addresses;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function create($data)
    {
        // Jika ini adalah alamat default, reset alamat default lainnya
        if (isset($data['is_default']) && $data['is_default']) {
            $this->resetDefaultAddress($data['user_id']);
        }

        $stmt = $this->db->prepare("INSERT INTO addresses (user_id, recipient_name, phone, province, city, district, postal_code, full_address, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issssssis",
            $data['user_id'],
            $data['recipient_name'],
            $data['phone'],
            $data['province'],
            $data['city'],
            $data['district'],
            $data['postal_code'],
            $data['full_address'],
            $data['is_default']
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    public function update($data)
    {
        // Jika ini adalah alamat default, reset alamat default lainnya
        if (isset($data['is_default']) && $data['is_default']) {
            $this->resetDefaultAddress($data['user_id']);
        }

        $stmt = $this->db->prepare("UPDATE addresses SET recipient_name = ?, phone = ?, province = ?, city = ?, district = ?, postal_code = ?, full_address = ?, is_default = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param(
            "sssssssiii",
            $data['recipient_name'],
            $data['phone'],
            $data['province'],
            $data['city'],
            $data['district'],
            $data['postal_code'],
            $data['full_address'],
            $data['is_default'],
            $data['id'],
            $data['user_id']
        );

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function delete($id, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function getDefaultAddress($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        // Jika tidak ada default, ambil alamat pertama
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function setAsDefault($id, $userId)
    {
        // Reset semua alamat menjadi non-default
        $this->resetDefaultAddress($userId);

        // Set alamat terpilih sebagai default
        $stmt = $this->db->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    private function resetDefaultAddress($userId)
    {
        $stmt = $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }

    public function countByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM addresses WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }
}
