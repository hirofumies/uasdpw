<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$message = "";
$mahasiswa_id = null;

// Tidak ada auto-fix lagi, cukup tampilkan yang aman

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Proses form mahasiswa - UPDATED ke mahasiswa_v2
if (isset($_POST['action']) && $_POST['action'] == 'register' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = clean_input($_POST["nama"]);
    $nim = clean_input($_POST["nim"]);
    $kelas = clean_input($_POST["kelas"]);
    $prodi = clean_input($_POST["prodi"]);
    
    if (!empty($nim) && !empty($nama) && !empty($kelas) && !empty($prodi)) {
        try {
            // Updated query ke mahasiswa_v2
            $stmt = $pdo->prepare("INSERT INTO mahasiswa_v2 (nim, nama, kelas, prodi, created_at, updated_at) VALUES (?, ?, ?, ?, CURDATE(), CURDATE())");
            $stmt->execute([$nim, $nama, $kelas, $prodi]);
            
            $mahasiswa_id = $pdo->lastInsertId();
            $_SESSION['mahasiswa_id'] = $mahasiswa_id;
            $_SESSION['nama'] = $nama;
            $_SESSION['nim'] = $nim;
            $_SESSION['kelas'] = $kelas;
            $_SESSION['prodi'] = $prodi;
            
            $message = "Data mahasiswa berhasil disimpan!";
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Semua field harus diisi!";
    }
}

// Proses upload file
if (isset($_POST['action']) && $_POST['action'] == 'upload' && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['mahasiswa_id']) && isset($_FILES["fileToUpload"])) {
        $message = uploadFile($_FILES["fileToUpload"], $_SESSION['nim'], $_SESSION['nama'], $_SESSION['kelas'], $_SESSION['prodi'], $pdo);
    } else {
        $message = "Silakan daftar terlebih dahulu sebelum upload file.";
    }
}

// Ambil data uploads untuk ditampilkan - UPDATED ke uploads_v2
$uploads = [];
if (isset($_SESSION['mahasiswa_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM uploads_v2 WHERE mahasiswa_id = ? ORDER BY tanggal_upload DESC");
    $stmt->execute([$_SESSION['mahasiswa_id']]);
    $uploads = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Dinamis - <?php echo isset($_SESSION['nim']) ? $_SESSION['nim'] : 'Portal'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🎓 Portal Upload Tugas Mahasiswa</h1>
            <p>Form Tugas Digital by Marsyah Nur Apriani</p>
            <?php if (isset($_SESSION['nim'])): ?>
                <div class="header-info">
                    <span>NIM: <?php echo $_SESSION['nim']; ?> 𓇻 Kelas: <?php echo $_SESSION['kelas']; ?> 𓇻 Prodi: <?php echo $_SESSION['prodi']; ?></span>
                </div>
            <?php endif; ?>
        </header>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['mahasiswa_id'])): ?>
        <!-- Form Registrasi Mahasiswa -->
        <div class="form-section">
            <h2>📝 Data Mahasiswa</h2>
            <p>Silakan isi data diri terlebih dahulu untuk dapat mengakses.</p>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap:</label>
                    <input type="text" id="nama" name="nama" required placeholder="Nama lengkap mahasiswa" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="nim">NIM:</label>
                    <input type="text" id="nim" name="nim" required placeholder="Contoh: 231001001" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="kelas">Kelas:</label>
                    <select id="kelas" name="kelas" required>
                        <option value="">Pilih Kelas</option>
                        <option value="A">Kelas A</option>
                        <option value="B">Kelas B</option>
                        <option value="C">Kelas C</option>
                        <option value="D">Kelas D</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prodi">Program Studi:</label>
                    <select id="prodi" name="prodi" required>
                        <option value="">Pilih Prodi</option>
                        <option value="Informatika">Informatika</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 Simpan Data & Masuk</button>
            </form>
        </div>
        <?php else: ?>
        
        <!-- Info Mahasiswa -->
        <div class="info-section">
            <h2>🪪 Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h2>
            <div class="student-info">
                <p><strong>NIM:</strong> <?php echo $_SESSION['nim']; ?></p>
                <p><strong>Kelas:</strong> <?php echo $_SESSION['kelas']; ?></p>
                <p><strong>Program Studi:</strong> <?php echo $_SESSION['prodi']; ?></p>
            </div>
            <p>Anda dapat mengupload tugas di bawah ini. File yang diupload akan tersimpan dengan aman.</p>
            <a href="?logout=true" class="btn btn-secondary">🚪 Logout</a>
        </div>

        <!-- Form Upload File -->
        <div class="form-section">
            <h2>📤 Upload Tugas</h2>
            <p>Pilih file tugas yang akan diupload. Pastikan file sesuai dengan format yang diperbolehkan.</p>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                
                <div class="form-group">
                    <label for="fileToUpload">Pilih File Tugas:</label>
                    <input type="file" id="fileToUpload" name="fileToUpload" required 
                           accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                    <small>Format yang diperbolehkan: PDF, DOC, DOCX, TXT, JPG, PNG (Maksimal 5MB)</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Upload File</button>
            </form>
        </div>

        <!-- Daftar File yang Sudah Diupload -->
        <?php if (!empty($uploads)): ?>
        <div class="uploads-section">
            <h2>🗂️ File yang Sudah Diupload</h2>
            <p>Total file: <?php echo count($uploads); ?> file</p>
            
            <div class="uploads-list">
                <?php foreach ($uploads as $upload): ?>
                <div class="upload-item">
                    <div class="file-info">
                        <strong>📄 <?php echo htmlspecialchars($upload['file_asli']); ?></strong>
                        <small>Tipe: <?php echo strtoupper($upload['tipe_file']); ?></small>
                        <small>Ukuran: <?php echo number_format($upload['ukuran_file']/1024, 2); ?> KB</small>
                        <small>Tanggal Upload: 
                        <?php 
                        $tanggal = $upload['tanggal_upload'];
                        if (!empty($tanggal) && $tanggal != '0000-00-00 00:00:00' && $tanggal != '0000-00-00') {
                            $timestamp = strtotime($tanggal);
                            if ($timestamp !== false && $timestamp > 0) {
                                echo date('d/m/Y H:i', $timestamp);
                            } else {
                                echo 'Tanggal tidak valid';
                            }
                        } else {
                            echo 'Tanggal tidak tersedia';
                        }
                        ?>
                        </small>
                    </div>
                    <div class="file-actions">
                        <a href="uploads/<?php echo htmlspecialchars($upload['nama_file']); ?>" 
                           target="_blank" class="btn btn-small btn-view">Lihat</a>
                        <a href="uploads/<?php echo htmlspecialchars($upload['nama_file']); ?>" 
                           download="<?php echo htmlspecialchars($upload['file_asli']); ?>" 
                           class="btn btn-small btn-download">📥 Download</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <?php if (isset($_SESSION['mahasiswa_id'])): ?>
        <div class="uploads-section">
            <h2>🗂️ File yang sudah Diupload</h2>
            <div class="no-files">
                <p>📭 Belum ada file yang diupload. Silakan upload tugas Anda di atas.</p>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php endif; ?>

        <footer>
            <p>&copy; 2025 - Project Desain dan Pemrograman Web</p>
            <p>Mendukung: Array, Form Processing, File Upload, Session Management, Database MySQL</p>
            <p class="waktu">Waktu akses: Rabu, 30 Juli 2025 pukul 09:59:05</p>
        </footer>
    </div>

    <script src="js/script.js"></script>

<script src="https://cdn.jsdelivr.net/npm/twemoji@14.0.2/dist/twemoji.min.js"></script>
<script>
  window.addEventListener('load', function() {
    console.log('Starting Twemoji...');
    
    setTimeout(function() {
      twemoji.parse(document.body, {
        folder: 'svg',
        ext: '.svg',
        base: 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/'
      });
      console.log('Twemoji completed!');
    }, 100);
  });
</script>

</body>
</html>
