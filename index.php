<?php

include 'koneksi.php';

$pesan_sukses = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pelapor   = $conn->real_escape_string($_POST['nama_pelapor']);
    $nama_fasilitas = $conn->real_escape_string($_POST['nama_fasilitas']);
    $lokasi         = $conn->real_escape_string($_POST['lokasi']);
    $id_kategori    = (int) $_POST['id_kategori'];
    $deskripsi      = $conn->real_escape_string($_POST['deskripsi']);
    $prioritas      = $conn->real_escape_string($_POST['prioritas']);
    $status         = "Menunggu"; // Default sesuai ENUM di database

    // Kolom tanggal_laporan menggunakan DEFAULT CURRENT_TIMESTAMP di database[cite: 4], 
    // namun bisa juga disisipkan secara manual jika diinginkan.
    $sql_insert = "INSERT INTO laporan_kerusakan (nama_pelapor, nama_fasilitas, lokasi, id_kategori, deskripsi, prioritas, status) 
                   VALUES ('$nama_pelapor', '$nama_fasilitas', '$lokasi', $id_kategori, '$deskripsi', '$prioritas', '$status')";

    if ($conn->query($sql_insert) === TRUE) {
        $pesan_sukses = "Laporan berhasil dikirim!";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }
}


$sql_select = "SELECT laporan_kerusakan.*, kategori_fasilitas.nama_kategori 
               FROM laporan_kerusakan 
               JOIN kategori_fasilitas ON laporan_kerusakan.id_kategori = kategori_fasilitas.id_kategori 
               ORDER BY laporan_kerusakan.id_laporan DESC";

$result = $conn->query($sql_select);


if (!$result) {
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">


    <header class="bg-primary text-white py-4 mb-4 shadow-sm">
        <div class="container">
            <h1 class="h3">Sistem Pelaporan Kerusakan Fasilitas Sekolah</h1>
            <p class="lead fs-6 mb-2">Catat dan pantau laporan kerusakan fasilitas sekolah.</p>
            <nav aria-label="Navigasi utama" >
                <a href="#form-laporan" class="btn btn-light btn-sm text-primary fw-semibold">Buat Laporan</a>
                <a href="#daftar-laporan" class="btn btn-outline-light btn-sm">Daftar Laporan</a>
            </nav>
        </div>
    </header>

    <main class="container mb-5">
        
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success" role="alert">
                <?= $pesan_sukses; ?>
            </div>
        <?php endif; ?>

        <section id="form-laporan" class="card shadow-sm mb-5">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Form Pelaporan Kerusakan</h2>
                <p class="text-muted small">Lengkapi data berikut untuk melaporkan fasilitas yang rusak.</p>

                <form action="" method="post">
                    <fieldset>
                        <legend class="fs-6 fw-bold text-secondary mb-3">Data Pelaporan</legend>

                        <div class="mb-3">
                            <label for="nama_pelapor" class="form-label">Nama pelapor</label>
                            <input type="text" id="nama_pelapor" name="nama_pelapor" maxlength="100" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama_fasilitas" class="form-label">Nama fasilitas</label>
                            <input type="text" id="nama_fasilitas" name="nama_fasilitas" maxlength="100" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi kerusakan</label>
                            <input type="text" id="lokasi" name="lokasi" maxlength="100" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_kategori" class="form-label">Kategori fasilitas</label>
                            <select id="id_kategori" name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih kategori --</option>
                                <option value="1">Elektronik</option>
                                <option value="2">Furnitur</option>
                                <option value="3">Jaringan</option>
                                <option value="4">Peralatan Kelas</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi kerusakan</label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" class="form-control" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="prioritas" class="form-label">Prioritas</label>
                            <select id="prioritas" name="prioritas" class="form-select" required>
                                <option value="">-- Pilih prioritas --</option>
                                <option value="Rendah">Rendah</option>
                                <option value="Sedang">Sedang</option>
                                <option value="Tinggi">Tinggi</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Kirim Laporan</button>
                    </fieldset>
                </form>
            </div>
        </section>

        <section id="daftar-laporan" class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Daftar Laporan Kerusakan</h2>
                <p class="text-muted small">Seluruh laporan yang tercatat pada database ditampilkan di bagian ini.</p>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Fasilitas</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Lokasi</th>
                                <th scope="col">Pelapor</th>
                                <th scope="col">Prioritas</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['nama_fasilitas']); ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['deskripsi']); ?></small>
                                        </td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_kategori']); ?></span></td>
                                        <td><?= htmlspecialchars($row['lokasi']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelapor']); ?></td>
                                        <td>
                                            <?php 
                                                $badgeClass = match($row['prioritas']) {
                                                    'Tinggi' => 'bg-danger',
                                                    'Sedang' => 'bg-warning text-dark',
                                                    default => 'bg-success'
                                                };
                                            ?>
                                            <span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars($row['prioritas']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($row['status']); ?></span>
                                        </td>
                                        <td><small><?= htmlspecialchars($row['tanggal_laporan']); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data ditampilkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white text-center py-4 border-top text-muted small">
        <div class="container">
            <p class="mb-0">&copy; 2024 Sistem Pelaporan Kerusakan Fasilitas Sekolah.</p>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php

$conn->close();
?>