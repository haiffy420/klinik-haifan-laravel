<!DOCTYPE html>
<html>
<?php
$host = "localhost";
$db = "db_apotek";
$user = "root";
$pass = "";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
}

?>

<head>
    <title>Invoice</title>
</head>

<body>
    <h1>Invoice</h1>

    <?php
    if (isset($_GET['id_transaksi'])) {
        $id_transaksi = $_GET['id_transaksi'];

        $detail_query = "SELECT dt.id_obat, dt.qty, o.nama_obat, o.harga
                        FROM detail_transaksi dt
                        JOIN obat o ON dt.id_obat = o.id_obat
                        WHERE dt.id_transaksi = '$id_transaksi'";
        $detail_result = mysqli_query($koneksi, $detail_query);

        if (mysqli_num_rows($detail_result) > 0) {
            echo "<h2>Detail Transaksi</h2>";
            echo "<table border='1'>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Obat</th>
                            <th>Quantity</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>

                    </thead>
                    <tbody>";

            $total_biaya = 0;
            $no = 0;

            while ($detail = mysqli_fetch_assoc($detail_result)) {
                $no++;
                $id_obat = $detail['id_obat'];
                $nama_obat = $detail['nama_obat'];
                $harga = (int)$detail['harga'];
                $qty = $detail['qty'];
                $biaya_obat = $qty * $harga;
                $total_biaya += $biaya_obat;

                echo "<tr>
                        <td>$no</td>
                        <td>$nama_obat</td>
                        <td>$qty</td>
                        <td>$harga</td>
                        <td>$biaya_obat</td>
                    </tr>";
            }
            echo "<tr>
            <td colspan='4'>Total Biaya:</td>
            <td>$total_biaya</td>
            </tr>";

            echo "</tbody>
                </table>";
        } else {
            echo "No details found for the provided transaction ID.";
        }
    } else {
        echo "Transaction ID not provided.";
    }
    ?>

</body>

</html>
