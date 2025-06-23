<?php
session_start();

// Set header untuk JSON response
header('Content-Type: application/json');

require './database/koneksi.php';

// Fungsi untuk mengirim response JSON
function sendJsonResponse($statusCode, $message, $data = null) {
    $response = [
        'statusCode' => $statusCode,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    sendJsonResponse(401, 'Anda harus login untuk menambahkan ke keranjang.');
}

// Validasi method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, 'Method tidak diizinkan.');
}

// Validasi input data
if (!isset($_POST['product_id']) || !isset($_POST['qty'])) {
    sendJsonResponse(400, 'Data tidak lengkap. Product ID dan quantity diperlukan.');
}

$product_id = (int)$_POST['product_id'];
$qty = (int)$_POST['qty'];
error_log("DEBUG: Session user_id = " . json_encode($_SESSION['dataUser']));
$user_id = $_SESSION['dataUser']['user_id'];

// Validasi input
if ($product_id <= 0) {
    sendJsonResponse(400, 'Product ID tidak valid.');
}

if ($qty <= 0) {
    sendJsonResponse(400, 'Kuantitas harus lebih dari 0.');
}

try {
    // Mulai transaction
    $conn->begin_transaction();

    // Cek produk dan stok
    $stmt_product = $conn->prepare("SELECT product_id, product_name, product_stok, product_price FROM tb_product WHERE product_id = ?");
    if (!$stmt_product) {
        throw new Exception('Error preparing product query: ' . $conn->error);
    }
    
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result = $stmt_product->get_result();
    $product = $result->fetch_assoc();
    $stmt_product->close();

    if (!$product) {
        $conn->rollback();
        sendJsonResponse(404, 'Produk tidak ditemukan.');
    }

    $available_stock = $product['product_stok'];

    if ($available_stock <= 0) {
        $conn->rollback();
        sendJsonResponse(400, 'Produk ini sedang habis stok.');
    }

    // Cek apakah produk sudah ada di keranjang
    $stmt_check = $conn->prepare("SELECT keranjang_id, qty FROM tb_keranjang WHERE user_id = ? AND product_id = ? AND is_payed = '2'");
    if (!$stmt_check) {
        throw new Exception('Error preparing cart check query: ' . $conn->error);
    }
    
    $stmt_check->bind_param("ii", $user_id, $product_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $existing_cart = $result->fetch_assoc();
    $stmt_check->close();

    if ($existing_cart) {
        // Update existing cart item
        $keranjang_id = $existing_cart['keranjang_id'];
        $current_qty = $existing_cart['qty'];
        $new_qty = $current_qty + $qty;

        // Cek apakah total quantity tidak melebihi stok
        if ($new_qty > $available_stock) {
            $conn->rollback();
            sendJsonResponse(400, "Total kuantitas ($new_qty) melebihi stok yang tersedia ($available_stock).");
        }

        $stmt_update = $conn->prepare("UPDATE tb_keranjang SET qty = ? WHERE keranjang_id = ?");
        if (!$stmt_update) {
            throw new Exception('Error preparing update query: ' . $conn->error);
        }
        
        $stmt_update->bind_param("ii", $new_qty, $keranjang_id);
        $stmt_update->execute();
        
        if ($stmt_update->affected_rows > 0) {
            $conn->commit();
            $stmt_update->close();
            sendJsonResponse(200, "Kuantitas produk '{$product['product_name']}' berhasil diperbarui menjadi $new_qty.");
        } else {
            $conn->rollback();
            $stmt_update->close();
            sendJsonResponse(400, 'Tidak ada perubahan pada keranjang.');
        }
        
    } else {
        // Insert new cart item
        if ($qty > $available_stock) {
            $conn->rollback();
            sendJsonResponse(400, "Kuantitas ($qty) melebihi stok yang tersedia ($available_stock).");
        }

        $stmt_insert = $conn->prepare("INSERT INTO tb_keranjang (product_id, user_id, qty, is_payed) VALUES (?, ?, ?, '2')");
        if (!$stmt_insert) {
            throw new Exception('Error preparing insert query: ' . $conn->error);
        }
        
        $stmt_insert->bind_param("iii", $product_id, $user_id, $qty);
        $stmt_insert->execute();
        
        if ($stmt_insert->affected_rows > 0) {
            $conn->commit();
            $stmt_insert->close();
            sendJsonResponse(200, "Produk '{$product['product_name']}' berhasil ditambahkan ke keranjang.");
        } else {
            $conn->rollback();
            $stmt_insert->close();
            sendJsonResponse(500, 'Gagal menambahkan produk ke keranjang.');
        }
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Add to cart error: " . $e->getMessage());
    sendJsonResponse(500, 'Terjadi kesalahan sistem. Silakan coba lagi.');
}
?>