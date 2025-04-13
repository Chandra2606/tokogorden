<?php
class Review
{
    private $conn;
    private $table = 'reviews';

    public function __construct($db)
    {
        $this->conn = $db;
    }


    public function getProductReviews($productId, $limit = 10, $offset = 0)
    {
        $query = "SELECT r.*, u.name as user_name 
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = ?
                  ORDER BY r.created_at DESC
                  LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $productId, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        return $reviews;
    }


    public function getTotalProductReviews($productId)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function getAverageRating($productId)
    {
        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table . " WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    }


    public function hasUserReviewed($userId, $productId)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    public function canUserReviewProduct($userId, $productId)
    {
        $query = "SELECT COUNT(*) as count FROM orders o 
                  JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.user_id = ? 
                  AND oi.product_id = ? 
                  AND o.status = 'completed'";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    public function create($data)
    {
        if ($this->hasUserReviewed($data['user_id'], $data['product_id'])) {
            return ['success' => false, 'message' => 'Anda sudah memberikan ulasan untuk produk ini'];
        }
        if ($data['order_id'] > 0 && !$this->canUserReviewProduct($data['user_id'], $data['product_id'])) {
            return ['success' => false, 'message' => 'Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli'];
        }

        $query = "INSERT INTO " . $this->table . " (user_id, product_id, order_id, rating, review, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiiis", $data['user_id'], $data['product_id'], $data['order_id'], $data['rating'], $data['review']);

        if ($stmt->execute()) {
            $this->updateProductRating($data['product_id']);
            return ['success' => true, 'id' => $this->conn->insert_id];
        }

        return ['success' => false, 'message' => 'Gagal menambahkan ulasan: ' . $stmt->error];
    }


    private function updateProductRating($productId)
    {
        $avgRating = $this->getAverageRating($productId);
        $query = "UPDATE products SET rating = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("di", $avgRating, $productId);
        $stmt->execute();
    }


    public function getReviewableProducts($userId, $orderId = null)
    {
        $orderCondition = "";
        if ($orderId) {
            $orderCondition = " AND o.id = " . intval($orderId);
        }

        $query = "SELECT DISTINCT p.id, p.name, p.image, p.slug, oi.order_id,
                  (SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = p.id) as has_reviewed
                  FROM products p
                  JOIN order_items oi ON p.id = oi.product_id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE o.user_id = ? AND o.status = 'completed'" . $orderCondition;

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }


    public function getAll($limit = 10, $offset = 0)
    {
        $query = "SELECT r.*, p.name as product_name, p.slug as product_slug, u.name as user_name 
                  FROM " . $this->table . " r
                  JOIN products p ON r.product_id = p.id
                  JOIN users u ON r.user_id = u.id
                  ORDER BY r.created_at DESC
                  LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        return $reviews;
    }
}
