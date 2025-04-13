<?php
class Wishlist
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getByUserId($userId)
    {
        $sql = "SELECT w.*, p.name as product_name, p.price, p.image, p.slug, p.stock 
                FROM wishlists w 
                JOIN products p ON w.product_id = p.id 
                WHERE w.user_id = ? 
                ORDER BY w.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }

        return $items;
    }

    public function add($userId, $productId)
    {
        if ($this->isProductInWishlist($userId, $productId)) {
            return true;
        }

        $stmt = $this->db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $productId);

        return $stmt->execute();
    }

    public function remove($userId, $productId)
    {
        $stmt = $this->db->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);

        return $stmt->execute();
    }

    public function isProductInWishlist($userId, $productId)
    {
        $stmt = $this->db->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    public function countByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM wishlists WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function getCountByUserId($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM wishlists WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function clearAll($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM wishlists WHERE user_id = ?");
        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }

    public function getPopularProducts($limit = 5)
    {
        $sql = "SELECT p.*, COUNT(w.id) as wishlist_count, c.name as category_name 
                FROM products p 
                JOIN wishlists w ON p.id = w.product_id 
                JOIN categories c ON p.category_id = c.id 
                GROUP BY p.id 
                ORDER BY wishlist_count DESC 
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
}
