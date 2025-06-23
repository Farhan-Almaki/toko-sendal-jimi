<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: ../');
    exit;
}
require __DIR__ . '/produk_function.php';

$id = (isset($_GET['id'])) ? $_GET['id'] : null;
$respon = (isset($_GET['response'])) ? $_GET['response'] : null;
$hapus = (isset($_GET['hapus'])) ? $_GET['hapus'] : null;
$modal = (isset($_GET['modal'])) ? $_GET['modal'] : null;

$myTransaksi = [];
$error_message = null;

if (isset($_POST['tampilkan'])) {
    $tgl_awal = $_POST['tgl_awal'];
    $tgl_akhir = $_POST['tgl_akhir'];

    if (!empty($tgl_awal) && !empty($tgl_akhir)) {
        if ($tgl_awal <= $tgl_akhir) {
            $myTransaksi = getTransaksiFilterRange($tgl_awal, $tgl_akhir);
        } else {
            $error_message = "Tanggal awal tidak boleh lebih besar dari tanggal akhir!";
        }
    } elseif (!empty($tgl_awal) && empty($tgl_akhir)) {
        $myTransaksi = getTransaksiFilter($tgl_awal);
    } else {
        $error_message = "Silakan isi minimal tanggal awal!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Vadodara:wght@300;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.css" />
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fa; }
        .sidebar { width: 250px; background-color: #2c3e50; color: #ecf0f1; position: fixed; height: 100vh; top: 0; left: 0; z-index: 1000; display: flex; flex-direction: column; }
        .main { margin-left: 250px; width: calc(100% - 250px); }
        .uwucontainer { padding: 0; margin: 0; }
        .sidebar-header { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid #34495e; color: #fff; font-size: 1.5rem; font-weight: 600; font-family: 'Poppins', sans-serif; }
        .sidebar-header .site-title i { margin-right: 10px; color: #4A90E2; }
        .sidebar nav ul { list-style: none; padding: 1.5rem 0; margin: 0; }
        .sidebar nav li a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #ecf0f1; text-decoration: none; transition: background-color 0.3s, color 0.3s; font-weight: 500; font-family: 'Poppins', sans-serif; border-left: 4px solid transparent; }
        .sidebar nav li a i { margin-right: 15px; font-size: 1.2rem; width: 25px; text-align: center; }
        .sidebar nav li a span { font-size: 1rem; }
        .sidebar nav li a:hover,
        .sidebar nav li a.active { background-color: #4A90E2; color: #fff; border-left: 4px solid #F5A623; }
        .header { background-color: #4A90E2; color: white; padding: 0.75rem 1.5rem; display: flex; align-items: center; flex-shrink: 0; }
        .header h1 { margin: 0; font-size: 1.25rem; font-family: 'Poppins', sans-serif; font-weight: 600; }
        .header .header-logo,
        .header .header-search { display: none; }
        .page-content { padding: 2rem; }
    </style>
    <title>Toko Jimi Collection Store | Admin Dashboard</title>
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
                    <li><a href="../data-produk"><i class="fas fa-box-archive"></i><span>Data Produk</span></a></li>
                    <li><a href="../data-pesanan"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
                    <li><a href="./" class="active"><i class="fas fa-file-alt"></i><span>Laporan</span></a></li>
                    <li><a href="../../auth/logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <div class="main">
            <div class="header">
                <h1>Laporan Transaksi</h1>
            </div>

            <div class="page-content">
                <?php if ($respon) : ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                         <?= htmlspecialchars($respon) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><?= htmlspecialchars($error_message) ?></strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Laporan Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="tgl_awal" class="form-label">Tanggal Awal:</label>
                                    <input type="date" name="tgl_awal" id="tgl_awal" class="form-control" 
                                        value="<?= isset($tgl_awal) ? htmlspecialchars($tgl_awal) : '' ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="tgl_akhir" class="form-label">Tanggal Akhir:</label>
                                    <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control" 
                                        value="<?= isset($tgl_akhir) ? htmlspecialchars($tgl_akhir) : '' ?>">
                                    <small class="form-text text-muted">*Kosongkan jika ingin filter satu tanggal saja</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="visibility: hidden;">Aksi</label>
                                    <div>
                                        <input type="submit" name="tampilkan" id="tampilkan" class="btn btn-success" value="Tampilkan Laporan">
                                        <a href="./" class="btn btn-secondary">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (isset($_POST['tampilkan']) && !isset($error_message) && !empty($myTransaksi)) : ?>
                    <div class="card shadow-sm mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                             <?php 
                                $periode = !empty($tgl_akhir) ? "Periode: " . date('d M Y', strtotime($tgl_awal)) . " - " . date('d M Y', strtotime($tgl_akhir)) : "Laporan Tanggal: " . date('d M Y', strtotime($tgl_awal));
                            ?>
                            <h5 class="mb-0"><?= $periode ?></h5>
                            <a href="./cetak.php?tgl=<?= htmlspecialchars($tgl_awal) ?><?= !empty($tgl_akhir) ? '&tgl_akhir=' . htmlspecialchars($tgl_akhir) : '' ?>" 
                               class="btn btn-danger" target=""> <i class="fas fa-file-pdf"></i> Export ke PDF
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabel-data" class="table table-striped table-bordered text-center" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>ID</th>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Sub Total</th>
                                            <th>Pembeli</th>
                                            <th>Alamat</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Metode Pembayaran</th> </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = 0; 
                                        $no = 1; 
                                        foreach ($myTransaksi as $transaksi) : 
                                            $sub_total = intval($transaksi['product_price']) * intval($transaksi['qty']);
                                            if ($transaksi['status_pembayaran'] === "1") {
                                                $total += $sub_total;
                                            }
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($no++) ?></td>
                                                <td><?= htmlspecialchars($transaksi['transaksi_id']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['product_name']) ?></td>
                                                <td class="text-right">Rp.<?= number_format($transaksi['product_price'], 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($transaksi['qty']) ?></td>
                                                <td class="text-right">Rp.<?= number_format($sub_total, 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($transaksi['fullname']) ?></td>
                                                <td><?= htmlspecialchars($transaksi['transaksi_alamat']) ?></td>
                                                <td>
                                                    <?php if ($transaksi['status_pembayaran'] === "2") {
                                                        echo '<span class="badge badge-warning">Pending</span>';
                                                    } elseif ($transaksi['status_pembayaran'] === "1") {
                                                        echo '<span class="badge badge-success">Diterima</span>';
                                                    } else {
                                                        echo '<span class="badge badge-danger">Ditolak</span>';
                                                    } ?>
                                                </td>
                                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($transaksi['tanggal_transaksi']))) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($transaksi['metode_display'] ?? 'N/A') ?></strong><br>
                                                    <small><?= htmlspecialchars($transaksi['nomor_display'] ?? 'N/A') ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <th colspan="5" class="text-right font-weight-bold">Total Pendapatan (Diterima):</th> <th class="text-right font-weight-bold">Rp. <?= number_format($total, 0, ',', '.') ?></th>
                                            <th colspan="5"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif (isset($_POST['tampilkan']) && empty($myTransaksi)): ?>
                     <div class="alert alert-warning text-center mt-4">
                        Tidak ada data transaksi untuk filter yang dipilih.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#tabel-data').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                },
                "pageLength": 10,
                "order": [[9, "desc"]]
            });
        });
        
        function resetForm() {
            window.location.href = './';
        }
        
        document.getElementById('tgl_awal').addEventListener('change', function() {
            document.getElementById('tgl_akhir').min = this.value;
        });
    </script>
</body>

</html>