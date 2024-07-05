<?php
require_once('db.php');


try {
    $object = new stdClass();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // รับข้อมูลจาก client ในรูปแบบ JSON และแปลงเป็น associative array
        $input = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบว่า input ครบถ้วนหรือไม่
        if (!isset($input['type'], $input['name'], $input['amount'], $input['expense_date'])) {
            $object->RespCode = 400;
            $object->RespMessage = 'bad : Missing required fields';
            http_response_code(400);
            echo json_encode($object);
            exit;
        }

        // เพิ่มรายการรายรับ/รายจ่าย
        $stmt = $db->prepare("INSERT INTO expenses (type, name, amount, expense_date) VALUES (:type, :name, :amount, :expense_date)");
        if ($stmt->execute([
            ':type' => $input['type'],
            ':name' => $input['name'],
            ':amount' => $input['amount'],
            ':expense_date' => $input['expense_date']
        ])) {
            $object->RespCode = 200;
            $object->RespMessage = 'success';
            $object->id = $db->lastInsertId();
            http_response_code(200);
        } else {
            $object->RespCode = 500;
            $object->RespMessage = 'bad : Insert failed';
            http_response_code(500);
        }

        echo json_encode($object);
    } else {
        http_response_code(405);
    } 


    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        // รับข้อมูลจาก client ในรูปแบบ JSON และแปลงเป็น associative array
        $input = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบว่ามี id ที่ต้องการลบหรือไม่
        if (!isset($input['id'])) {
            $object->RespCode = 400;
            $object->RespMessage = 'bad : Missing id parameter';
            http_response_code(400);
            echo json_encode($object);
            exit;
        }

        // ลบรายการรายรับ/รายจ่าย
        $stmt = $db->prepare("DELETE FROM expenses WHERE id = :id");
        if ($stmt->execute([':id' => $input['id']])) {
            $object->RespCode = 200;
            $object->RespMessage = 'success';
            http_response_code(200);
        } else {
            $object->RespCode = 500;
            $object->RespMessage = 'bad : Delete failed';
            http_response_code(500);
        }

        echo json_encode($object);
    } else {
        http_response_code(405);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        // รับข้อมูลจาก client ในรูปแบบ JSON และแปลงเป็น associative array
        $input = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบว่ามีข้อมูลที่ต้องการปรับปรุงครบถ้วนหรือไม่
        if (!isset($input['id'], $input['type'], $input['name'], $input['amount'], $input['expense_date'])) {
            $object->RespCode = 400;
            $object->RespMessage = 'bad : Missing required fields';
            http_response_code(400);
            echo json_encode($object);
            exit;
        }

        // ปรับปรุงรายการรายรับ/รายจ่าย
        $stmt = $db->prepare("UPDATE expenses SET type = :type, name = :name, amount = :amount, expense_date = :expense_date WHERE id = :id");
        if ($stmt->execute([
            ':type' => $input['type'],
            ':name' => $input['name'],
            ':amount' => $input['amount'],
            ':expense_date' => $input['expense_date'],
            ':id' => $input['id']
        ])) {
            $object->RespCode = 200;
            $object->RespMessage = 'success';
            http_response_code(200);
        } else {
            $object->RespCode = 500;
            $object->RespMessage = 'bad : Update failed';
            http_response_code(500);
        }

        echo json_encode($object);
    } else {
        http_response_code(405);
    }


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

        // สร้างคำสั่ง SQL สำหรับการดึงข้อมูลรายการรายรับ/รายจ่าย
        $sql = "SELECT * FROM expenses $condition ORDER BY expense_date DESC";
        $stmt = $db->prepare($sql);
        if ($stmt->execute($params)) {
            $num = $stmt->rowCount();
            if ($num > 0) {
                $object->Result = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $object->Result[] = $row;
                }
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

   
    


} 

catch (PDOException $e) {
    http_response_code(500);
    echo $e->getMessage();
}



?>