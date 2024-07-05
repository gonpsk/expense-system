<?php
$db_host = 'localhost';
$db_name = 'expense_tracker';
$db_user = 'root';
$db_pass = '';

// ใส่ไว้เพื่อเวลาphp return กลับมาฝั่งหน้าบ้าน ถ้าไม่ใส่มันจะแปลง json กลับเป็น string 
header('Content-Type: application/json');
date_default_timezone_set("Asia/Bangkok");

try {
    // pdo php data object สร้างการเชื่อมต่อฐานข้อมูล
$db = new PDO("mysql:host=${db_host}; dbname=${db_name}",$db_user, $db_pass);
//  ATTR_ERRMODE เป็นค่าคงที่ (constant) ใน PHP PDO ซึ่งใช้กำหนดโหมดการจัดการข้อผิดพลาดในการเชื่อมต่อฐานข้อมูลด้วย PDO
$db->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
// ใส่แล้วโค้ดพัง
//  echo "database is connected";     
} 
catch(PDOException $e) {
    echo $e->getMessage();
 }
 
?>