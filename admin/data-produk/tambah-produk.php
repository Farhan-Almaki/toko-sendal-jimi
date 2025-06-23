<?php

session_start();
require './produk_function.php';
if (!isset($_SESSION['login'])) {
    // Perbaikan: 'Location; ../' menjadi 'Location: ../'
    header('Location: ../');
    exit;
}


// ADD PRODUCT LOGIC
if (isset($_POST['tambah-produk'])) {
    // Logika ini akan diproses sebelum redirect
    if (tambah($_POST) > 0) {
        echo "
			<script>
				document.location.href = './?response=successadd';
			</script>
		";
    } else {
        // Redirect dengan pesan error bisa lebih spesifik jika fungsi tambah() mengembalikan pesan error
        echo "
			<script>
				document.location.href = './?response=failadd';
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>Tambah Produk | Admin Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
        }
        
        /* Gaya Sidebar Fixed */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
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

        .sidebar nav ul {
            list-style: none;
            padding: 1.5rem 0;
            margin: 0;
        }

        .sidebar nav li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #ecf0f1;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 500;
            border-left: 4px solid transparent;
        }

        .sidebar nav li a i {
            margin-right: 15px;
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }

        .sidebar nav li a:hover,
        .sidebar nav li a.active {
            background-color: #4A90E2;
            color: #fff;
            border-left: 4px solid #F5A623;
        }

        /* Gaya Konten Utama */
        .main-content {
            margin-left: 250px;
        }

        .header {
            background-color: #4A90E2;
            color: white;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .page-content {
            padding: 2rem;
        }

        .form-card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="site-title"><i class="fa-solid fa-store"></i> Toko Jimi</span>
        </div>
        <nav>
            <ul>
                <li><a href="#" class="active"><i class="fas fa-plus-circle"></i><span>Tambah Produk</span></a></li>
                <li><a href="./"><i class="fas fa-arrow-left"></i><span>Kembali</span></a></li>
                <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </aside>

    <div class="main-content">
        <div class="header">
            <h1>Tambah Produk Baru</h1>
        </div>
        <div class="page-content">
            <div class="form-card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk</label>
                        <input type="text" id="nama_produk" name="nama_produk" placeholder="Masukkan nama produk" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="harga_produk">Harga Produk</label>
                        <input type="number" id="harga_produk" name="harga_produk" placeholder="Contoh: 50000" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="desc_produk">Deskripsi Produk</label>
                        <textarea id="desc_produk" name="desc_produk" placeholder="Jelaskan detail produk" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="stok_produk">Stok Produk</label>
                        <input type="number" id="stok_produk" name="stok_produk" placeholder="Masukkan jumlah stok" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Produk</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="gambar" id="gambar" required>
                            <label class="custom-file-label" for="gambar">Pilih file...</label>
                        </div>
                    </div>

                    <button type="submit" name="tambah-produk" class="btn btn-primary mt-3">
                        <i class="fas fa-save"></i> Tambah Produk
                    </button>
                    <a href="./" class="btn btn-secondary mt-3">Batal</a>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script>
        // Script untuk menampilkan nama file pada input file Bootstrap
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
</body>
</html>