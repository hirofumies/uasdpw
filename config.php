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
    
    $test = $pdo->query("SELECT COUNT(*) as count FROM mahasiswa_v2");
    $result = $test->fetch();
    echo "<!-- Current mahasiswa_v2 count: " . $result['count'] . " -->\n";
    
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
