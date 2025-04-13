<?php
class Order
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT o.*, u.name as user_name FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $result = $this->db->query($sql);
        $orders = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        return $orders;
    }

    public function getRecent($limit = 5)
    {
        return $this->getAll($limit);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                                    FROM orders o 
                                    JOIN users u ON o.user_id = u.id 
                                    WHERE o.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getByUserId($userId, $limit = null, $offset = 0)
    {
        $sql = "SELECT o.* FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        return $orders;
    }

    public function getByUserIdAndStatus($userId, $status, $limit = null, $offset = 0)
    {
        $sql = "SELECT o.* FROM orders o WHERE o.user_id = ? AND o.status = ? ORDER BY o.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $userId, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        return $orders;
    }

    public function getOrderItems($orderId)
    {
        $stmt = $this->db->prepare("SELECT oi.*, p.name as product_name, p.image as product_image, p.slug as product_slug 
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?");
        $stmt->bind_param("i", $orderId);
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

    public function create($userId, $items, $totalPrice, $shippingAddress, $phone, $notes = '', $paymentMethod = '')
    {
        $this->db->begin_transaction();

        try {
            // Pastikan alamat pengiriman tidak kosong
            if (empty($shippingAddress) || $shippingAddress === '0') {
                throw new Exception("Alamat pengiriman tidak boleh kosong");
            }

            // Insert order
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, total_price, shipping_address, phone, notes, payment_method) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idssss", $userId, $totalPrice, $shippingAddress, $phone, $notes, $paymentMethod);
            $stmt->execute();
            $orderId = $this->db->insert_id;

            // Insert order items
            foreach ($items as $item) {
                // Pastikan discount_amount ada dan valid
                $discountAmount = 0;
                if (isset($item['discount_amount']) && is_numeric($item['discount_amount'])) {
                    $discountAmount = $item['discount_amount'];
                }

                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount_amount) 
                                           VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidd", $orderId, $item['id'], $item['quantity'], $item['price'], $discountAmount);
                $stmt->execute();

                // Update product stock
                $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['id']);
                $stmt->execute();
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function updatePaymentStatus($id, $paymentStatus)
    {
        // Memastikan status pembayaran valid
        $validStatuses = ['unpaid', 'paid', 'failed', 'refunded'];

        if (!in_array($paymentStatus, $validStatuses)) {
            // Jika tidak valid, gunakan 'unpaid' sebagai default
            $paymentStatus = 'unpaid';
        }

        $stmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $paymentStatus, $id);
        return $stmt->execute();
    }

    public function countAll()
    {
        $result = $this->db->query("SELECT COUNT(*) as total FROM orders");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function countByStatus($status)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getTotalRevenue()
    {
        $result = $this->db->query("SELECT SUM(total_price) as total FROM orders WHERE payment_status = 'paid'");
        $row = $result->fetch_assoc();
        return $row['total'] ? $row['total'] : 0;
    }

    public function getMonthlySalesData($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $sql = "SELECT 
                MONTH(created_at) as month, 
                MONTHNAME(created_at) as month_name,
                SUM(total_price) as total
                FROM orders 
                WHERE YEAR(created_at) = ? AND payment_status = 'paid'
                GROUP BY MONTH(created_at), MONTHNAME(created_at)
                ORDER BY MONTH(created_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $sales = [];
        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        foreach ($months as $index => $month) {
            $sales[$index] = [
                'month' => $month,
                'total' => 0
            ];
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $monthIndex = intval($row['month']) - 1;
                $sales[$monthIndex]['total'] = floatval($row['total']);
            }
        }

        return $sales;
    }

    public function completeOrder($id)
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'delivered' WHERE id = ? AND status = 'shipped'");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function markOrderAsCompleted($id)
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'completed' WHERE id = ? AND status = 'delivered'");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getRevenueByPeriod($startDate, $endDate)
    {
        $sql = "SELECT SUM(total_price) as total FROM orders 
                WHERE created_at BETWEEN ? AND ? AND payment_status = 'paid'";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ? $row['total'] : 0;
    }

    public function getOrderCountByPeriod($startDate, $endDate)
    {
        $sql = "SELECT COUNT(*) as total FROM orders WHERE created_at BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function countByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function countByUserIdAndStatus($userId, $status)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status = ?");
        $stmt->bind_param("is", $userId, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function countPendingByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status IN ('pending', 'processing', 'shipped')");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function addOrderItem($data)
    {
        $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount_amount) 
                                   VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "iiidi",
            $data['order_id'],
            $data['product_id'],
            $data['quantity'],
            $data['price'],
            $data['discount_amount']
        );

        return $stmt->execute();
    }
}
