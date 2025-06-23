<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['dataUser']['role'] !== '1') {
    header("Location: ../../auth/login.php");
    exit();
}

require __DIR__ . '/../../database/koneksi.php';
require __DIR__ . '/../../controller/transaksiController.php';

$response = (isset($_GET['r'])) ? $_GET['r'] : null;
$alert_message = '';
$alert_type = 'info';

if ($response === "trxditerima") {
    $alert_message = "Pesanan berhasil diterima!";
    $alert_type = 'success';
} elseif ($response === "trxditolak") {
    $alert_message = "Pesanan berhasil ditolak dan stok produk telah dikembalikan.";
    $alert_type = 'danger';
} elseif ($response === "trxfailed") {
    $alert_message = "Aksi gagal, silakan coba lagi.";
    $alert_type = 'danger';
}

$pendingTransaksi = getPendingTransaksi();
$processedTransaksi = getProcessedTransaksi();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <title>Manajemen Pesanan | Admin Dashboard</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.css"/>

    <style>
        body {
            font-family: 'Poppins', sans-serif; /* Mengganti font utama */
            background-color: #f4f7fa;
        }
        
        /* Mengatur layout utama dengan flexbox */
        .uwucontainer {
            display: flex;
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }

        /* Menyembunyikan header lama */
        .header {
            display: none;
        }

        /* Gaya Sidebar Baru */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #34495e;
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar-header .site-title i {
            margin-right: 10px;
            color: #4A90E2;
        }

        .sidebar ul {
            list-style: none;
            padding: 1.5rem 0;
            margin: 0;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #ecf0f1;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 500;
            border-left: 4px solid transparent;
        }

        .sidebar ul li a i {
            margin-right: 15px;
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #4A90E2;
            color: #fff;
            border-left: 4px solid #F5A623;
        }
        
        /* Penyesuaian Main Content */
        .main {
            flex-grow: 1; /* Memastikan konten utama mengisi sisa ruang */
            display: flex;
            flex-direction: column;
            width: calc(100% - 250px); /* Kalkulasi lebar konten */
        }
        
        .page-content {
            padding: 2rem;
            overflow-y: auto; /* Tambah scroll jika kontennya panjang */
        }

        /* Penyesuaian judul halaman */
        .page-content h1 {
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
        }

    </style>
</head>

<body>
    <div class="uwucontainer">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="site-title"><i class="fa-solid fa-store"></i> Toko Jimi</span>
            </div>
            <nav>
                <ul>
                    <li><a href="../"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li><a href="../data-produk.php"><i class="fas fa-box-archive"></i><span>Data Produk</span></a></li>
                    <li><a href="./" class="active"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
                    <li><a href="../data-laporan.php"><i class="fas fa-file-alt"></i><span>Laporan</span></a></li>
                    <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <div class="main">
            <div class="page-content">
                <h1>Manajemen Pesanan</h1>

                <?php if ($alert_message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                        <strong><?= htmlspecialchars($alert_message) ?></strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Pesanan Pending</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tabel-pending-pesanan" class="table table-striped table-bordered" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>ID</th>
                                        <th>Produk</th>
                                        <th>Pembeli</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Alamat</th>
                                        <th>Kontak</th>
                                        <th>Metode Pembayaran</th> <th>Nomor Pembayaran</th> <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pendingTransaksi)): ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($pendingTransaksi as $transaksi): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($no++) ?></td>
                                                <td><?= htmlspecialchars($transaksi['transaksi_id']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['product_name']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['fullname']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['qty']) ?></td>
                                                <td>Rp <?= number_format($transaksi['total_pembayaran'], 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($transaksi['transaksi_alamat']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['contact_pembeli']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($transaksi['metode_display'] ?? 'N/A') ?></strong><br>
                                                    <?php if (($transaksi['atas_nama_display'] ?? 'N/A') !== 'N/A'): ?>
                                                        <em>A.N: <?= htmlspecialchars($transaksi['atas_nama_display']) ?></em>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($transaksi['nomor_display'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($transaksi['tanggal_transaksi']))) ?></td>
                                                <td>
                                                    <a href="pesanan_function.php?action=terima&id=<?= htmlspecialchars($transaksi['transaksi_id']) ?>" class="btn btn-success btn-sm confirm-action" data-action="terima" title="Terima Pesanan">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="pesanan_function.php?action=tolak&id=<?= htmlspecialchars($transaksi['transaksi_id']) ?>" class="btn btn-danger btn-sm confirm-action" data-action="tolak" title="Tolak Pesanan">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="12" class="text-center">Tidak ada pesanan yang sedang menunggu konfirmasi.</td> </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-5">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Pesanan (Diterima & Ditolak)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tabel-riwayat-pesanan" class="table table-striped table-bordered" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>ID</th>
                                        <th>Produk</th>
                                        <th>Pembeli</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Alamat</th>
                                        <th>Kontak</th>
                                        <th>Metode Pembayaran</th> <th>Nomor Pembayaran</th> <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($processedTransaksi)): ?>
                                        <?php $no = 1; ?>
                                        <?php foreach ($processedTransaksi as $transaksi): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($no++) ?></td>
                                                <td><?= htmlspecialchars($transaksi['transaksi_id']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['product_name']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['fullname']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['qty']) ?></td>
                                                <td>Rp <?= number_format($transaksi['total_pembayaran'], 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($transaksi['transaksi_alamat']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['contact_pembeli']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($transaksi['metode_display'] ?? 'N/A') ?></strong><br>
                                                    <?php if (($transaksi['atas_nama_display'] ?? 'N/A') !== 'N/A'): ?>
                                                        <em>A.N: <?= htmlspecialchars($transaksi['atas_nama_display']) ?></em>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($transaksi['nomor_display'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if ($transaksi['status_pembayaran'] == '1'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> Diterima
                                                        </span>
                                                    <?php elseif ($transaksi['status_pembayaran'] == '3'): ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times-circle"></i> Ditolak
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-question-circle"></i> Unknown
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($transaksi['tanggal_transaksi']))) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="12" class="text-center">Belum ada pesanan yang diproses (Diterima/Ditolak).</td> </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.js"></script>
    
    <script src="../main.js"></script>

    <script>
        $(document).ready(function() {
            // Konfigurasi DataTables untuk tabel Pending
            $('#tabel-pending-pesanan').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                },
                "order": [[ 10, "asc" ]], // Urutan kolom berubah, sesuaikan indeksnya
                "pageLength": 10
            });

            // Konfigurasi DataTables untuk tabel Riwayat
            $('#tabel-riwayat-pesanan').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                },
                "order": [[ 11, "desc" ]], // Urutan kolom berubah, sesuaikan indeksnya
                "pageLength": 10
            });


            $('.confirm-action').on('click', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const action = $(this).data('action');
                let title = '';
                let text = '';
                let icon = '';

                if (action === 'terima') {
                    title = 'Konfirmasi Terima Pesanan?';
                    text = 'Pesanan ini akan ditandai sebagai "Diterima".';
                    icon = 'success';
                } else if (action === 'tolak') {
                    title = 'Konfirmasi Tolak Pesanan?';
                    text = 'Pesanan ini akan ditandai sebagai "Ditolak" dan stok produk akan dikembalikan.';
                    icon = 'warning';
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.value) {
                        window.location.href = url;
                    }
                });
            });

            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>
</body>
</html>