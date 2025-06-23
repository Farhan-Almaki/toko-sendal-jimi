<?php
// File: laporan/produk_function.php

// Koneksi ke database (Anda bisa sesuaikan jika perlu)
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

// ==========================================================
// FUNGSI-FUNGSI DI BAWAH INI TELAH DIPERBAIKI
// ==========================================================

function getTransaksiFilter($tgl)
{
    global $conn;
    $tgl = mysqli_real_escape_string($conn, $tgl);
    
    // PERBAIKAN: Query diubah untuk mengambil data pembayaran langsung dari tabel transaksi
    $sql = "SELECT t.*, u.fullname, 
                   t.metode_pembayaran_user AS metode_display, 
                   t.nomor_pembayaran_user AS nomor_display, 
                   k.qty, k.product_id, p.product_name, p.product_price
            FROM tb_transaksi t 
            INNER JOIN tb_user u ON t.user_id = u.user_id 
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id 
            INNER JOIN tb_product p ON k.product_id = p.product_id 
            WHERE t.tanggal_transaksi = '$tgl' AND k.is_payed = '1'
            ORDER BY t.tanggal_transaksi DESC, t.transaksi_id DESC";
            
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getTransaksiFilterRange($tgl_awal, $tgl_akhir)
{
    global $conn;
    $tgl_awal = mysqli_real_escape_string($conn, $tgl_awal);
    $tgl_akhir = mysqli_real_escape_string($conn, $tgl_akhir);

    // PERBAIKAN: Query diubah untuk mengambil data pembayaran langsung dari tabel transaksi
    $sql = "SELECT t.*, u.fullname, 
                   t.metode_pembayaran_user AS metode_display, 
                   t.nomor_pembayaran_user AS nomor_display, 
                   k.qty, k.product_id, p.product_name, p.product_price
            FROM tb_transaksi t 
            INNER JOIN tb_user u ON t.user_id = u.user_id 
            INNER JOIN tb_keranjang k ON t.keranjang_grup = k.keranjang_id 
            INNER JOIN tb_product p ON k.product_id = p.product_id 
            WHERE t.tanggal_transaksi BETWEEN '$tgl_awal' AND '$tgl_akhir' AND k.is_payed = '1'
            ORDER BY t.tanggal_transaksi DESC, t.transaksi_id DESC";
            
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}


// ==========================================================
// FUNGSI LAINNYA TIDAK DIUBAH
// ==========================================================

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
    }

    $query = "UPDATE `tb_product` SET `product_name`='$product_name',`product_desc`='$product_description',`product_thumb`='$gambar',`product_stok`='$stock_product',`product_price`='$product_price' WHERE product_id = '$id'";

    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

function upload()
{
    $nama_file = $_FILES['gambar']['name'];
    $ukuran_file = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmp_name = $_FILES['gambar']['tmp_name'];

    if ($error === 4) {
        echo "<script>
                window.location.href = './?response=imgfail';
			</script>";
        return false;
    }

    $ekstensi_gambar_valid = ['jpg', 'jpeg', 'png', 'jfif'];
    $ekstensi_gambar = explode('.', $nama_file);
    $ekstensi_gambar = strtolower(end($ekstensi_gambar));
    if (!in_array($ekstensi_gambar, $ekstensi_gambar_valid)) {
        echo "<script>
                window.location.href = './?response=imgwarning';
			</script>";
        return false;
    }

    if ($ukuran_file > 1000000) {
        echo "<script>
            window.location.href = './?response=imgover';
		</script>";
        return false;
    }

    $nama_file_baru = uniqid();
    $nama_file_baru .= '.';
    $nama_file_baru .= $ekstensi_gambar;

    move_uploaded_file($tmp_name, '../../img/' . $nama_file_baru);

    return $nama_file_baru;
}

?>