<?php
function uploadFile($file, $nim, $nama, $kelas, $prodi, $pdo) {
    $target_dir = "uploads/";
    $uploadOk = 1;
    $message = "";
    
    // Pastikan direktori uploads ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
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
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            try {
                // Ambil mahasiswa_id dari session atau query
                $stmt = $pdo->prepare("SELECT id FROM mahasiswa_v2 WHERE nim = ?");
                $stmt->execute([$nim]);
                $mahasiswa = $stmt->fetch();
                
                if (!$mahasiswa) {
                    return "Error: Data mahasiswa tidak ditemukan.";
                }
                
                $mahasiswa_id = $mahasiswa['id'];
                
                // Simpan ke database - TANPA tanggal_upload untuk menghindari error
                $stmt = $pdo->prepare("INSERT INTO uploads_v2 (mahasiswa_id, file_asli, nama_file, tipe_file, ukuran_file) VALUES (?, ?, ?, ?, ?)");
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
                unlink($target_file);
                $message = "Error database: " . $e->getMessage();
            }
        } else {
            $message = "Error: Gagal mengupload file.";
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
?>
