<?php

require_once '../../database/koneksi.php';

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

function tambah($data)
{
    global $conn;
    $product_name = htmlspecialchars($data["nama_produk"]);
    $product_price = htmlspecialchars($data["harga_produk"]);
    $product_description = htmlspecialchars($data["desc_produk"]);
    $stock_product = htmlspecialchars($data["stok_produk"]);

    $product_image = upload();
    if (!$product_image) {
        return false;
    }

    $query = "INSERT INTO `tb_product` VALUES ('','$product_name','$product_description','$product_image','$stock_product','$product_price')";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function update($data)
{
    global $conn;
    $id = $data["id"];
    $product_name = htmlspecialchars($data["nama_produk"]);
    $product_price = htmlspecialchars($data["harga_produk"]);
    $product_description = htmlspecialchars($data["desc_produk"]);
    $stock_product = htmlspecialchars($data["stok_produk"]);
    $gambar_lama = htmlspecialchars($data["gambar_lama"]);

    if ($_FILES['gambar']['error'] === 4) {
        $gambar = $gambar_lama;
    } else {
        $gambar = upload();
        if (!$gambar) {
            return false;
        }
    }

    $query = "UPDATE `tb_product` SET `product_name`='$product_name',`product_desc`='$product_description',`product_thumb`='$gambar',`product_stok`='$stock_product',`product_price`='$product_price' WHERE product_id = '$id'";

    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

function delete($id) {
    global $conn;
    $query = "DELETE FROM tb_product WHERE product_id = '$id'";
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

// UPLOAD FUNCTION - KHUSUS UNTUK WINDOWS/XAMPP
function upload()
{
    $nama_file = $_FILES['gambar']['name'];
    $ukuran_file = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmp_name = $_FILES['gambar']['tmp_name'];

    if ($error === 4) {
        echo "<script>
                alert('Pilih gambar terlebih dahulu!');
                window.location.href = './';
            </script>";
        return false;
    }

    $ekstensi_gambar_valid = ['jpg', 'jpeg', 'png', 'gif'];
    $ekstensi_gambar = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    
    if (!in_array($ekstensi_gambar, $ekstensi_gambar_valid)) {
        echo "<script>
                alert('Yang anda upload bukan gambar! Format yang diizinkan: jpg, jpeg, png, gif');
                window.location.href = './';
            </script>";
        return false;
    }

    if ($ukuran_file > 5000000) {
        echo "<script>
                alert('Ukuran gambar terlalu besar! Maksimal 5MB');
                window.location.href = './';
            </script>";
        return false;
    }

    // Buat nama file unik
    $nama_file_baru = uniqid() . '.' . $ekstensi_gambar;

    // SOLUSI UNTUK WINDOWS - gunakan path absolut
    $document_root = $_SERVER['DOCUMENT_ROOT'];
    $project_folder = '/toko-sendal-jimi-main'; // sesuaikan dengan nama folder project Anda
    $target_dir = $document_root . $project_folder . '/assets/img/';
    
    // Alternatif jika path di atas tidak work, gunakan path relatif sederhana
    // $target_dir = '../../assets/img/';
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            // Jika gagal buat di assets, gunakan folder lokal
            $target_dir = './uploads/';
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
        }
    }
    
    $upload_path = $target_dir . $nama_file_baru;

    // Debug - uncomment untuk troubleshooting jika masih error
    // echo "Target dir: " . $target_dir . "<br>";
    // echo "Upload path: " . $upload_path . "<br>";
    // echo "File exists: " . (file_exists($target_dir) ? 'Yes' : 'No') . "<br>";
    // echo "Is writable: " . (is_writable($target_dir) ? 'Yes' : 'No') . "<br>";
    // die();

    if (move_uploaded_file($tmp_name, $upload_path)) {
        return $nama_file_baru;
    } else {
        echo "<script>
                alert('Gagal mengupload gambar! Path: $upload_path');
                window.location.href = './';
            </script>";
        return false;
    }
}

function getProductStock($product_id) {
    global $conn;
    $query = "SELECT product_stok FROM tb_product WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($product_stok);
    $stmt->fetch();
    $stmt->close();
    return $product_stok;
}