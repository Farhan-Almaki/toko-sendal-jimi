<?php

// import koneksi
require '../database/koneksi.php';

function registrasi($data)
{
    global $conn;

    $fullname = strtolower(stripslashes($data["fullname"]));
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);
    $alamat = stripslashes($data["alamat"]); // Hapus strtolower untuk alamat agar tetap proper case
    $contact = stripslashes($data["contact"]); // Tambahan field kontak

    // cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM tb_user WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>
            window.location.href = '../auth/register?response=usnfalse'
			</script>";
        return false;
    }

    // cek konfirmasi password
    if ($password !== $password2) {
        echo "<script>
            window.location.href = '../auth/register?response=passfalse'
			</script>";
        return false;
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // tambahkan userbaru ke database dengan kolom contact
    // Perbaikan: Query INSERT disesuaikan dengan struktur tabel yang memiliki kolom contact
    mysqli_query($conn, "INSERT INTO tb_user (username, fullname, password, role, alamat, contact) VALUES ('$username', '$fullname', '$password', 2, '$alamat', '$contact')");

    return mysqli_affected_rows($conn);
}