<?php

// insert data report
function buat_pengaduan($user_id, $message, $image, $conn) {
    // Prepare SQL Injection => mencegah pihak luar memanipulasi data yang ada pada database
    $stmt = $conn->prepare("INSERT INTO reports (user_id, message, image, status, created_at) VALUES (?, ?, ?, ?, NOW())");

    // memberikan defautl value kepada parameter status
    $status = 'proses';

    // menghubungkan argumen dengan query
    $stmt->bind_param("ssss", $user_id, $message, $image, $status);

    // eksekusi, menyimpan dalam data base
    return $stmt->execute();
}


// Mendapatkan data report sesuai id_user, sesuai status
function get_pengaduan_by_status($username, $status, $conn) {
    // mendapatkan id user
    $query_id = "SELECT id FROM users WHERE username = '$username'";
    $result_id = mysqli_query($conn, $query_id);
    $row = mysqli_fetch_assoc($result_id);
    $id = $row['id'];

    // mengambil data laporan sesuai status
    $query = "SELECT * FROM reports WHERE user_id = '$id' AND status ='$status'";
    $result = mysqli_query($conn, $query);

    // menyuimpan data yang sudah didapatkan
    $pengaduan = [];
    while($row = mysqli_fetch_assoc($result)) {
        $pengaduan[] = $row;
    }

    return $pengaduan;
}

// mendapatkan data sesuai status
function get_all_pengaduan_by_status($status, $conn) {
    // membuat query/permintaan
    $query = "SELECT * FROM reports WHERE status = ?";

    // prepared statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s",$status);

    // eksekusi
    $stmt->execute();

    // mengambil hasil query
    $result = $stmt->get_result();

    $pengaduan = [];
    while($row = mysqli_fetch_assoc($result)) {
        $pengaduan[] = $row;
    }

    return $pengaduan;

}


// menambahkan feedback
function addFeedback($report_id, $petugas_id, $feedback, $conn) {
    // kita melakukan 2 operasi sekaligus
    // 1. Menambahkan Feedback
    // 2. Mengubah status report yang tadinya proses menjadi selesai

    $conn->begin_transaction();

    // menyiapkan query untuk menambahkan feedback pada database
    try {
        $stmt = $conn->prepare("INSERT INTO feedbacks(report_id, feedback, petugas_id, created_at) VALUES (?,?,?, NOW())");
        $stmt->bind_param("isi", $report_id, $feedback, $petugas_id);

        // eksekusi Query
        if(!$stmt->execute()) {
            throw new Exception("Gagal menyimpan feedback");
        }

        // Mengubah status report
        $updateStmt = $conn->prepare("UPDATE reports SET status = 'selesai' WHERE id = ?");
        $updateStmt->bind_param("i", $report_id);

        // eksekusi Query
        if(!$updateStmt->execute()) {
            throw new Exception("Gagal update status report");
        }

        // jika semua operasi berhasil
        $conn->commit();  //menyimpan di database
        return true;   //operasi yang kita lakukan berhasil

    } catch (Throwable $error) {
        //ini kalau operasinya gagal
        $conn->rollback();   //membatalkan semua perubahan yang telah dilakukan
        echo "Error: " . $error->getMessage();
        return false;
    }
}

// get reports with feedbacks by status selesai


// get reports with feedback by user and by status 
?>