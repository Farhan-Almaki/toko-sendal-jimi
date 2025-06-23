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

// ========== DEBUG SECTION ==========
error_log("=== DEBUG ADD TO CART ===");
error_log("POST Data: " . json_encode($_POST));
error_log("Session Data: " . json_encode($_SESSION));

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    error_log("ERROR: User tidak login");
    sendJsonResponse(401, 'Anda harus login untuk menambahkan ke keranjang.');
}

// Validasi method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Method bukan POST");
    sendJsonResponse(405, 'Method tidak diizinkan.');
}

// Validasi input data
if (!isset($_POST['product_id']) || !isset($_POST['qty'])) {
    error_log("ERROR: Data tidak lengkap");
    sendJsonResponse(400, 'Data tidak lengkap. Product ID dan quantity diperlukan.');
}

$product_id = (int)$_POST['product_id'];
$qty = (int)$_POST['qty'];

error_log("DEBUG: Product ID = " . $product_id);
error_log("DEBUG: Quantity = " . $qty);
error_log("DEBUG: Session user_id = " . json_encode($_SESSION['dataUser']));

if (!isset($_SESSION['dataUser']['user_id'])) {
    error_log("ERROR: User ID tidak ada di session");
    sendJsonResponse(401, 'User ID tidak ditemukan dalam sesi login.');
}

$user_id = $_SESSION['dataUser']['user_id'];
error_log("DEBUG: User ID yang akan digunakan = " . $user_id);

// Validasi input
if ($product_id <= 0) {
    error_log("ERROR: Product ID tidak valid");
    sendJsonResponse(400, 'Product ID tidak valid.');
}

if ($qty <= 0) {
    error_log("ERROR: Quantity tidak valid");
    sendJsonResponse(400, 'Kuantitas harus lebih dari 0.');
}

try {
    // Cek koneksi database dulu
    if ($conn->connect_error) {
        error_log("ERROR: Database connection failed - " . $conn->connect_error);
        sendJsonResponse(500, 'Koneksi database gagal.');
    }
    
    error_log("DEBUG: Database connection OK");
    
    // Mulai transaction - Pake OOP style
    $conn->autocommit(FALSE);
    error_log("DEBUG: Transaction started");

    // Cek produk dan stok
    $stmt_product = $conn->prepare("SELECT product_id, product_name, product_stok, product_price FROM tb_product WHERE product_id = ?");
    if (!$stmt_product) {
        error_log("ERROR: Prepare product query failed - " . $conn->error);
        throw new Exception('Error preparing product query: ' . $conn->error);
    }
    
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result = $stmt_product->get_result();
    $product = $result->fetch_assoc();
    $stmt_product->close();

    error_log("DEBUG: Product data = " . json_encode($product));

    if (!$product) {
        error_log("ERROR: Product tidak ditemukan");
        $conn->rollback();
        sendJsonResponse(404, 'Produk tidak ditemukan.');
    }

    $available_stock = $product['product_stok'];
    error_log("DEBUG: Available stock = " . $available_stock);

    if ($available_stock <= 0) {
        error_log("ERROR: Stock habis");
        $conn->rollback();
        sendJsonResponse(400, 'Produk ini sedang habis stok.');
    }

    // Cek apakah produk sudah ada di keranjang
    $stmt_check = $conn->prepare("SELECT keranjang_id, qty FROM tb_keranjang WHERE user_id = ? AND product_id = ? AND is_payed = '2'");
    if (!$stmt_check) {
        error_log("ERROR: Prepare cart check failed - " . $conn->error);
        throw new Exception('Error preparing cart check query: ' . $conn->error);
    }
    
    $stmt_check->bind_param("ii", $user_id, $product_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $existing_cart = $result->fetch_assoc();
    $stmt_check->close();

    error_log("DEBUG: Existing cart = " . json_encode($existing_cart));

    if ($existing_cart) {
        error_log("DEBUG: Updating existing cart item");
        // Update existing cart item
        $keranjang_id = $existing_cart['keranjang_id'];
        $current_qty = $existing_cart['qty'];
        $new_qty = $current_qty + $qty;

        error_log("DEBUG: New quantity = " . $new_qty);

        // Cek apakah total quantity tidak melebihi stok
        if ($new_qty > $available_stock) {
            error_log("ERROR: New quantity exceeds stock");
            $conn->rollback();
            sendJsonResponse(400, "Total kuantitas ($new_qty) melebihi stok yang tersedia ($available_stock).");
        }

        $stmt_update = $conn->prepare("UPDATE tb_keranjang SET qty = ? WHERE keranjang_id = ?");
        if (!$stmt_update) {
            error_log("ERROR: Prepare update failed - " . $conn->error);
            throw new Exception('Error preparing update query: ' . $conn->error);
        }
        
        $stmt_update->bind_param("ii", $new_qty, $keranjang_id);
        $execute_result = $stmt_update->execute();
        $affected_rows = $stmt_update->affected_rows;
        
        error_log("DEBUG: Update execute result = " . ($execute_result ? 'true' : 'false'));
        error_log("DEBUG: Update affected rows = " . $affected_rows);
        
        if ($execute_result && $affected_rows > 0) {
            $conn->commit();
            $stmt_update->close();
            error_log("SUCCESS: Cart updated successfully");
            sendJsonResponse(200, "Kuantitas produk '{$product['product_name']}' berhasil diperbarui menjadi $new_qty.");
        } else {
            $conn->rollback();
            $stmt_update->close();
            error_log("ERROR: Update failed - no rows affected");
            sendJsonResponse(400, 'Tidak ada perubahan pada keranjang.');
        }
        
    } else {
        error_log("DEBUG: Inserting new cart item");
        // Insert new cart item
        if ($qty > $available_stock) {
            error_log("ERROR: Quantity exceeds stock");
            $conn->rollback();
            sendJsonResponse(400, "Kuantitas ($qty) melebihi stok yang tersedia ($available_stock).");
        }

        $stmt_insert = $conn->prepare("INSERT INTO tb_keranjang (product_id, user_id, qty, is_payed) VALUES (?, ?, ?, '2')");
        if (!$stmt_insert) {
            error_log("ERROR: Prepare insert failed - " . $conn->error);
            throw new Exception('Error preparing insert query: ' . $conn->error);
        }
        
        $stmt_insert->bind_param("iii", $product_id, $user_id, $qty);
        $execute_result = $stmt_insert->execute();
        $affected_rows = $stmt_insert->affected_rows;
        
        error_log("DEBUG: Insert execute result = " . ($execute_result ? 'true' : 'false'));
        error_log("DEBUG: Insert affected rows = " . $affected_rows);
        error_log("DEBUG: Insert error = " . $stmt_insert->error);
        
        if ($execute_result && $affected_rows > 0) {
            $conn->commit();
            $stmt_insert->close();
            error_log("SUCCESS: Product added to cart successfully");
            sendJsonResponse(200, "Produk '{$product['product_name']}' berhasil ditambahkan ke keranjang.");
        } else {
            $conn->rollback();
            $stmt_insert->close();
            error_log("ERROR: Insert failed - no rows affected");
            sendJsonResponse(500, 'Gagal menambahkan produk ke keranjang.');
        }
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("EXCEPTION: " . $e->getMessage());
    error_log("EXCEPTION: " . $e->getTraceAsString());
    sendJsonResponse(500, 'Terjadi kesalahan sistem: ' . $e->getMessage());
}
?>