<?php
session_start();
require '../database/koneksi.php';
require '../controller/authController.php';

// url get
$response = (isset($_GET['response'])) ? $_GET['response'] : null;

// flash msg dengan warna yang sesuai
if ($response === "passfalse") {
    $response = "Wrong password";
    $alert_type = "danger";
} elseif ($response === "false") {
    $response = "Account is not available, please register first";
    $alert_type = "warning";
} elseif ($response === "signupsuccess") {
    $response = "Registration successful! Please login with your new account";
    $alert_type = "success";
}

// cek apakah tombol submit suda ditekan atau belum
if (isset($_POST["login"])) {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $result = mysqli_query($conn, "SELECT * FROM tb_user WHERE username = '$username' ");

    // cek username
    if (mysqli_num_rows($result) === 1) {

        //cek password
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row["password"])) {
                $_SESSION["login"] = true;
                $_SESSION['dataUser'] = [
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                    'fullname' => $row['fullname'] ?? '',
                    'alamat' => $row['alamat'] ?? '',
                    'contact' => $row['contact'] ?? '',
                ];
            if ($row['role'] === "2") {
            header('Location: /index.php');
            exit;
        } elseif ($row['role'] === '1') {
            header('Location: /admin/index.php');
            exit;
        }
        } else {
            echo "<script>
            window.location.href = './login.php?response=passfalse'
            </script>";
        }
    } else {
        echo "<script>
            window.location.href = './login.php?response=false'
            </script>";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css">

    <link rel="stylesheet" href="style.css">

    <title>Toko Jimmi Collection | Sign In</title>
</head>

<body>

    <div class="global-container">
        <div class="card login-form">
            <div class="card-body">
                <h3 class="card-title text-center">Login</h3>
                <div class="card-text">
                    <?php if ($response) : ?>
                        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                            <?php if ($alert_type === "success") : ?>
                                <i class="fas fa-check-circle mr-2"></i>
                            <?php elseif ($alert_type === "danger") : ?>
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?php else : ?>
                                <i class="fas fa-info-circle mr-2"></i>
                            <?php endif; ?>
                            <strong><?= $response ?></strong>
                            <button type="button" class="close" id="close-alert">
                                <a href="./login.php"><i class="fas fa-times"></i></a>
                            </button>
                        </div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" class="form-control form-control-sm" id="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control form-control-sm" id="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block">Sign in</button>

                        <div class="sign-up">
                            Don't have an account? <a href="./register.php">Create One</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Auto hide alert after registration success -->
    <script>
        // Auto hide success alert setelah 5 detik
        <?php if ($response && $alert_type === "success") : ?>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        <?php endif; ?>
    </script>
</body>

</html>