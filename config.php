<?php
$servername = $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal';
$username = $_ENV['MYSQLUSER'] ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? 'f1igdGyHoRgGzOZVOVORGudfjTMKTDRR';
$dbname = $_ENV['MYSQLDATABASE'] ?? 'railway';
$port = $_ENV['MYSQLPORT'] ?? '3306';

echo "<!-- Debug Info:\n";
echo "Host: $servername\n";
echo "User: $username\n";
echo "Database: $dbname\n";
echo "Port: $port\n";
echo "-->\n";

try {
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<!-- Database connected successfully -->\n";
    
    // Drop old tables and create new ones with correct structure
    $pdo->exec("DROP TABLE IF EXISTS uploads");
    $pdo->exec("DROP TABLE IF EXISTS mahasiswa");
    
    $createMahasiswaTable = "
    CREATE TABLE mahasiswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nim VARCHAR(50) NOT NULL,
        nama TEXT NOT NULL,
        kelas VARCHAR(20) NOT NULL,
        prodi VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_nim (nim)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $createUploadsTable = "
    CREATE TABLE uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mahasiswa_id INT NOT NULL,
        nama_file VARCHAR(255) NOT NULL,
        file_asli VARCHAR(255) NOT NULL,
        ukuran_file INT NOT NULL,
        status ENUM('active', 'deleted') DEFAULT 'active',
        tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mahasiswa_id (mahasiswa_id),
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createMahasiswaTable);
    $pdo->exec($createUploadsTable);
    echo "<!-- Tables created/verified successfully -->\n";
    
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
