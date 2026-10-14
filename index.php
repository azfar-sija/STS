<?php
// ====== KONEKSI DATABASE (objek mysqli $conn dari koneksi.php) ======
mysqli_report(MYSQLI_REPORT_OFF); // agar error ditangani lewat pesan di bawah, bukan exception
require_once 'koneksi.php';
$conn->set_charset('utf8mb4');

$error_koneksi = '';
$pesan = '';
$galat = '';

// ====== CREATE (bonus): simpan laporan baru ======
if (!$error_koneksi && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelapor   = trim($_POST['nama_pelapor'] ?? '');
    $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
    $lokasi         = trim($_POST['lokasi'] ?? '');
    $id_kategori    = (int)($_POST['id_kategori'] ?? 0);
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $prioritas      = $_POST['prioritas'] ?? '';

    $prioritas_valid = ['Rendah', 'Sedang', 'Tinggi'];

    if ($nama_pelapor === '' || $nama_fasilitas === '' || $lokasi === '' || $id_kategori < 1 || $deskripsi === '' || !in_array($prioritas, $prioritas_valid, true)) {
        $galat = 'Semua kolom wajib diisi dengan benar.';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO laporan_kerusakan (nama_pelapor, nama_fasilitas, lokasi, id_kategori, deskripsi, prioritas)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssiss', $nama_pelapor, $nama_fasilitas, $lokasi, $id_kategori, $deskripsi, $prioritas);

        if ($stmt->execute()) {
            // Redirect agar refresh halaman tidak mengirim ulang form
            header('Location: index.php?simpan=ok#daftar-laporan');
            exit;
        }
        $galat = 'Laporan gagal disimpan: ' . $stmt->error;
        $stmt->close();
    }
}

if (isset($_GET['simpan']) && $_GET['simpan'] === 'ok') {
    $pesan = 'Laporan berhasil disimpan.';
}

// ====== READ: ambil data laporan ======
$laporan = [];
$galat_query = '';
if (!$error_koneksi) {
    $hasil = $conn->query(
        'SELECT id_laporan, nama_pelapor, nama_fasilitas, lokasi, prioritas, status, tanggal_laporan
         FROM laporan_kerusakan
         ORDER BY tanggal_laporan DESC, id_laporan DESC'
    );
    if ($hasil) {
        while ($baris = $hasil->fetch_assoc()) {
            $laporan[] = $baris;
        }
    } else {
        $galat_query = 'Query gagal: ' . $conn->error;
    }

    $kategori = [];
    $hasil_kategori = $conn->query('SELECT id_kategori, nama_kategori FROM kategori_fasilitas ORDER BY id_kategori');
    if ($hasil_kategori) {
        while ($k = $hasil_kategori->fetch_assoc()) {
            $kategori[(int)$k['id_kategori']] = $k['nama_kategori'];
        }
    }
}

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>
    <script src="https://cdn.tailwindcss.com/"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6">
            <h1 class="text-xl font-semibold">Sistem Pelaporan Kerusakan Fasilitas Sekolah</h1>
            <p class="mt-1 text-sm text-gray-500">Catat dan pantau laporan kerusakan fasilitas sekolah.</p>
            <nav aria-label="Navigasi utama" class="mt-4 flex gap-4 text-sm">
                <a href="#form-laporan" class="underline-offset-4 hover:underline">Buat Laporan</a>
                <a href="#daftar-laporan" class="underline-offset-4 hover:underline">Daftar Laporan</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-10 px-4 py-8">

        <?php if ($error_koneksi): ?>
            <div class="rounded border border-gray-300 bg-white px-4 py-3 text-sm"><?= e($error_koneksi) ?></div>
        <?php endif; ?>

        <section id="form-laporan" aria-labelledby="judul-form">
            <h2 id="judul-form" class="text-lg font-semibold">Form Pelaporan Kerusakan</h2>
            <p class="mt-1 text-sm text-gray-500">Lengkapi data berikut untuk melaporkan fasilitas yang rusak.</p>

            <?php if ($pesan): ?>
                <p class="mt-4 rounded border border-gray-300 bg-white px-4 py-2 text-sm"><?= e($pesan) ?></p>
            <?php endif; ?>
            <?php if ($galat): ?>
                <p class="mt-4 rounded border border-gray-800 bg-white px-4 py-2 text-sm"><?= e($galat) ?></p>
            <?php endif; ?>

            <form action="index.php#form-laporan" method="post" class="mt-4 rounded border border-gray-200 bg-white p-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nama_pelapor" class="mb-1 block text-sm font-medium">Nama pelapor</label>
                        <input type="text" id="nama_pelapor" name="nama_pelapor" maxlength="100" required
                               value="<?= e($_POST['nama_pelapor'] ?? '') ?>"
                               class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-800 focus:outline-none">
                    </div>

                    <div>
                        <label for="nama_fasilitas" class="mb-1 block text-sm font-medium">Nama fasilitas</label>
                        <input type="text" id="nama_fasilitas" name="nama_fasilitas" maxlength="100" required
                               value="<?= e($_POST['nama_fasilitas'] ?? '') ?>"
                               class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-800 focus:outline-none">
                    </div>

                    <div>
                        <label for="lokasi" class="mb-1 block text-sm font-medium">Lokasi kerusakan</label>
                        <input type="text" id="lokasi" name="lokasi" maxlength="100" required
                               value="<?= e($_POST['lokasi'] ?? '') ?>"
                               class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-800 focus:outline-none">
                    </div>

                    <div>
                        <label for="id_kategori" class="mb-1 block text-sm font-medium">Kategori fasilitas</label>
                        <select id="id_kategori" name="id_kategori" required
                                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gray-800 focus:outline-none">
                            <option value="">-- Pilih kategori --</option>
                            <?php
                            foreach ($kategori as $id => $nama):
                                $dipilih = ((int)($_POST['id_kategori'] ?? 0) === $id) ? 'selected' : '';
                            ?>
                                <option value="<?= $id ?>" <?= $dipilih ?>><?= e($nama) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="deskripsi" class="mb-1 block text-sm font-medium">Deskripsi kerusakan</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" required
                                  class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-800 focus:outline-none"><?= e($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label for="prioritas" class="mb-1 block text-sm font-medium">Prioritas</label>
                        <select id="prioritas" name="prioritas" required
                                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gray-800 focus:outline-none">
                            <option value="">-- Pilih prioritas --</option>
                            <?php foreach (['Rendah', 'Sedang', 'Tinggi'] as $p):
                                $dipilih = (($_POST['prioritas'] ?? '') === $p) ? 'selected' : '';
                            ?>
                                <option value="<?= $p ?>" <?= $dipilih ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </section>

        <section id="daftar-laporan" aria-labelledby="judul-daftar">
            <h2 id="judul-daftar" class="text-lg font-semibold">Daftar Laporan Kerusakan</h2>
            <p class="mt-1 text-sm text-gray-500">Seluruh laporan yang tercatat pada database ditampilkan di bagian ini.</p>

            <?php if ($galat_query): ?>
                <p class="mt-4 text-sm"><?= e($galat_query) ?></p>
            <?php endif; ?>

            <div class="mt-4 overflow-x-auto rounded border border-gray-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-100 text-gray-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">No.</th>
                            <th scope="col" class="px-4 py-3 font-medium">Fasilitas</th>
                            <th scope="col" class="px-4 py-3 font-medium">Lokasi</th>
                            <th scope="col" class="px-4 py-3 font-medium">Pelapor</th>
                            <th scope="col" class="px-4 py-3 font-medium">Prioritas</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 font-medium">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($laporan)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada laporan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporan as $i => $row):
                                $tanggal = date('d/m/Y H:i', strtotime($row['tanggal_laporan']));
                                $prioritas_baris = $row['prioritas'] ?? '';
                                $kelas_prioritas = $prioritas_baris === 'Tinggi' ? 'font-semibold text-gray-900' : 'text-gray-600';
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3"><?= $i + 1 ?></td>
                                    <td class="px-4 py-3"><?= e($row['nama_fasilitas'] ?? '') ?></td>
                                    <td class="px-4 py-3"><?= e($row['lokasi'] ?? '') ?></td>
                                    <td class="px-4 py-3"><?= e($row['nama_pelapor'] ?? '') ?></td>
                                    <td class="px-4 py-3 <?= $kelas_prioritas ?>"><?= e($prioritas_baris) ?></td>
                                    <td class="px-4 py-3"><?= e($row['status'] ?? '') ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?= e($tanggal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <p class="mx-auto max-w-5xl px-4 py-4 text-xs text-gray-500">&copy; 2024 Sistem Pelaporan Kerusakan Fasilitas Sekolah.</p>
    </footer>
</body>
</html>