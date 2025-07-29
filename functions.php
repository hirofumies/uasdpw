<?php
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateStatus($status) {
    $allowed_status = ['active', 'deleted'];
    return in_array($status, $allowed_status) ? $status : 'active';
}

function registerMahasiswa($nim, $nama, $kelas, $prodi, $pdo) {
    $check_stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
    $check_stmt->execute([$nim]);
    
    if ($check_stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, kelas, prodi, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([clean_input($nim), clean_input($nama), clean_input($kelas), clean_input($prodi)]);
        return $pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare("UPDATE mahasiswa SET nama = ?, kelas = ?, prodi = ?, updated_at = NOW() WHERE nim = ?");
        $stmt->execute([clean_input($nama), clean_input($kelas), clean_input($prodi), clean_input($nim)]);
        return $check_stmt->fetch()['id'];
    }
}

function uploadFile($file, $nim, $nama, $kelas, $prodi, $pdo) {
    $target_dir = "uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("pdf", "doc", "docx", "txt", "jpg", "png");
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return "Format file tidak diizinkan.";
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        try {
            $pdo->beginTransaction();
            
            $mahasiswa_id = registerMahasiswa($nim, $nama, $kelas, $prodi, $pdo);
            
            $status = validateStatus('active');
            $stmt = $pdo->prepare("INSERT INTO uploads (mahasiswa_id, nama_file, file_asli, ukuran_file, status, tanggal_upload) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$mahasiswa_id, $new_filename, $file["name"], $file["size"], $status]);
            
            $pdo->commit();
            return "File berhasil diupload.";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            return "Gagal menyimpan data: " . $e->getMessage();
        }
    } else {
        return "Upload file gagal.";
    }
}
?>