<?php
session_start();

if (!isset($_SESSION['login'])) {
    header('Location: ./auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Toko Jimi Collection | Admin Dashboard</title>
    <style>
        /* CSS Reset & General Styling */
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #F5A623;
            --background-color: #f4f7fa;
            --sidebar-bg: #2c3e50;
            --text-color: #333;
            --light-text-color: #ecf0f1;
            --border-color: #e0e0e0;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        /* Sidebar Styling (Fixed) */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--light-text-color);
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #34495e;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar-header .site-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .sidebar nav ul {
            list-style: none;
            padding: 1.5rem 0;
        }

        .sidebar nav li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--light-text-color);
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
            background-color: var(--primary-color);
            color: #fff;
            border-left: 4px solid var(--secondary-color);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header-title p {
            color: #777;
            font-size: 0.9rem;
        }

        .page-content {
            padding: 2rem;
        }

        /* Hero Section Styling */
        .hero-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            align-items: center;
            gap: 2rem;
            background: linear-gradient(135deg, var(--primary-color), #3498db);
            color: #fff;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .hero-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-text p {
            font-size: 1rem;
            margin-bottom: 2rem;
            max-width: 500px;
        }

        .hero-image img {
            width: 100%;
            max-width: 450px; /* Adjusted max-width for better fit */
            display: block;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            object-fit: cover;
            height: 280px; /* Adjusted height for the new image */
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
                <li><a href="./" class="active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="./data-produk.php"><i class="fas fa-box-archive"></i><span>Data Produk</span></a></li>
                <li><a href="./data-pesanan.php"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
                <li><a href="./data-laporan.php"><i class="fas fa-file-alt"></i><span>Laporan</span></a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="header-title">
                <h1>Welcome Back, Admin!</h1>
                <p>Semoga Harimu Menyenangkan</p>
            </div>
        </header>

        <div class="page-content">
            <div class="hero-section">
                <div class="hero-text">
                    <h2>Toko Jimi Collection</h2>
                        <p>Toko Jimi! Berlokasi di Cikupa, Tangerang, kami adalah spesialis sandal Crocs yang terpercaya. Temukan koleksi terlengkap yang menawarkan kenyamanan tak tertandingi dan gaya yang ikonik untuk seluruh keluarga.</p>
                </div>
                <div class="hero-image">
                    <img src="./img/clogs2.jpg" alt="">
                </div>
            </div>
        </div>
    </main>
</body>
</html>