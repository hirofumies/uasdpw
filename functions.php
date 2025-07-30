<?php
function uploadFile($file, $nim, $nama, $kelas, $prodi, $pdo) {
    $target_dir = "uploads/";
    $uploadOk = 1;
    $message = "";
    
    // Periksa apakah direktori uploads ada dan dapat ditulis
    if (!file_exists($target_dir)) {
        return "Error: Direktori uploads tidak ditemukan. Silakan buat direktori 'uploads' secara manual di server.";
    }
    
    // Periksa apakah direktori dapat ditulis
    if (!is_writable($target_dir)) {
        return "Error: Direktori uploads tidak dapat ditulis. Periksa permission direktori (chmod 755 uploads/).";
    }
    
    // Validasi file upload
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return "Error: File tidak berhasil diupload. Error code: " . ($file['error'] ?? 'undefined');
    }
    
    $file_asli = $file["name"];
    $imageFileType = strtolower(pathinfo($file_asli, PATHINFO_EXTENSION));
    
    // Generate nama file unik
    $nama_file = $nim . "_" . date("YmdHis") . "." . $imageFileType;
    $target_file = $target_dir . $nama_file;
    
    // Cek ukuran file (maksimal 5MB)
    if ($file["size"] > 5000000) {
        $message = "Error: File terlalu besar. Maksimal 5MB.";
        $uploadOk = 0;
    }
    
    // Format file yang diperbolehkan
    $allowed_types = array("pdf", "doc", "docx", "txt", "jpg", "jpeg", "png");
    if (!in_array($imageFileType, $allowed_types)) {
        $message = "Error: Format file tidak diperbolehkan. Gunakan: PDF, DOC, DOCX, TXT, JPG, PNG";
        $uploadOk = 0;
    }
    
    // Cek jika file sudah ada
    if (file_exists($target_file)) {
        $message = "Error: File dengan nama tersebut sudah ada.";
        $uploadOk = 0;
    }
    
    if ($uploadOk == 0) {
        return $message;
    } else {
        // Periksa apakah file temporary ada
        if (!file_exists($file["tmp_name"])) {
            return "Error: File temporary tidak ditemukan. Coba upload ulang.";
        }
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            try {
                // Ambil mahasiswa_id dari session atau query
                $stmt = $pdo->prepare("SELECT id FROM mahasiswa_v2 WHERE nim = ?");
                $stmt->execute([$nim]);
                $mahasiswa = $stmt->fetch();
                
                if (!$mahasiswa) {
                    // Hapus file jika data mahasiswa tidak ditemukan
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                    return "Error: Data mahasiswa tidak ditemukan.";
                }
                
                $mahasiswa_id = $mahasiswa['id'];
                
                // Simpan ke database - pastikan kolom sesuai dengan struktur tabel
                $stmt = $pdo->prepare("INSERT INTO uploads_v2 (mahasiswa_id, file_asli, nama_file, tipe_file, ukuran_file, tanggal_upload) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $mahasiswa_id,
                    $file_asli,
                    $nama_file,
                    $imageFileType,
                    $file["size"]
                ]);
                
                $message = "File berhasil diupload: " . $file_asli;
            } catch(PDOException $e) {
                // Hapus file jika gagal simpan ke database
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                $message = "Error database: " . $e->getMessage();
            }
        } else {
            $message = "Error: Gagal mengupload file. Periksa permission direktori uploads.";
        }
    }
    
    return $message;
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk mengecek direktori upload (tanpa membuat otomatis)
function ensureUploadDirectory() {
    $upload_dir = "uploads/";
    
    // Hanya cek apakah direktori ada, tidak membuat otomatis
    if (!file_exists($upload_dir)) {
        error_log("Upload directory does not exist: " . $upload_dir);
        return false;
    }
    
    // Cek apakah direktori dapat ditulis
    if (!is_writable($upload_dir)) {
        error_log("Upload directory is not writable: " . $upload_dir);
        return false;
    }
    
    // Buat file .htaccess untuk keamanan jika memungkinkan
    $htaccess_file = $upload_dir . ".htaccess";
    if (!file_exists($htaccess_file) && is_writable($upload_dir)) {
        $htaccess_content = "Options -Indexes\n";
        $htaccess_content .= "Options -ExecCGI\n";
        $htaccess_content .= "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n";
        @file_put_contents($htaccess_file, $htaccess_content);
    }
    
    return true;
}
?>
