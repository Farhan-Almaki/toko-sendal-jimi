<?php

require __DIR__ . '/../database/koneksi.php';

function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// ==========================================================
// SEMUA FUNGSI GET DI BAWAH INI TELAH DIPERBAIKI
// ==========================================================

function getAllTransaksi()
{
    global $conn;
    // PERBAIKAN: Langsung ambil data dari tabel transaksi
    $sql = "SELECT t.*, u.fullname,
                   t.metode_pembayaran_user AS metode_display,
                   t.nomor_pembayaran_user AS nomor_display,
                   k.qty, k.product_id, p.product_name, p.product_price
            FROM tb_transaksi t
            INNER JOIN tb_user u ON t.user_id = u.user_id
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id
            INNER JOIN tb_product p ON k.product_id = p.product_id
            ORDER BY t.tanggal_transaksi DESC, t.transaksi_id DESC";
    
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getTransaksiByUserId($id)
{
    global $conn;
    // PERBAIKAN: Langsung ambil data dari tabel transaksi dan hapus join ke tb_bank yang tidak perlu
    $sql = "SELECT t.*, u.fullname,
                   t.metode_pembayaran_user AS metode_display,
                   t.nomor_pembayaran_user AS nomor_display,
                   k.qty, k.product_id,
                   p.product_name, p.product_price
            FROM tb_transaksi t
            INNER JOIN tb_user u ON t.user_id = u.user_id
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id
            INNER JOIN tb_product p ON k.product_id = p.product_id
            WHERE t.user_id = '$id' AND k.is_payed = '1'
            ORDER BY t.tanggal_transaksi DESC, t.transaksi_id DESC";
    
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getTransaksiById($id)
{
    global $conn;
    // PERBAIKAN: Langsung ambil data dari tabel transaksi
    $sql = "SELECT t.*, u.fullname, u.alamat, u.contact,
                   t.metode_pembayaran_user AS metode_display,
                   t.nomor_pembayaran_user AS nomor_display,
                   k.qty, k.product_id,
                   p.product_name, p.product_price, p.product_thumb
            FROM tb_transaksi t
            INNER JOIN tb_user u ON t.user_id = u.user_id
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id
            INNER JOIN tb_product p ON k.product_id = p.product_id
            WHERE t.transaksi_id = '$id'";
    
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

function getPendingTransaksi()
{
    global $conn;
    // PERBAIKAN: Langsung ambil data dari tabel transaksi
    $sql = "SELECT t.*, u.fullname,
                   t.metode_pembayaran_user AS metode_display,
                   t.nomor_pembayaran_user AS nomor_display,
                   k.qty, k.product_id,
                   p.product_name, p.product_price
            FROM tb_transaksi t
            INNER JOIN tb_user u ON t.user_id = u.user_id
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id
            INNER JOIN tb_product p ON k.product_id = p.product_id
            WHERE t.status_pembayaran = '2' AND k.is_payed = '1'
            ORDER BY t.tanggal_transaksi ASC, t.transaksi_id ASC";
    
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getProcessedTransaksi()
{
    global $conn;
    // PERBAIKAN: Langsung ambil data dari tabel transaksi
    $sql = "SELECT t.*, u.fullname,
                   t.metode_pembayaran_user AS metode_display,
                   t.nomor_pembayaran_user AS nomor_display,
                   k.qty, k.product_id,
                   p.product_name, p.product_price
            FROM tb_transaksi t
            INNER JOIN tb_user u ON t.user_id = u.user_id
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id
            INNER JOIN tb_product p ON k.product_id = p.product_id
            WHERE t.status_pembayaran IN ('1', '3') AND k.is_payed = '1'
            ORDER BY t.tanggal_transaksi DESC, t.transaksi_id DESC";
    
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}


// ==========================================================
// FUNGSI addTransaksi DAN updateTransaksiStatus ANDA SUDAH BENAR DAN TIDAK DIUBAH
// ==========================================================

function addTransaksi($data, $myCart)
{
    global $conn;

    $user_id = $data['user_id'];
    $alamat_pembeli = htmlspecialchars($data['alamat_pembeli']);
    $contact_pembeli = htmlspecialchars($data['contact_pembeli'] ?? '');
    $tglTransaksi = date('Y-m-d');
    $metode_pembayaran_user = htmlspecialchars($data['metode_pembayaran_user']);
    $nomor_pembayaran_user = htmlspecialchars($data['nomor_pembayaran_user']);
    
    // Logika untuk bank_id ini bisa Anda simpan jika masih dibutuhkan untuk keperluan lain
    $bank_id_to_store = null;
    $bank_data = query("SELECT bank_id FROM tb_bank WHERE REPLACE(nama_bank, ' ', '') = REPLACE('$metode_pembayaran_user', 'Virtual Account ', '') LIMIT 1");
    if (!empty($bank_data)) {
        $bank_id_to_store = $bank_data[0]['bank_id'];
    }

    if (empty($myCart)) {
        return 0;
    }

    mysqli_begin_transaction($conn);

    try {
        $transaksi_ids = [];
        
        foreach ($myCart as $cartItem) {
            $keranjang_id = $cartItem['keranjang_id'];
            $product_id = $cartItem['product_id'];
            $qty = $cartItem['qty'];
            $product_price = $cartItem['product_price'];
            $total_harga = intval($product_price) * intval($qty);

            $db_product = query("SELECT * FROM tb_product WHERE product_id = '$product_id'")[0];
            $db_stok_product = intval($db_product['product_stok']);
            $newStok = $db_stok_product - $qty;

            if ($newStok < 0) {
                throw new Exception("Stok tidak mencukupi untuk produk ID: " . $product_id);
            }

            $sql_transaksi = "INSERT INTO `tb_transaksi`
                             (`user_id`, `bank_id`, `metode_pembayaran_user`, `nomor_pembayaran_user`, `keranjang_grup`, `transaksi_alamat`, `contact_pembeli`, `tanggal_transaksi`, `status_pembayaran`, `bukti_pembayaran`, `total_pembayaran`)
                             VALUES (
                                '$user_id',
                                " . ($bank_id_to_store ? "'$bank_id_to_store'" : "NULL") . ",
                                '$metode_pembayaran_user',
                                '$nomor_pembayaran_user',
                                '$keranjang_id',
                                '$alamat_pembeli',
                                '$contact_pembeli',
                                '$tglTransaksi',
                                '2',
                                '',
                                '$total_harga'
                             )";
            
            if (!mysqli_query($conn, $sql_transaksi)) {
                throw new Exception("Failed to insert transaction: " . mysqli_error($conn));
            }
            
            $transaksi_ids[] = mysqli_insert_id($conn);

            $sql_update_stock = "UPDATE `tb_product` SET `product_stok`='$newStok' WHERE product_id = '$product_id'";
            if (!mysqli_query($conn, $sql_update_stock)) {
                throw new Exception("Failed to update product stock: " . mysqli_error($conn));
            }

            $sql_update_cart_item = "UPDATE `tb_keranjang` SET `is_payed`='1' WHERE keranjang_id = '$keranjang_id'";
            if (!mysqli_query($conn, $sql_update_cart_item)) {
                throw new Exception("Failed to update cart status for item: " . mysqli_error($conn));
            }
        }
        
        mysqli_commit($conn);
        
        return $transaksi_ids[0];
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error in addTransaksi: " . $e->getMessage());
        return 0;
    }
}

function updateTransaksiStatus($transaksi_id, $status)
{
    global $conn;
    if (!in_array($status, ['1', '3'])) {
        return false;
    }

    $transaksi_id = mysqli_real_escape_string($conn, $transaksi_id);
    $status = mysqli_real_escape_string($conn, $status);

    $sql = "UPDATE tb_transaksi SET status_pembayaran = '$status' WHERE transaksi_id = '$transaksi_id'";
    
    if (mysqli_query($conn, $sql)) {
        if ($status == '3') {
            $transaksi_detail = getTransaksiById($transaksi_id);
            if ($transaksi_detail) {
                $product_id = $transaksi_detail['product_id'];
                $qty = $transaksi_detail['qty'];

                $db_product = query("SELECT product_stok FROM tb_product WHERE product_id = '$product_id'")[0];
                $current_stok = intval($db_product['product_stok']);
                $restored_stok = $current_stok + $qty;

                $sql_restore_stock = "UPDATE tb_product SET product_stok = '$restored_stok' WHERE product_id = '$product_id'";
                if (!mysqli_query($conn, $sql_restore_stock)) {
                    error_log("Failed to restore stock for product ID: " . $product_id . " on transaction ID: " . $transaksi_id);
                }
            }
        }
        return true;
    } else {
        error_log("Failed to update transaction status for ID: " . $transaksi_id . " Error: " . mysqli_error($conn));
        return false;
    }
}

?>