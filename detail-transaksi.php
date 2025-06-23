<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ./auth/login.php");
    exit();
}

require __DIR__ . '/database/koneksi.php';
require __DIR__ . '/controller/transaksiController.php';

$user_id = $_SESSION['dataUser']['user_id'];
$fullname = $_SESSION['dataUser']['fullname'];

$response = (isset($_GET['r'])) ? $_GET['r'] : null;
$alert_message = '';
$alert_type = 'info';

if ($response === "trxsuccess") {
    $alert_message = "Pesanan Anda berhasil dibuat dan sedang menunggu konfirmasi admin.";
    $alert_type = 'success';
} elseif ($response === "trxfailed") {
    $alert_message = "Terjadi kesalahan saat membuat transaksi, silakan coba lagi.";
    $alert_type = 'danger';
} elseif ($response === "trxditerima") {
    $alert_message = "Pesanan Anda telah diterima dan akan segera diproses.";
    $alert_type = 'success';
} elseif ($response === "trxditolak") {
    $alert_message = "Pesanan Anda telah ditolak oleh admin.";
    $alert_type = 'danger';
}

$myTransaksi = getTransaksiByUserId($user_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Detail Transaksi - Toko Jimi</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.css"/>
</head>

<body id="home">
    <nav class="navbar-container sticky-top">
        <div class="navbar-logo">
            <h3><a href="./">Toko Jimi</a></h3>
        </div>
        <div class="navbar-box">
            <ul class="navbar-list">
                <li><a href="./"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./shop.php"><i class="fas fa-shopping-cart"></i> Shop</a></li>
                <li><a href="./my-cart.php"><i class="fas fa-shopping-bag"></i> My Cart</a></li>
                <li><a href="./detail-transaksi.php" class="active"><i class="fas fa-receipt"></i> Transaksi</a></li>
                <li><a href="./auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="navbar-toggle">
            <span></span>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-12">
                <?php if ($alert_message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert" data-aos="fade-down">
                        <strong><?= htmlspecialchars($alert_message) ?></strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-receipt"></i> Detail Transaksi Saya</h4>
                        <small>Selamat datang, <?= htmlspecialchars($fullname) ?></small>
                    </div>
                </div>

                <?php if (empty($myTransaksi)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum Ada Transaksi</h5>
                            <p class="text-muted">Anda belum memiliki transaksi apapun.</p>
                            <a href="./shop.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Mulai Belanja
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabel-transaksi" class="table table-striped table-bordered" width="100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                            <th>Alamat</th>
                                            <th>Kontak</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Metode Pembayaran</th> </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        $total_keseluruhan = 0;
                                        ?>
                                        <?php foreach ($myTransaksi as $transaksi): ?>
                                            <?php
                                            $subtotal = intval($transaksi['product_price']) * intval($transaksi['qty']);
                                            if ($transaksi['status_pembayaran'] == '1') {
                                                $total_keseluruhan += $subtotal;
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($transaksi['product_name']) ?></strong>
                                                </td>
                                                <td>Rp <?= number_format($transaksi['product_price'], 0, ',', '.') ?></td>
                                                <td>
                                                    <span class="badge badge-secondary"><?= $transaksi['qty'] ?></span>
                                                </td>
                                                <td>
                                                    <strong>Rp <?= number_format($subtotal, 0, ',', '.') ?></strong>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($transaksi['transaksi_alamat']) ?></small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <i class="fas fa-phone"></i> 
                                                        <?= htmlspecialchars($transaksi['contact_pembeli'] ?? 'N/A') ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if ($transaksi['status_pembayaran'] == '1'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> Diterima
                                                        </span>
                                                    <?php elseif ($transaksi['status_pembayaran'] == '2'): ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock"></i> Pending
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
                                                <td>
                                                    <small><?= date('d/m/Y', strtotime($transaksi['tanggal_transaksi'])) ?></small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <strong><?= htmlspecialchars($transaksi['metode_display'] ?? 'N/A') ?></strong><br>
                                                        <?= htmlspecialchars($transaksi['nomor_display'] ?? 'N/A') ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-info-circle"></i> Informasi Transaksi</h6>
                                            <p class="card-text small mb-0">
                                                • Status 'Diterima' berarti pesanan Anda telah dikonfirmasi dan akan segera diproses.<br>
                                                • Status 'Pending' berarti pesanan Anda sedang menunggu verifikasi admin.<br>
                                                • Status 'Ditolak' berarti pesanan Anda tidak dapat diproses.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h6 class="card-title">Total Keseluruhan Transaksi Diterima</h6>
                                            <h4 class="mb-0">
                                                <strong>Rp <?= number_format($total_keseluruhan, 0, ',', '.') ?></strong>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
        $(document).ready(function() {
            $('#tabel-transaksi').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                },
                "order": [[ 8, "desc" ]],
                "pageLength": 10,
                "scrollX": true,
                "columnDefs": [
                    { "width": "5%", "targets": 0 },
                    { "width": "20%", "targets": 1 },
                    { "width": "10%", "targets": 2 },
                    { "width": "5%", "targets": 3 },
                    { "width": "12%", "targets": 4 },
                    { "width": "15%", "targets": 5 },
                    { "width": "10%", "targets": 6 },
                    { "width": "10%", "targets": 7 },
                    { "width": "8%", "targets": 8 },
                    { "width": "15%", "targets": 9 } 
                ]
            });

            AOS.init({
                duration: 800,
                once: true
            });

            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>
</body>
</html>