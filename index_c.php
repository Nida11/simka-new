<?php 
$hostname = '15.1.1.77';
$username = 'simka';
$password = 'Lampu2014';
$database = 'bapenda_jabar_dev';
$port     = '3306';

$conn = new mysqli($hostname, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

function format_polisi($nopol) {
    // Menghilangkan spasi yang berlebih dan merapikan nomor polisi
    $nopol = trim($nopol);
    if (preg_match('/^([A-Z])(?:\s?)([A-Z]{0,3})(\d{0,4})$/', $nopol, $matches)) {
        $hurufAwal = $matches[1];
        $hurufTengah = $matches[2];
        $angka = $matches[3];

        $spasiTengah = 4 - strlen($hurufTengah);
        return $hurufAwal . str_repeat(" ", 1) . $hurufTengah . str_repeat(" ", $spasiTengah) . $angka;
    }
    return $nopol;
}

$where = "";
$limit = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $cari = $conn->real_escape_string($_POST['no_polisi']);
        $formatted = format_polisi($cari);

        $where = "WHERE REPLACE(no_polisi, ' ', '') REGEXP '^[A-Z]{1}[A-Z]{0,3}[0-9]{0,4}$'";
    } elseif (isset($_POST['search_all'])) {
        // Format fleksibel seperti T DSA6292, T, T 6, T DSA, dsb.
        $where = "WHERE no_polisi REGEXP '^[A-Z]( [A-Z]{0,3})?(\\d{0,4})?$'";
        $limit = "LIMIT 20";
    } elseif (isset($_POST['update_all']) && isset($_POST['check_list'])) {
        foreach ($_POST['check_list'] as $id => $old_nopol) {
            $formatted = format_polisi($old_nopol);
            $id = (int)$id;
            $sql = "UPDATE simka_identitas_kendaraan SET no_polisi = '{$formatted}' WHERE id_identitas_kendaraan = {$id}";
            $conn->query($sql);
        }
    }

    $sql = "
        SELECT id_identitas_kendaraan, no_polisi FROM simka_identitas_kendaraan 
        $where ORDER BY id_identitas_kendaraan $limit
    ";
    $results = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pencarian Nomor Polisi</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 4px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Cari Nomor Polisi</h2>
    <form method="post">
        <input type="text" name="no_polisi" placeholder="Masukkan No Polisi" id="no_polisi_input">
        <button type="submit" name="search">Cari</button>
        <button type="submit" name="search_all" id="btn_search_all">Cari Semua</button>
    </form>

    <?php if ($results && $results->num_rows > 0): ?>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="select_all" onclick="toggleSelectAll(this)"></th>
                    <th>ID</th>
                    <th>No Polisi</th>
                    <th>No Polisi Format Baru</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results->fetch_assoc()): ?>
                <?php $formatted = format_polisi($row['no_polisi']); ?>
                <tr>
                    <td><input type="checkbox" name="check_list[<?= $row['id_identitas_kendaraan'] ?>]" value="<?= $row['no_polisi'] ?>"></td>
                    <td><?= $row['id_identitas_kendaraan'] ?></td>
                    <td><?= $row['no_polisi'] ?></td>
                    <td><?= $formatted ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <button type="submit" name="update_all">Update All</button>
    </form>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <p>Tidak ditemukan data.</p>
    <?php endif; ?>

    <script>
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="check_list"]');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }

        // Hapus required jika tombol "Cari Semua" diklik
        document.getElementById('btn_search_all').addEventListener('click', function () {
            document.getElementById('no_polisi_input').removeAttribute('required');
        });
    </script>
</body>
</html>
