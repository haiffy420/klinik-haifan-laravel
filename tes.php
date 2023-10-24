<!DOCTYPE html>
<html lang="en">
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div>
        <h1>Semua Transaksi</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>ID Petugas</th>
                    <th>Total Biaya</th>
                    <th>Cetak Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM transaksi";
                $result = mysqli_query($koneksi, $query);

                while ($transaksi = mysqli_fetch_assoc($result)) {
                    $id_transaksi = $transaksi['id_transaksi'];
                    $id_petugas = $transaksi['id_petugas'];
                    $total_biaya = 0;

                    $detail_query =
                        "SELECT dt.id_obat, dt.qty, o.harga
                        FROM detail_transaksi dt
                        JOIN obat o ON dt.id_obat = o.id_obat
                        WHERE dt.id_transaksi = '$id_transaksi'";
                    $detail_result = mysqli_query($koneksi, $detail_query);

                    while ($detail = mysqli_fetch_assoc($detail_result)) {
                        $harga = (int)$detail['harga'];
                        $biaya_obat = $detail['qty'] * $harga;
                        $total_biaya += $biaya_obat;
                    }

                    echo "<tr>";
                    echo "<td>$id_transaksi</td>";
                    echo "<td>$id_petugas</td>";
                    echo "<td>$total_biaya</td>";
                    echo "<td><a href='cetak.php?id_transaksi=$id_transaksi'>Cetak Invoice</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <h1>Transaksi baru</h1>
    <form method="post" action="transaksi.php">
        <div style="display: flex; flex-direction: column; max-width:fit-content;">
            <label for="id_petugas">ID Petugas</label>
            <input type="text" name="id_petugas" id="id_petugas">
            <br />
            <div id="obat-container">
                <div class="obat">
                    <label for="id_obat">ID Obat</label>
                    <select name="id_obat[]" class="id_obat">
                        <option value="">Pilih Obat</option>
                        <?php
                        $query = "SELECT * FROM obat";
                        $result = mysqli_query($koneksi, $query);

                        if (!$result) {
                            echo "Error: " . mysqli_error($koneksi);
                        }

                        while ($row = mysqli_fetch_assoc($result)) {
                            $id_obat = $row['id_obat'];
                            $nama_obat = $row['nama_obat'];

                            echo "<option value='$id_obat'>$nama_obat</option>";
                        }
                        ?>
                    </select>
                    <label for="qty">Quantity</label>
                    <input type="text" name="qty[]" class="qty">
                </div>
            </div>
            <button type="button" id="addObat">Tambah Obat</button>
        </div>
        <br />
        <button type="submit">Submit</button>
    </form>

    <script>
        document.getElementById("addObat").addEventListener("click", function() {
            var obatContainer = document.getElementById("obat-container");
            var newObat = document.querySelector(".obat").cloneNode(true);

            newObat.querySelectorAll("input").forEach(function(input) {
                input.value = "";
            });

            obatContainer.appendChild(newObat);
        });
    </script>

</body>

</html>
