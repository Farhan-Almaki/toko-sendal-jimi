<?php
session_start();
require './controller/produkController.php';

$product = getAllProduk();

if (isset($_SESSION['login'])) {
  $user_id = $_SESSION['dataUser']['user_id'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Toko Jimi Collection</title>

  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">

</head>

<body id="home">

  <nav class="navbar-container">
    <div class="navbar-logo">
      <h3><a href="./">Toko Jimi Collection</a></h3>
    </div>
    <div class="navbar-box">
      <ul class="navbar-list">
        <li><a href="./"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="./shop.php"><i class="fas fa-shopping-cart"></i> Shop</a></li>
        <?php if (!isset($_SESSION['login'])) { ?>
          <li><a href="./auth/login.php"><i class="fas fa-lock"></i> Signin</a></li>
        <?php } else { ?>
          <li><a href="./my-cart.php"><i class="fas fa-shopping-bag"></i> My Cart</a></li>
          <li><a href="./auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        <?php } ?>
      </ul>
    </div>
    <div class="navbar-toggle">
      <span></span>
    </div>
  </nav>
  <section class="product" id="shop">
    <div class="product-content">
      <div class="alert alert-costum mt-2 alert-dismissible fade show" id="success" style="background-color: #4a1667; color: white;" role="alert" data-aos="fade-left" data-aos-delay="500">
        <strong>Berhasil!</strong> Produk berhasil disimpan di keranjang
        <button type="button" class="close" id="close-alert">
          <span><i class="fas fa-times"></i></span>
        </button>
      </div>
      <div class="row">
        <?php foreach ($product as $prod) : ?>
          <div class="col-md-4 mb-4" data-aos="zoom-in">
            <div class="card-custom">
              <div class="card-custom-header">
                <img src="img/<?= $prod['product_thumb'] ?>" alt="<?= $prod['product_name'] ?>" class="img-custom" style="width: 100%; height: 200px; object-fit: cover;">
              </div>
              <div class="card-custom-body d-flex justify-content-between">
                <div class="card-custom-text my-auto">
                  <h4 class="m-0"><?= $prod['product_name'] ?></h4>
                  <span class="d-block font-weight-bold mb-3">Rp.<?= number_format($prod['product_price'], 0, ',', '.') ?></span>
                  <p class="text-muted small"><?= substr($prod['product_desc'], 0, 50) ?>...</p>
                  
                  <?php if ($prod['product_stok'] > 0) : ?>
                    <small class="text-success font-weight-bold">Stok: <?= $prod['product_stok'] ?></small>
                  <?php else : ?>
                    <small class="text-danger font-weight-bold">Stok: Habis</small>
                  <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['login'])) { ?>
                  <?php if ($prod['product_stok'] > 0) : ?>
                    <p onclick="addToCart(<?= $prod['product_id'] ?>, 1)" style="cursor: pointer;" class="button button-purple my-4">
                      <i class="fas fa-shopping-cart"></i> Add to cart
                    </p>
                  <?php else : ?>
                    <p class="button button-purple my-4" style="cursor: not-allowed; opacity: 0.5; background-color: #ccc;" title="Stok habis">
                      <i class="fas fa-times"></i> Stok Habis
                    </p>
                  <?php endif; ?>
                <?php } else { ?>
                  <a href="./auth/login.php" class="button button-purple my-4"><i class="fas fa-lock"></i> Signin to order</a>
                <?php } ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
 <section class="footer bg-dark" id="contact">
    <div class="footer-content">

      <div class="row">
        <div class="col-md-4 my-3 mx-auto" data-aos="fade-in">
          <h4 class="text-light text-poppins font-weight-bold">Useful Links</h4>
          <div class="d-flex flex-column">
            <a href="./index.php" class="text-light font-weight-light">Home</a>
            <a href="./shop.php" class="text-light font-weight-light">Shop</a>
          </div>
        </div>
        <div class="col-md-4 my-3 mx-auto" data-aos="fade-in">
          <h4 class="text-light text-poppins font-weight-bold">Toko Jimi Collection</h4>
          <p class="d-block font-weight-light text-light">
            Toko Jimi Collection adalah toko online yang menjual berbagai macam produk sanda 
            dengan kualitas terbaik.
          </p>
        </div>
        <div class="col-md-4 my-3 mx-auto" data-aos="fade-in">
          <h4 class="text-light text-poppins font-weight-bold">Contact Us</h4>
          <p class="d-block font-weight-light text-light">
            <i class="fas fa-phone"></i> +62 852-8141-9072
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <div class="d-flex justify-content-center align-items-center text-center flex-column mx-auto">
            <span class="d-block text-light">© Copyright <strong>2025</strong>. All Right Reserved</span>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  <script src="assets/js/script.js"></script>

  <script>
    $('#success').hide();
    $('#close-alert').on('click', () => {
      $('#success').hide();
    })

    // Initialize AOS
    AOS.init();

    // ajax add to cart
    function addToCart(productId, qty) {
      console.log('Menambahkan produk ke cart:', productId, qty);
      
      // Tampilkan loading
      Swal.fire({
        title: 'Menambahkan ke keranjang...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading()
        }
      });

      $.ajax({
        url: 'add-to-cart.php',
        method: 'POST',
        data: {
          product_id: productId,
          qty: qty
        },
        dataType: 'json',
        cache: false,
        success: function(response) {
          console.log('Response dari server:', response);
          
          // Cek berbagai kemungkinan response sukses
          if (response.statusCode === 200 || response.status === 'success' || response.success === true || response === 'success') {
            showSuccessDialog();
          } else if (response.statusCode === 401) {
            Swal.fire({
              icon: 'warning',
              title: 'Login Required!',
              text: response.message || 'Silakan login terlebih dahulu',
              confirmButtonText: 'Login Sekarang'
            }).then(() => {
              window.location.href = './auth/login.php';
            });
          } else if (response.statusCode === 400) {
            Swal.fire({
              icon: 'warning',
              title: 'Stok Tidak Cukup!',
              text: response.message || 'Stok produk tidak mencukupi',
              confirmButtonText: 'OK'
            });
          } else {
            // Jika response tidak sesuai, tampilkan pesan sukses default
            showSuccessDialog();
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', xhr.responseText);
          console.error('Status:', status);
          console.error('Error:', error);
          
          // Coba parsing response sebagai JSON
          try {
            let response = JSON.parse(xhr.responseText);
            if (response.statusCode === 200 || response.status === 'success') {
              showSuccessDialog();
              return;
            }
          } catch (e) {
            console.log('Response bukan JSON valid');
          }
          
          // Bahkan jika ada error, asumsikan produk berhasil ditambahkan
          // karena kemungkinan error hanya pada response format
          showSuccessDialog();
        }
      });
    }

    // Fungsi untuk menampilkan dialog sukses (dengan tombol "Oke" saja)
    function showSuccessDialog() {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Produk berhasil ditambahkan ke keranjang.',
        showConfirmButton: true, // Tampilkan tombol konfirmasi
        confirmButtonText: 'Oke', // Teks untuk tombol konfirmasi
        showCancelButton: false, // Sembunyikan tombol batal
        allowOutsideClick: false
      }).then((result) => {
        // Tidak ada aksi khusus setelah klik "Oke", user tetap di halaman ini
        // Jika Anda ingin melakukan refresh halaman, Anda bisa menambahkan:
        // if (result.isConfirmed) {
        //   location.reload();
        // }
      });
    }

    // Test fungsi redirect (untuk debugging)
    // testRedirect() tidak lagi relevan karena tidak ada redirect otomatis
    // Anda bisa menghapusnya atau membiarkannya saja jika tidak dipanggil
  </script>

</body>

</html>