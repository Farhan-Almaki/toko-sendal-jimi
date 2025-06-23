<?php
session_start();

require './database/koneksi.php';
require './controller/cartController.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ./auth/login");
    exit;
}

$user_id = $_SESSION['dataUser']['user_id'];
$myCart = getMyCart($user_id);

// Handle update cart
if (isset($_POST['update'])) {
    $result = updateCart($_POST);
    
    if (is_int($result) && $result > 0) {
        echo "<script>
            alert('Keranjang berhasil diperbarui!');
            window.location.href = './my-cart';
        </script>";
    } elseif (is_string($result)) {
        echo "<script>
            alert('$result');
            window.location.href = './my-cart';
        </script>";
    } else {
        echo "<script>
            alert('Gagal memperbarui keranjang!');
            window.location.href = './my-cart';
        </script>";
    }
}

// Handle delete cart
if (isset($_POST['delete'])) {
    $id = $_POST['cart_id'];
    if (deleteCart($id) > 0) {
        echo "<script>
            alert('Item berhasil dihapus dari keranjang!');
            window.location.href = './my-cart';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus item dari keranjang!');
            window.location.href = './my-cart';
        </script>";
    }
}

// Handle response messages
$response_message = '';
if (isset($_GET['r'])) {
    switch ($_GET['r']) {
        case 'updatesuccess':
            $response_message = 'Keranjang berhasil diperbarui!';
            break;
        case 'updatefailed':
            $response_message = 'Gagal memperbarui keranjang!';
            break;
        case 'deletesuccess':
            $response_message = 'Item berhasil dihapus!';
            break;
        case 'deletefailed':
            $response_message = 'Gagal menghapus item!';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Cart - Toko Jimi Collection</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.25/datatables.min.css" />
</head>

<body id="home">
    <!-- Navbar -->
    <nav class="navbar-container sticky-top">
        <div class="navbar-logo">
            <h3><a href="./">Toko Jimi Collection</a></h3>
        </div>
        <div class="navbar-box">
            <ul class="navbar-list">
                <li><a href="./"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./shop"><i class="fas fa-shopping-cart"></i> Shop</a></li>
                <?php if (!isset($_SESSION['login'])) { ?>
                    <li><a href="./auth/login"><i class="fas fa-lock"></i> Signin</a></li>
                <?php } else { ?>
                    <li><a href="./my-cart"><i class="fas fa-shopping-bag"></i> My Cart</a></li>
                    <li><a href="./detail-transaksi"><i class="fas fa-list"></i> Pesanan</a></li>
                    <li><a href="./auth/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="navbar-toggle">
            <span></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <?php if ($response_message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($response_message) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-2">
                <div class="card">
                    <div class="card-header">
                        <h5>Menu</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><a href="./my-cart" class="btn btn-outline-primary btn-sm btn-block">Keranjang Saya</a></li>
                            <li class="mt-2"><a href="./detail-transaksi" class="btn btn-outline-secondary btn-sm btn-block">Pesanan Saya</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h5>Keranjang Belanja</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Stok</th>
                                        <th>Harga</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    $stok_habis = false;
                                    
                                    if (!empty($myCart)):
                                        foreach ($myCart as $item):
                                            $sub_total = intval($item['product_price'] ?? 0) * intval($item['qty'] ?? 0);
                                            
                                            // Cek stok habis
                                            if (($item['product_stok'] ?? 0) == 0) {
                                                $stok_habis = true;
                                            }
                                    ?>
                                    <tr <?= ($item['product_stok'] ?? 0) == 0 ? 'class="table-danger"' : '' ?>>
                                        <td><?= htmlspecialchars($item['product_name'] ?? '') ?></td>
                                        <td>
                                            <?= htmlspecialchars($item['product_stok'] ?? '') ?>
                                            <?php if (($item['product_stok'] ?? 0) == 0): ?>
                                                <span class="badge badge-danger">Habis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>Rp. <?= number_format($item['product_price'] ?? 0, 0, ',', '.') ?></td>
                                        <form action="" method="post" style="display: contents;">
                                            <td>
                                                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['keranjang_id'] ?? '') ?>">
                                                <input type="number" name="qty" class="form-control form-control-sm" 
                                                       value="<?= htmlspecialchars($item['qty'] ?? 1) ?>" 
                                                       min="1" max="<?= $item['product_stok'] ?? 0 ?>"
                                                       style="width: 80px; margin: 0 auto;">
                                            </td>
                                            <td>Rp. <?= number_format($sub_total, 0, ',', '.') ?></td>
                                            <td>
                                                <button type="submit" name="update" class="btn btn-warning btn-sm" 
                                                        title="Update Qty">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm ml-1" 
                                                        onclick="return confirm('Yakin ingin menghapus item ini?')"
                                                        title="Hapus Item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                    <?php 
                                            $total += $sub_total;
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-muted">
                                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                            <p>Keranjang Anda kosong</p>
                                            <a href="./shop" class="btn btn-primary">Mulai Belanja</a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($myCart)): ?>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <?php if ($stok_habis): ?>
                                        <div class="alert alert-warning" role="alert">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Perhatian!</strong> Ada produk dengan stok habis. 
                                            Silakan hapus atau tunggu restock untuk melanjutkan checkout.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 text-right">
                                    <h4>Total: <span class="text-success">Rp. <?= number_format($total, 0, ',', '.') ?></span></h4>
                                    
                                    <?php if (!$stok_habis): ?>
                                        <a href="./checkout" class="btn btn-success btn-lg mt-2">
                                            <i class="fas fa-credit-card"></i> Checkout
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-lg mt-2" disabled>
                                            <i class="fas fa-credit-card"></i> Checkout (Stok Habis)
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="./shop" class="btn btn-outline-primary btn-lg mt-2 ml-2">
                                        <i class="fas fa-shopping-cart"></i> Lanjut Belanja
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize AOS
            AOS.init();
            
            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>
</html>