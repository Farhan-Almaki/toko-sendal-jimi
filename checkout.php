<?php
// checkout.php

session_start();

require __DIR__ . '/database/koneksi.php';
require __DIR__ . '/controller/cartController.php';
require __DIR__ . '/controller/bankController.php'; 
require __DIR__ . '/controller/transaksiController.php';

if (isset($_SESSION['login']) && isset($_SESSION['dataUser']['user_id'])) {
    $user_id = $_SESSION['dataUser']['user_id'];
    $fullname = $_SESSION['dataUser']['fullname'] ?? '';
    $alamat = $_SESSION['dataUser']['alamat'] ?? '';
    $contact = $_SESSION['dataUser']['contact'] ?? '';
} else {
    echo "<script>alert('Anda harus login untuk checkout.'); window.location.href = './auth/login.php';</script>";
    exit;
}


$myCart = getMyCart($user_id);
$response_message = '';
$alert_class = 'info';

if (isset($_GET['r'])) {
    $response = $_GET['r'];
    if ($response === "trxfailed") {
        $response_message = "Transaksi gagal, silakan coba lagi.";
        $alert_class = 'danger';
    } elseif ($response === "bankfalse") {
        $response_message = "Mohon pilih metode pembayaran.";
        $alert_class = 'warning';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi dasar, pastikan metode dan nomor pembayaran ada
    if (empty($_POST['metode_pembayaran_user']) || empty($_POST['nomor_pembayaran_user'])) {
        header('Location: ./checkout.php?r=bankfalse');
        exit;
    }

    if (empty($myCart)) {
        echo "<script>alert('Keranjang Anda kosong, tidak dapat membuat transaksi.'); window.location.href = './my-cart.php';</script>";
        exit;
    }

    // `bank_id` mungkin tidak diisi dari form jika itu e-wallet, jadi kita akan membiarkannya null
    // Fungsi addTransaksi di controller akan mengurus pengaitan bank_id jika metode adalah VA
    // Kita bisa tambahkan 'bank_id' ke $_POST jika ada, atau biarkan fungsi addTransaksi yang menentukan.
    // Untuk sederhana, kita akan biarkan addTransaksi yang menentukan bank_id berdasarkan metode_pembayaran_user
    // $transaksi_id_baru = addTransaksi($_POST, $myCart); // ini sudah benar

    $transaksi_id_baru = addTransaksi($_POST, $myCart);


    if ($transaksi_id_baru > 0) {
        // Redirect ke detail-transaksi dengan parameter sukses
        header("Location: ./detail-transaksi.php?r=trxsuccess");
        exit;
    } else {
        header('Location: ./checkout.php?r=trxfailed');
        exit;
    }
}

// Data metode pembayaran yang akan ditampilkan di frontend
$metode_pembayaran_utama = [
    ['value' => 'virtual_account', 'text' => 'Virtual Account'],
    ['value' => 'e_wallet', 'text' => 'E-Wallet'],
];

$virtual_account_options = [
    ['value' => 'BCA', 'text' => 'BCA'],
    ['value' => 'BTN', 'text' => 'BTN'],
    ['value' => 'MANDIRI', 'text' => 'Mandiri'],
    ['value' => 'BRI', 'text' => 'BRI'],
];

$ewallet_options = [
    ['value' => 'DANA', 'text' => 'DANA'],
    ['value' => 'GOPAY', 'text' => 'GOPAY'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Checkout - Toko Jimi</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">
    
    <style>
        .swal2-popup.swal-wide {
            width: 800px !important;
        }
        /* CSS untuk menyembunyikan/menampilkan form group */
        .hidden-form-group {
            display: none;
        }
    </style>
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
                <?php if (!isset($_SESSION['login'])) { ?>
                    <li><a href="./auth/login.php"><i class="fas fa-lock"></i> Signin</a></li>
                <?php } else { ?>
                    <li><a href="./my-cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                    <li><a href="./detail-transaksi"><i class="fas fa-list"></i> Pesanan</a></li>
                    <li><a href="./auth/logout.php"><i class="fas fa-lock"></i> Logout</a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="navbar-toggle"><span></span></div>
    </nav>
    <div class="container mt-5">
        <?php if ($response_message) : ?>
            <div class="alert alert-<?= $alert_class ?> mt-2 alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($response_message) ?></strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Informasi Pembeli</h4>
                <form action="" method="post" id="checkout-form">
                    <input type="hidden" name="user_id" id="user_id" value="<?= htmlspecialchars($user_id) ?>">
                    <input type="hidden" name="metode_pembayaran_user" id="metode_pembayaran_user_input">
                    <input type="hidden" name="nomor_pembayaran_user" id="nomor_pembayaran_user_input">
                    <input type="hidden" name="total_pembayaran" value="<?= htmlspecialchars($total ?? 0) ?>">
                    
                    <div class="form-group">
                        <label for="nama_pembeli">Nama lengkap</label>
                        <input type="text" name="nama_pembeli" id="nama_pembeli" class="form-control" value="<?= htmlspecialchars($fullname) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="alamat_pembeli">Alamat lengkap</label>
                        <textarea name="alamat_pembeli" id="alamat_pembeli" class="form-control" rows="3" placeholder="Masukkan alamat lengkap Anda" required><?= htmlspecialchars($alamat) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="contact_pembeli">Nomor Kontak</label>
                        <input type="tel" name="contact_pembeli" id="contact_pembeli" class="form-control" value="<?= htmlspecialchars($contact) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="select_jenis_pembayaran">Pilih Jenis Pembayaran</label>
                        <select class="form-control" id="select_jenis_pembayaran" required>
                            <option selected value="0">Pilih Jenis Pembayaran...</option>
                            <?php foreach ($metode_pembayaran_utama as $option) : ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['text']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih jenis pembayaran (Virtual Account atau E-Wallet)</small>
                    </div>

                    <div class="form-group hidden-form-group" id="bank-va-group">
                        <label for="select_bank_va">Pilih Bank Virtual Account</label>
                        <select class="form-control" id="select_bank_va">
                            <option selected value="0">Pilih Bank...</option>
                            <?php foreach ($virtual_account_options as $option) : ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['text']) ?></Virtual Account></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group hidden-form-group" id="ewallet-group">
                        <label for="select_ewallet">Pilih E-Wallet</label>
                        <select class="form-control" id="select_ewallet">
                            <option selected value="0">Pilih E-Wallet...</option>
                            <?php foreach ($ewallet_options as $option) : ?>
                                <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['text']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    </form>
            </div>
            
            <div class="col-md-6">
                <h4>Ringkasan Pesanan</h4>
                <div class="card" id="ringkasan-pesanan">
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $total = 0; ?>
                                <?php if (!empty($myCart)): foreach ($myCart as $cartItem) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cartItem['product_name']) ?></td>
                                        <td><?= $cartItem['qty'] ?></td>
                                        <td>Rp.<?= number_format($cartItem['product_price'], 0, ',', '.') ?></td>
                                        <?php
                                        $sub_total = intval($cartItem['product_price']) * intval($cartItem['qty']);
                                        ?>
                                        <td>Rp.<?= number_format($sub_total, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php $total += $sub_total; ?>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="text-center">Keranjang kosong</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="3">Total Pembayaran</th>
                                    <th id="total-pembayaran-display">Rp.<?= number_format($total, 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
        $(document).ready(function() {
            AOS.init();

            // Sembunyikan semua grup pilihan detail pembayaran saat halaman dimuat
            $('#bank-va-group').hide();
            $('#ewallet-group').hide();

            let finalMetodePembayaranText = ''; // Variabel untuk menyimpan teks metode pembayaran akhir
            let finalNomorPembayaran = ''; // Variabel untuk menyimpan nomor pembayaran dari pop-up pertama

            // Event listener untuk dropdown JENIS pembayaran (Virtual Account / E-Wallet)
            $('#select_jenis_pembayaran').on('change', function() {
                const jenisValue = $(this).val();
                
                // Reset pilihan detail dan sembunyikan semua grup
                $('#select_bank_va').val('0');
                $('#select_ewallet').val('0');
                $('#bank-va-group').hide();
                $('#ewallet-group').hide();
                finalMetodePembayaranText = ''; // Reset teks final
                finalNomorPembayaran = ''; // Reset nomor pembayaran

                if (jenisValue === 'virtual_account') {
                    $('#bank-va-group').show();
                } else if (jenisValue === 'e_wallet') {
                    $('#ewallet-group').show();
                } else {
                    // Jika memilih "Pilih Jenis Pembayaran...", pastikan tidak ada grup yang tampil
                    $('#bank-va-group').hide();
                    $('#ewallet-group').hide();
                }
            });

            // Event listener untuk dropdown BANK Virtual Account
            $('#select_bank_va').on('change', function() {
                const bankVaValue = $(this).val();
                if (bankVaValue !== '0') {
                    finalMetodePembayaranText = 'Virtual Account ' + $(this).find('option:selected').text();
                    showNumberInputPopup(finalMetodePembayaranText, bankVaValue); // Panggil pop-up input nomor
                }
            });

            // Event listener untuk dropdown E-Wallet
            $('#select_ewallet').on('change', function() {
                const ewalletValue = $(this).val();
                if (ewalletValue !== '0') {
                    finalMetodePembayaranText = 'e-Wallet ' + $(this).find('option:selected').text();
                    showNumberInputPopup(finalMetodePembayaranText, ewalletValue); // Panggil pop-up input nomor
                }
            });

            // Fungsi untuk menampilkan pop-up input nomor VA/e-wallet
            function showNumberInputPopup(metodeText, metodeValueForPlaceholder) {
                let labelNomor = '';
                let placeholderText = '';

                if (metodeText.includes('Virtual Account')) {
                    labelNomor = 'Nomor Virtual Account';
                    // Ambil nama bank dari metodeText, misal "Virtual Account BCA" -> "BCA"
                    const bankName = metodeText.replace('Virtual Account ', '');
                    placeholderText = `Masukkan ${labelNomor} ${bankName}`;
                } else if (metodeText.includes('e-Wallet')) {
                    labelNomor = 'Nomor e-Wallet';
                    // Ambil nama e-wallet dari metodeText, misal "e-Wallet DANA" -> "DANA"
                    const ewalletName = metodeText.replace('e-Wallet ', '');
                    placeholderText = `Masukkan ${labelNomor} ${ewalletName} (Contoh: 0812xxxxxxxx)`;
                }

                Swal.fire({
                    title: `<strong>Masukkan ${labelNomor}</strong>`,
                    html: `
                        <p>Silakan masukkan nomor ${labelNomor} Anda untuk pembayaran menggunakan <strong>${metodeText}</strong>.</p>
                        <div class="form-group text-left">
                            <label for="nomor_pembayaran_input">${labelNomor}</label>
                            <input type="text" id="nomor_pembayaran_input" class="swal2-input" placeholder="${placeholderText}" required>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-arrow-right"></i> Lanjutkan',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    allowOutsideClick: false, // Tidak bisa klik di luar pop-up
                    preConfirm: () => {
                        const nomorPembayaran = Swal.getPopup().querySelector('#nomor_pembayaran_input').value;
                        if (!nomorPembayaran) {
                            Swal.showValidationMessage('Mohon masukkan nomor pembayaran Anda.');
                            return false;
                        }
                        return nomorPembayaran;
                    }
                }).then((result) => {
                    if (result.value) {
                        finalNomorPembayaran = result.value; // Simpan nomor yang diinput
                        // Lanjutkan ke pop-up konfirmasi pesanan
                        showConfirmationPopup(finalMetodePembayaranText, finalNomorPembayaran);
                    } else {
                        // Jika pop-up input nomor dibatalkan, reset semua pilihan
                        $('#select_jenis_pembayaran').val('0');
                        $('#select_bank_va').val('0');
                        $('#select_ewallet').val('0');
                        $('#bank-va-group').hide();
                        $('#ewallet-group').hide();
                        finalMetodePembayaranText = '';
                        finalNomorPembayaran = '';
                    }
                });
            }

            // Fungsi untuk menampilkan pop-up konfirmasi pesanan
            function showConfirmationPopup(metodeText, nomorPembayaranUser) {
                // Ambil semua data yang dibutuhkan untuk ditampilkan di pop-up
                const namaPembeli = $('#nama_pembeli').val();
                const alamatPembeli = $('#alamat_pembeli').val();
                const contactPembeli = $('#contact_pembeli').val();
                const ringkasanHtml = $('#ringkasan-pesanan').html();
                const totalPembayaran = $('#total-pembayaran-display').text();

                Swal.fire({
                    title: '<strong>Konfirmasi Pesanan Anda</strong>',
                    icon: 'info',
                    html: `
                        <div style="text-align: left;">
                            <p>Harap periksa kembali detail pesanan Anda sebelum melanjutkan.</p>
                            <hr>
                            <strong>Nama Pembeli:</strong> ${namaPembeli}<br>
                            <strong>Alamat:</strong> ${alamatPembeli}<br>
                            <strong>Kontak:</strong> ${contactPembeli}<br>
                            <strong>Dibayar Dengan:</strong> ${metodeText}<br>
                            <strong>Nomor Pembayaran:</strong> ${nomorPembayaranUser}
                            <hr>
                        </div>
                        ${ringkasanHtml}
                        <hr>
                        <div style="text-align: right; font-weight: bold; font-size: 1.2em;">
                            Total Pembayaran: ${totalPembayaran}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Buat Pesanan',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    customClass: {
                        popup: 'swal-wide'
                    }
                }).then((result) => {
                    if (result.value) {
                        // Set nilai ke input hidden sebelum submit form
                        $('#metode_pembayaran_user_input').val(metodeText);
                        $('#nomor_pembayaran_user_input').val(nomorPembayaranUser);
                        // Tidak perlu set bank_id_input lagi, karena addTransaksi akan menentukan sendiri
                        $('#checkout-form').submit();
                    } else {
                        // Jika pop-up konfirmasi dibatalkan, reset semua pilihan
                        $('#select_jenis_pembayaran').val('0');
                        $('#select_bank_va').val('0');
                        $('#select_ewallet').val('0');
                        $('#bank-va-group').hide();
                        $('#ewallet-group').hide();
                        finalMetodePembayaranText = '';
                        finalNomorPembayaran = '';
                    }
                });
            }
        });
    </script>
</body>
</html>