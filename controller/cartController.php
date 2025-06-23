<?php

require_once __DIR__ . '/../database/koneksi.php';

function getMyCart($user_id)
{
    global $conn;
    
    // Validasi user_id
    if (empty($user_id)) {
        return [];
    }
    
    $query = "SELECT tc.keranjang_id, tp.product_name, tp.product_stok, tp.product_price, tc.qty, tc.is_payed, tp.product_id
              FROM tb_keranjang tc
              JOIN tb_product tp ON tc.product_id = tp.product_id
              WHERE tc.user_id = ? AND tc.is_payed = '2'
              ORDER BY tc.keranjang_id DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparing getMyCart query: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    } else {
        error_log("Error executing getMyCart query: " . $stmt->error);
    }
    $stmt->close();
    return $rows;
}

function updateCart($data)
{
    global $conn;

    // Validasi input
    if (empty($data['cart_id']) || empty($data['qty'])) {
        return "Data tidak lengkap.";
    }

    $cart_id = (int) htmlspecialchars($data['cart_id']);
    $qty = (int) htmlspecialchars($data['qty']);

    // Validasi kuantitas
    if ($qty <= 0) {
        return "Kuantitas harus lebih dari 0.";
    }

    // Ambil product_id dari keranjang
    $stmt = $conn->prepare("SELECT product_id FROM tb_keranjang WHERE keranjang_id = ?");
    if (!$stmt) {
        return "Error preparing statement: " . $conn->error;
    }
    
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_data = $result->fetch_assoc();
    $stmt->close();

    if (!$cart_data) {
        return "Item keranjang tidak ditemukan.";
    }

    $product_id = $cart_data['product_id'];

    // Cek stok produk
    $stmt = $conn->prepare("SELECT product_stok FROM tb_product WHERE product_id = ?");
    if (!$stmt) {
        return "Error preparing stock check: " . $conn->error;
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product_data = $result->fetch_assoc();
    $stmt->close();

    if (!$product_data) {
        return "Produk tidak ditemukan.";
    }

    $product_stok = $product_data['product_stok'];

    // Validasi stok
    if ($qty > $product_stok) {
        return "Stok tidak mencukupi. Stok tersedia: " . $product_stok;
    }

    // Update keranjang
    $stmt = $conn->prepare("UPDATE tb_keranjang SET qty = ? WHERE keranjang_id = ?");
    if (!$stmt) {
        return "Error preparing update: " . $conn->error;
    }
    
    $stmt->bind_param("ii", $qty, $cart_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    return $affected_rows;
}

function deleteCart($id)
{
    global $conn;
    
    // Validasi ID
    $cart_id = (int) htmlspecialchars($id);
    if ($cart_id <= 0) {
        return 0;
    }
    
    $stmt = $conn->prepare("DELETE FROM tb_keranjang WHERE keranjang_id = ?");
    if (!$stmt) {
        error_log("Error preparing delete statement: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    return $affected_rows;
}

function isProductInStock($product_id, $qty)
{
    global $conn;
    
    $stmt = $conn->prepare("SELECT product_stok FROM tb_product WHERE product_id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        return false;
    }
    
    return $product['product_stok'] >= $qty;
}

function checkOutVisibility($myCart) 
{
    if (empty($myCart)) {
        return false;
    }
    
    foreach ($myCart as $item) {
        if (!isProductInStock($item['product_id'], $item['qty'])) {
            return false;
        }
    }
    return true;
}

?>