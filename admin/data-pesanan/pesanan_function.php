<?php
// admin/data-pesanan/pesanan_function.php

require_once '../../database/koneksi.php'; 
require __DIR__ . '/../../controller/transaksiController.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $transaksi_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'terima') {
        if (updateTransaksiStatus($transaksi_id, '1')) {
            echo "<script>alert('Pesanan berhasil diterima!'); window.location.href = './index.php?r=trxditerima';</script>";
        } else {
            echo "<script>alert('Gagal menerima pesanan.'); window.location.href = './index.php?r=trxfailed';</script>";
        }
    } elseif ($action === 'tolak') {
        if (updateTransaksiStatus($transaksi_id, '3')) {
            echo "<script>alert('Pesanan berhasil ditolak! Stok produk telah dikembalikan.'); window.location.href = './index.php?r=trxditolak';</script>";
        } else {
            echo "<script>alert('Gagal menolak pesanan.'); window.location.href = './index.php?r=trxfailed';</script>";
        }
    } else {
        echo "<script>alert('Aksi tidak valid.'); window.location.href = './index.php';</script>";
    }
    exit;
}
?>