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

// upload func - DIPERBAIKI
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
    $ekstensi_gambar = explode('.', $nama_file);
    $ekstensi_gambar = strtolower(end($ekstensi_gambar));
    
    if (!in_array($ekstensi_gambar, $ekstensi_gambar_valid)) {
        echo "<script>
                alert('Yang anda upload bukan gambar! Format yang diizinkan: jpg, jpeg, png, gif');
                window.location.href = './';
			</script>";
        return false;
    }

    if ($ukuran_file > 5000000) { // 5MB
        echo "<script>
                alert('Ukuran gambar terlalu besar! Maksimal 5MB');
                window.location.href = './';
		</script>";
        return false;
    }

    // Buat nama file unik
    $nama_file_baru = uniqid();
    $nama_file_baru .= '.';
    $nama_file_baru .= $ekstensi_gambar;

    // FIX: Path upload yang benar - sesuaikan dengan struktur folder
    $upload_path = '../../img/' . $nama_file_baru;
    
    // Pastikan folder img ada
    if (!file_exists('../../img/')) {
        mkdir('../../img/', 0777, true);
    }

    if (move_uploaded_file($tmp_name, $upload_path)) {
        return $nama_file_baru;
    } else {
        echo "<script>
                alert('Gagal mengupload gambar!');
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