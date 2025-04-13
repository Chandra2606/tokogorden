<?php
class Discount
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM discounts ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        $discounts = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $discounts[] = $row;
            }
        }

        return $discounts;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getByType($type)
    {
        $stmt = $this->db->prepare("SELECT * FROM discounts WHERE type = ? AND active = 1");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();

        $discounts = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $discounts[] = $row;
            }
        }

        return $discounts;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO discounts (name, type, value, is_percentage, min_qty, code, start_date, end_date, active) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $isPercentage = isset($data['is_percentage']) ? $data['is_percentage'] : 1;
        $minQty = isset($data['min_qty']) ? $data['min_qty'] : null;
        $code = isset($data['code']) ? $data['code'] : null;
        $startDate = isset($data['start_date']) ? $data['start_date'] : null;
        $endDate = isset($data['end_date']) ? $data['end_date'] : null;
        $active = isset($data['active']) ? $data['active'] : 1;

        $stmt->bind_param(
            "ssdiisssi",
            $data['name'],
            $data['type'],
            $data['value'],
            $isPercentage,
            $minQty,
            $code,
            $startDate,
            $endDate,
            $active
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE discounts SET name = ?, type = ?, value = ?, is_percentage = ?, 
                                    min_qty = ?, code = ?, start_date = ?, end_date = ?, active = ? 
                                    WHERE id = ?");

        $isPercentage = isset($data['is_percentage']) ? $data['is_percentage'] : 1;
        $minQty = isset($data['min_qty']) ? $data['min_qty'] : null;
        $code = isset($data['code']) ? $data['code'] : null;
        $startDate = isset($data['start_date']) ? $data['start_date'] : null;
        $endDate = isset($data['end_date']) ? $data['end_date'] : null;
        $active = isset($data['active']) ? $data['active'] : 1;

        $stmt->bind_param(
            "ssdiisssi",
            $data['name'],
            $data['type'],
            $data['value'],
            $isPercentage,
            $minQty,
            $code,
            $startDate,
            $endDate,
            $active,
            $data['id']
        );

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM discounts WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function assignToProduct($discountId, $productId)
    {
        $stmt = $this->db->prepare("INSERT INTO product_discounts (product_id, discount_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $productId, $discountId);

        return $stmt->execute();
    }

    public function removeFromProduct($discountId, $productId)
    {
        $stmt = $this->db->prepare("DELETE FROM product_discounts WHERE product_id = ? AND discount_id = ?");
        $stmt->bind_param("ii", $productId, $discountId);

        return $stmt->execute();
    }

    public function getProductDiscount($productId, $quantity = 1)
    {
        $sql = "SELECT d.* FROM discounts d 
                JOIN product_discounts pd ON d.id = pd.discount_id 
                WHERE pd.product_id = ? AND d.active = 1
                AND (d.start_date IS NULL OR d.start_date <= NOW()) 
                AND (d.end_date IS NULL OR d.end_date >= NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        $maxDiscount = null;
        $maxDiscountAmount = 0;

        if ($result->num_rows > 0) {
            while ($discount = $result->fetch_assoc()) {
                if ($discount['type'] == 'bundle' && $quantity < $discount['min_qty']) {
                    continue;
                }

                $discountAmount = $discount['is_percentage'] ? $discount['value'] : 0;

                if ($maxDiscount === null || $discountAmount > $maxDiscountAmount) {
                    $maxDiscount = $discount;
                    $maxDiscountAmount = $discountAmount;
                }
            }
        }

        return $maxDiscount;
    }

    public function validateVoucherCode($code)
    {
        $stmt = $this->db->prepare("SELECT * FROM discounts 
                                   WHERE type = 'voucher' AND code = ? AND active = 1
                                   AND (start_date IS NULL OR start_date <= NOW()) 
                                   AND (end_date IS NULL OR end_date >= NOW())");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function getActiveTimeDiscounts()
    {
        $sql = "SELECT * FROM discounts 
                WHERE type = 'time' AND active = 1
                AND start_date <= NOW() AND end_date >= NOW()";

        $result = $this->db->query($sql);
        $discounts = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $discounts[] = $row;
            }
        }

        return $discounts;
    }

    public function getAssignedProducts($discountId)
    {
        $sql = "SELECT p.* FROM products p 
                JOIN product_discounts pd ON p.id = pd.product_id 
                WHERE pd.discount_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $discountId);
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

    public function getAssignedProductIds($discountId)
    {
        $sql = "SELECT product_id FROM product_discounts WHERE discount_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $discountId);
        $stmt->execute();
        $result = $stmt->get_result();

        $productIds = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productIds[] = $row['product_id'];
            }
        }

        return $productIds;
    }

    public function removeAllProductAssignments($discountId)
    {
        $stmt = $this->db->prepare("DELETE FROM product_discounts WHERE discount_id = ?");
        $stmt->bind_param("i", $discountId);

        return $stmt->execute();
    }

    public function calculateDiscount($product, $quantity = 1, $voucherCode = null)
    {
        $price = $product['price'];
        $discountAmount = 0;

        $productDiscount = $this->getProductDiscount($product['id'], $quantity);
        $voucherDiscount = null;

        if ($voucherCode !== null) {
            $voucherDiscount = $this->validateVoucherCode($voucherCode);
        }

        $timeDiscounts = $this->getActiveTimeDiscounts();

        if ($productDiscount !== null) {
            if ($productDiscount['is_percentage']) {
                $discountAmount = $price * ($productDiscount['value'] / 100);
            } else {
                $discountAmount = $productDiscount['value'];
            }
        }

        if ($voucherDiscount !== null) {
            $voucherDiscountAmount = 0;

            if ($voucherDiscount['is_percentage']) {
                $voucherDiscountAmount = $price * ($voucherDiscount['value'] / 100);
            } else {
                $voucherDiscountAmount = $voucherDiscount['value'];
            }

            if ($voucherDiscountAmount > $discountAmount) {
                $discountAmount = $voucherDiscountAmount;
            }
        }

        foreach ($timeDiscounts as $timeDiscount) {
            $timeDiscountAmount = 0;

            if ($timeDiscount['is_percentage']) {
                $timeDiscountAmount = $price * ($timeDiscount['value'] / 100);
            } else {
                $timeDiscountAmount = $timeDiscount['value'];
            }

            if ($timeDiscountAmount > $discountAmount) {
                $discountAmount = $timeDiscountAmount;
            }
        }

        if ($discountAmount > $price) {
            $discountAmount = $price;
        }

        return $discountAmount;
    }
}
