<?php
class Category
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll($limit = null, $offset = null)
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            if ($offset !== null) {
                $sql .= " OFFSET ?";
            }
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            if ($offset !== null) {
                $stmt->bind_param("ii", $limit, $offset);
            } else {
                $stmt->bind_param("i", $limit);
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return $categories;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function create($name, $slug)
    {
        $stmt = $this->db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    public function update($id, $name, $slug)
    {
        $stmt = $this->db->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $slug, $id);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function getProductCount($categoryId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function search($keyword, $limit = null, $offset = null)
    {
        $keyword = "%$keyword%";
        $sql = "SELECT * FROM categories WHERE name LIKE ? ORDER BY name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            if ($offset !== null) {
                $sql .= " OFFSET ?";
            }
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            if ($offset !== null) {
                $stmt->bind_param("sii", $keyword, $limit, $offset);
            } else {
                $stmt->bind_param("si", $keyword, $limit);
            }
        } else {
            $stmt->bind_param("s", $keyword);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return $categories;
    }

    public function countSearch($keyword)
    {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM categories WHERE name LIKE ?");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function countAll()
    {
        $result = $this->db->query("SELECT COUNT(*) as total FROM categories");
        $row = $result->fetch_assoc();

        return $row['total'];
    }
}
