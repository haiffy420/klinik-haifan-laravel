<?php
$host = "localhost";
$db = "db_apotek";
$user = "root";
$pass = "";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
}

if (isset($_POST['id_petugas'])) {
    $id_petugas = $_POST['id_petugas'];
    $id_obat = $_POST['id_obat'];
    $qty = $_POST['qty'];

    // Insert a new transaction
    $query = "INSERT INTO transaksi (`id_petugas`) VALUES ('$id_petugas')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $last_id_transaksi = mysqli_insert_id($koneksi);

        // Loop through the arrays of id_obat and qty to insert multiple rows
        for ($i = 0; $i < count($id_obat); $i++) {
            $current_id_obat = $id_obat[$i];
            $current_qty = $qty[$i];

            $query2 = "INSERT INTO detail_transaksi (`id_transaksi`, `id_obat`, `qty`) VALUES ('$last_id_transaksi', '$current_id_obat', '$current_qty')";
            $result2 = mysqli_query($koneksi, $query2);

            if (!$result2) {
                echo "Data gagal ditambahkan: " . mysqli_error($koneksi);
                break; // Exit the loop if an insertion fails
            }
        }

        echo "Data berhasil ditambahkan";
        // redirect to tes.php
        header("refresh:3;url=tes.php");
        exit();
    } else {
        echo "Data gagal ditambahkan: " . mysqli_error($koneksi);
    }
}
