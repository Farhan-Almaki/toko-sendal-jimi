<?php
// Nonaktifkan pelaporan error untuk mencegah error tampil di PDF
error_reporting(0);

// Sertakan file autoload Dompdf
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Pastikan koneksi.php ada di dalam folder database/
require_once __DIR__ . '/../../database/koneksi.php';

// Sertakan file fungsi produk Anda yang sudah dimodifikasi
require __DIR__ . '/produk_function.php';

// Ambil parameter tanggal dari URL
$tgl_awal = $_GET['tgl'];
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : null;

// Ambil data transaksi
if (!empty($tgl_akhir)) {
    $myTransaksi = getTransaksiFilterRange($tgl_awal, $tgl_akhir);
    $periode = "Periode: " . date('d/m/Y', strtotime($tgl_awal)) . " - " . date('d/m/Y', strtotime($tgl_akhir));
} else {
    $myTransaksi = getTransaksiFilter($tgl_awal);
    $periode = "Tanggal: " . date('d/m/Y', strtotime($tgl_awal));
}

// Inisialisasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'sans-serif');
$dompdf = new Dompdf($options);

// Mulai buffer output untuk menangkap HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Toko Jimi Collection Store</title>
    <style>
        /* CSS yang akan digunakan Dompdf */
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            margin: 0.3in;
            color: #333;
        }
        
        .header-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
            text-transform: uppercase;
        }
        
        .header-subtitle {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 20px;
            color: #7f8c8d;
            font-weight: bold;
        }
        
        .table-container {
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7pt;
        }
        
        table, th, td {
            border: 1px solid #34495e;
        }
        
        th {
            background-color: #3498db;
            color: white;
            padding: 6px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
        }
        
        td {
            padding: 4px 3px;
            vertical-align: top;
            /* Untuk memastikan teks tidak terlalu panjang dan tetap dalam batas kolom */
            word-wrap: break-word; /* Memecah kata panjang */
            overflow-wrap: break-word; /* Alternatif untuk memecah kata panjang */
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 6pt;
            font-weight: bold;
            border-radius: 3px;
            color: white;
        }
        
        .badge-success {
            background-color: #27ae60;
        }
        
        .badge-warning {
            background-color: #f39c12;
            color: #2c3e50;
        }
        .badge-danger { /* Tambahkan badge untuk status Ditolak */
            background-color: #e74c3c;
            color: white;
        }
        
        .total-row {
            background-color: #ecf0f1;
            font-weight: bold;
            font-size: 8pt;
        }
        
        .summary-box {
            border: 2px solid #3498db;
            padding: 15px;
            margin-top: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .summary-box h4 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 14pt;
            color: #2c3e50;
            text-align: center;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 10pt;
        }
        
        .summary-label {
            font-weight: bold;
            color: #34495e;
        }
        
        .summary-value {
            color: #2c3e50;
            font-weight: bold;
        }
        
        .footer-info {
            text-align: center;
            margin-top: 30px;
            font-style: italic;
            font-size: 8pt;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-style: italic;
            font-size: 12pt;
        }
    </style>
</head>
<body>
    <div class="header-title">
        Laporan Keuangan Toko Jimi Collection Store
    </div>
    <div class="header-subtitle">
        <?= $periode ?>
    </div>

    <div class="table-container">
        <?php if (!empty($myTransaksi)) : ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 3%;">No</th>
                        <th style="width: 8%;">ID Transaksi</th>
                        <th style="width: 16%;">Produk</th>
                        <th style="width: 8%;">Harga</th>
                        <th style="width: 4%;">Qty</th>
                        <th style="width: 10%;">Subtotal</th>
                        <th style="width: 12%;">Pembeli</th>
                        <th style="width: 13%;">Alamat</th>
                        <th style="width: 7%;">Status</th>
                        <th style="width: 7%;">Tanggal</th>
                        <th style="width: 12%;">Metode Pembayaran</th> </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    $total = 0;
                    $total_qty = 0;
                    $transaksi_lunas = 0;
                    $transaksi_pending = 0;
                    $transaksi_ditolak = 0; // Tambah counter untuk status ditolak
                    ?>
                    
                    <?php foreach ($myTransaksi as $transaksi) : ?>
                        <tr>
                            <td class="text-center"><?= $i ?></td>
                            <td class="text-center"><?= htmlspecialchars($transaksi['transaksi_id']) ?></td>
                            <td><?= htmlspecialchars($transaksi['product_name']) ?></td>
                            <td class="text-right">Rp <?= number_format($transaksi['product_price'], 0, ',', '.') ?></td>
                            <td class="text-center"><?= $transaksi['qty'] ?></td>
                            <?php
                            $sub_total = intval($transaksi['product_price']) * intval($transaksi['qty']);
                            ?>
                            <td class="text-right">Rp <?= number_format($sub_total, 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($transaksi['fullname']) ?></td>
                            <td><?= htmlspecialchars($transaksi['transaksi_alamat']) ?></td>
                            <td class="text-center">
                                <?php if ($transaksi['status_pembayaran'] === "2") : ?>
                                    <span class="badge badge-warning">Pending</span>
                                    <?php $transaksi_pending++; ?>
                                <?php elseif ($transaksi['status_pembayaran'] === "1") : ?>
                                    <span class="badge badge-success">Diterima</span> <?php $transaksi_lunas++; ?>
                                <?php else : // Status '3' (Ditolak)
                                ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                    <?php $transaksi_ditolak++; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($transaksi['tanggal_transaksi'])) ?></td>
                            <td class="text-center">
                                <strong><?= htmlspecialchars($transaksi['metode_display'] ?? 'N/A') ?></strong><br>
                                <small><?= htmlspecialchars($transaksi['nomor_display'] ?? 'N/A') ?></small>
                                <?php if (($transaksi['atas_nama_display'] ?? 'N/A') !== 'N/A'): ?>
                                    <br><small>A.N: <?= htmlspecialchars($transaksi['atas_nama_display']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                        $i++;
                        // Hanya tambahkan ke total jika statusnya 'Diterima' (status 1)
                        if ($transaksi['status_pembayaran'] === "1") {
                            $total += intval($sub_total);
                            $total_qty += intval($transaksi['qty']);
                        }
                        ?>
                    <?php endforeach; ?>
                    
                    <tr class="total-row">
                        <td colspan="4" class="text-right">TOTAL PENDAPATAN (Diterima):</td> <td class="text-center"><?= $total_qty ?></td>
                        <td class="text-right">Rp <?= number_format($total, 0, ',', '.') ?></td>
                        <td colspan="5"></td>
                    </tr>
                </tbody>
            </table>
        <?php else : ?>
            <div class="no-data">
                Tidak ada data transaksi untuk periode yang dipilih.
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($myTransaksi)) : ?>
        <div class="summary-box">
            <h4>RINGKASAN LAPORAN</h4>
            
            <div class="summary-item">
                <span class="summary-label">Total Transaksi (Keseluruhan):</span>
                <span class="summary-value"><?= count($myTransaksi) ?> transaksi</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Transaksi Diterima:</span> <span class="summary-value"><?= $transaksi_lunas ?> transaksi</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Transaksi Pending:</span>
                <span class="summary-value"><?= $transaksi_pending ?> transaksi</span>
            </div>

            <div class="summary-item">
                <span class="summary-label">Transaksi Ditolak:</span>
                <span class="summary-value"><?= $transaksi_ditolak ?> transaksi</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Total Item Terjual (Diterima):</span> <span class="summary-value"><?= $total_qty ?> item</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Total Pendapatan (Diterima):</span> <span class="summary-value">Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Rata-rata Pendapatan per Transaksi Diterima:</span> <span class="summary-value">Rp <?= $transaksi_lunas > 0 ? number_format($total / $transaksi_lunas, 0, ',', '.') : '0' ?></span>
            </div>
            
            <?php if (count($myTransaksi) > 0) : ?>
                <div class="summary-item">
                    <span class="summary-label">Tingkat Pembayaran Berhasil:</span>
                    <span class="summary-value"><?= round(($transaksi_lunas / count($myTransaksi)) * 100, 1) ?>%</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="footer-info">
        Laporan dibuat pada: <?= date('d/m/Y H:i:s') ?><br>
        Dicetak oleh: Admin Toko Jimi Collection Store
    </div>
</body>
</html>
<?php
// Ambil konten HTML dari buffer
$html = ob_get_clean();

// Load HTML ke Dompdf
$dompdf->loadHtml($html);

// Atur ukuran kertas dan orientasi
$dompdf->setPaper('A4', 'landscape');

// Render HTML sebagai PDF
$dompdf->render();

// Generate filename dengan timestamp
$filename = "Laporan_Keuangan_" . date('Y-m-d_H-i-s') . ".pdf";

// Stream PDF ke browser
$dompdf->stream($filename, array("Attachment" => 0));
exit();
?>