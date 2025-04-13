<?php
class Banner
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM banners ORDER BY priority DESC, created_at DESC";
        $result = $this->db->query($sql);
        $banners = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $banners[] = $row;
            }
        }

        return $banners;
    }

    public function getActive()
    {
        $sql = "SELECT * FROM banners WHERE is_active = 1 ORDER BY priority DESC, created_at DESC";
        $result = $this->db->query($sql);
        $banners = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $banners[] = $row;
            }
        }

        return $banners;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM banners WHERE id = ?");
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
        $stmt = $this->db->prepare("INSERT INTO banners (title, description, image, link, is_active, priority) 
                                    VALUES (?, ?, ?, ?, ?, ?)");

        $isActive = isset($data['is_active']) ? $data['is_active'] : 1;
        $priority = isset($data['priority']) ? $data['priority'] : 0;

        $stmt->bind_param(
            "ssssii",
            $data['title'],
            $data['description'],
            $data['image'],
            $data['link'],
            $isActive,
            $priority
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    public function update($id, $data)
    {
        $fields = [];
        $types = "";
        $values = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $types .= "s";
            $values[] = $data['title'];
        }

        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $types .= "s";
            $values[] = $data['description'];
        }

        if (isset($data['image'])) {
            $fields[] = "image = ?";
            $types .= "s";
            $values[] = $data['image'];
        }

        if (isset($data['link'])) {
            $fields[] = "link = ?";
            $types .= "s";
            $values[] = $data['link'];
        }

        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $types .= "i";
            $values[] = $data['is_active'];
        }

        if (isset($data['priority'])) {
            $fields[] = "priority = ?";
            $types .= "i";
            $values[] = $data['priority'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE banners SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function toggleStatus($id)
    {
        $banner = $this->getById($id);

        if (!$banner) {
            return false;
        }

        $newStatus = $banner['is_active'] ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE banners SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $id);

        return $stmt->execute();
    }
}
