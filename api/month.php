<?php

require_once('db.php');

try {
    $object = new stdClass();
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // ตรวจสอบ query parameter สำหรับเดือน
        $month = isset($_GET['month']) ? $_GET['month'] : null;

        // สร้างเงื่อนไขสำหรับค้นหาตามเดือน (ถ้ามีการระบุเดือน)
        $condition = '';
        $params = [];
        if ($month) {
            // เพิ่มเงื่อนไขใน SQL WHERE clause สำหรับค้นหาเดือน
            $condition = 'WHERE MONTH(expense_date) = :month';
            $params[':month'] = $month;
        }

        // สร้างคำสั่ง SQL สำหรับการดึงข้อมูลรายรับ/รายจ่าย
        $sql = "SELECT *, 
                    SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) AS total_income,
                    SUM(CASE WHEN type = 'expense'  THEN amount ELSE 0 END) AS total_expense
                FROM expenses 
                $condition";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute($params)) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $object->Result = [
                    'total_income' => floatval($row['total_income']),
                    'total_expense' => floatval($row['total_expense']),
                    'month' => $month,
                ];
                $object->RespCode = 200;
                $object->RespMessage = 'success';
                http_response_code(200);
            } else {
                $object->RespCode = 404;
                $object->RespMessage = 'No records found for the specified month';
                http_response_code(404);
            }
        } else {
            $object->RespCode = 500;
            $object->RespMessage = 'Error fetching data';
            http_response_code(500);
        }

        echo json_encode($object);
    } else {
        http_response_code(405);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>