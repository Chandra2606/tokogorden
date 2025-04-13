<?php
class Product
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $result = $this->db->query($sql);
        $products = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                    JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id = ?");
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
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p 
                                    JOIN categories c ON p.category_id = c.id 
                                    WHERE p.slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getByCategoryId($categoryId, $limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? 
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function search($keyword, $limit = null, $offset = 0)
    {
        $keyword = "%$keyword%";
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE ? OR p.description LIKE ? 
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image, is_featured) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssdisi",
            $data['category_id'],
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image'],
            $data['is_featured']
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE products SET category_id = ?, name = ?, slug = ?, 
                                    description = ?, price = ?, stock = ?, image = ?, is_featured = ? 
                                    WHERE id = ?");
        $stmt->bind_param(
            "isssdisii",
            $data['category_id'],
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image'],
            $data['is_featured'],
            $id
        );

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->db->query("SELECT COUNT(*) as total FROM products");
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function getDiscountedProducts()
    {
        $sql = "SELECT p.*, c.name as category_name, d.value, d.is_percentage, d.type 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                JOIN product_discounts pd ON p.id = pd.product_id 
                JOIN discounts d ON pd.discount_id = d.id 
                WHERE d.active = 1 
                AND (d.start_date IS NULL OR d.start_date <= NOW()) 
                AND (d.end_date IS NULL OR d.end_date >= NOW()) 
                ORDER BY p.created_at DESC";

        $result = $this->db->query($sql);
        $products = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function getDiscountedPrice($product)
    {
        $stmt = $this->db->prepare("SELECT d.* FROM discounts d 
                                  JOIN product_discounts pd ON d.id = pd.discount_id 
                                  WHERE pd.product_id = ? AND d.active = 1 
                                  AND (d.start_date IS NULL OR d.start_date <= NOW()) 
                                  AND (d.end_date IS NULL OR d.end_date >= NOW())");
        $stmt->bind_param("i", $product['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $discountedPrice = $product['price'];

        if ($result->num_rows > 0) {
            $discount = $result->fetch_assoc();

            if ($discount['is_percentage']) {
                $discountAmount = $product['price'] * ($discount['value'] / 100);
            } else {
                $discountAmount = $discount['value'];
            }

            $discountedPrice = $product['price'] - $discountAmount;
            if ($discountedPrice < 0) $discountedPrice = 0;
        }

        return $discountedPrice;
    }

    public function getTopSelling($limit = 5)
    {
        $sql = "SELECT p.*, c.name as category_name, SUM(oi.quantity) as total_sold
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE o.status != 'cancelled' OR o.status IS NULL
                GROUP BY p.id
                ORDER BY total_sold DESC, p.created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }


    public function countSearch($keyword)
    {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products WHERE name LIKE ? OR description LIKE ?");
        $stmt->bind_param("ss", $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function countByCategoryId($categoryId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function searchInCategory($keyword, $categoryId, $limit = null, $offset = 0)
    {
        $keyword = "%$keyword%";
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND (p.name LIKE ? OR p.description LIKE ?) 
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iss", $categoryId, $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function countSearchInCategory($keyword, $categoryId)
    {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products 
                                   WHERE category_id = ? AND (name LIKE ? OR description LIKE ?)");
        $stmt->bind_param("iss", $categoryId, $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function updateStock($id, $newStock)
    {
        $stmt = $this->db->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStock, $id);

        return $stmt->execute();
    }
}
