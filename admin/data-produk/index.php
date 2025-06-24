<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: ../');
    exit;
}
require './produk_function.php';

$id = (isset($_GET['id'])) ? $_GET['id'] : null;
$respon = (isset($_GET['response'])) ? $_GET['response'] : null;
$hapus = (isset($_GET['hapus'])) ? $_GET['hapus'] : null;
$modal = (isset($_GET['modal'])) ? $_GET['modal'] : null;


if ($respon === "deletesuccess") {
    $respon = "Data produk berhasil dihapus!";
} elseif ($respon === "deletefalse") {
    $respon = "Data produk gagal dihapus!";
} elseif ($respon === "successadd") {
    $respon = "Data produk berhasil ditambah!";
} elseif ($respon === "failadd") {
    $respon = "Data produk gagal ditambah!";
} elseif ($respon === "imgfail") {
    $respon = "Anda belum pilih gambar produk!";
} elseif ($respon === "imgwarning") {
    $respon = "Yang Anda upload bukan gambar!";
} elseif ($respon === "imgover") {
    $respon = "Ukuran gambar terlalu besar!";
} elseif ($respon === "error") {
    $respon = "Anda belum pilih kategori!";
} elseif ($respon === "updatesuccess") {
    $respon = "Berhasil ubah data produk!";
} elseif ($respon === "updatefalse") {
    $respon = "Gagal ubah data produk!";
}


$produk = query("SELECT * FROM tb_product");

if ($id != null) {
    $productId = query("SELECT * FROM tb_product WHERE product_id = '$id'")[0];
}


if ($hapus === "true") {
    if (delete($id) > 0) {
        echo "
			<script>
				document.location.href = './?response=deletesuccess';
			</script>
		";
    } else {
        echo "
			<script>
				document.location.href = './?response=deletefalse';
			</script>
		";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>Data Produk | Admin Dashboard</title>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
        }

        /* Menghilangkan flex dari container utama */
        .container {
            padding: 0;
            margin: 0;
            width: 100%;
        }

        /* Menyembunyikan header lama */
        .header {
            display: none;
        }

        /* === PERUBAHAN UTAMA DI SINI === */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            /* 1. Atur posisi menjadi 'fixed' agar tetap di tempat */
            position: fixed;
            /* 2. Buat tingginya selalu 100% dari layar */
            height: 100vh;
            /* 3. Pin ke pojok kiri atas */
            top: 0;
            left: 0;
            /* 4. Pastikan selalu di atas konten lain */
            z-index: 1000;
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
        
        /* === PERUBAHAN KEDUA DI SINI === */
        .main {
            /* 5. Beri margin kiri seukuran lebar sidebar agar konten tidak tertutup */
            margin-left: 250px;
            width: auto; /* Biarkan lebarnya otomatis */
        }
        
        .page-content {
            padding: 2rem;
        }

        /* Penyesuaian tampilan tabel agar lebih modern */
        .page-content .table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

    </style>
</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="site-title"><i class="fa-solid fa-store"></i> Toko Jimi</span>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li><a href="./index.php" class="active"><i class="fas fa-box-archive"></i><span>Data Produk</span></a></li>
                    <li><a href="../data-pesanan/index.php"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
                    <li><a href="../data-laporan/index.php"><i class="fas fa-file-alt"></i><span>Laporan</span></a></li>
                    <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <div class="main">
            <div class="page-content">
                <?php if ($respon) : ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($respon) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.style.display='none';">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <h1 class="mb-4" style="font-weight: 600;">Manajemen Produk</h1>
                <a href="./tambah-produk.php" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Tambah Barang</a>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark text-center">
                            <tr>
                                <th>Nama Produk</th>
                                <th>Deskripsi</th>
                                <th>Gambar</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produk as $item) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= htmlspecialchars($item['product_desc']) ?></td>
                                    <td class="text-center"><img src="../../img/<?= htmlspecialchars($item['product_thumb']) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;"></td>
                                    <td class="text-right">Rp. <?= number_format($item['product_price'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($item['product_stok']) ?></td>
                                    <td class="text-center">
                                        <a href="./update-produk.php?id=<?= $item['product_id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?hapus=true&id=<?= $item['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($produk)) : ?>
                    <div class="alert alert-warning text-center mt-3">
                        <h3>Data Produk Masih Kosong</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>

</html>