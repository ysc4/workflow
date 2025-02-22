<?php
	$host = 'localhost'; // Hostname or IP address
	$db = 'u415861906_infosec2222'; // Database name
	$user = 'root'; // MySQL username
    $port = 3307;
	$pass = ''; // MySQL password
	$charset = 'utf8mb4'; // Character set (optional but recommended)

	try {
		// Set DSN (Data Source Name)
		$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
		
		// Options for PDO
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enables exceptions for errors
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetches results as associative arrays
			PDO::ATTR_EMULATE_PREPARES   => false,                  // Disables emulated prepared statements
		];
		
		// Create a PDO instance
		$pdo = new PDO($dsn, $user, $pass, $options);
		$asdsadsa = 1;
		
		
	} catch (PDOException $e) {
		// Handle connection errors
		echo "Connection failed: " . $e->getMessage();
	}
    
    // LOGIN
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
    
        // Validate inputs
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
            exit;
        }
    
        // Fetch user credentials from the database
        $stmt = $pdo->prepare("SELECT uc.username, uc.password, e.department, e.employeeID
                               FROM UserCredentials uc
                               JOIN employee e ON uc.employeeID = e.employeeID
                               WHERE uc.username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
    
        if ($user) {
            // Compare the plain text password directly
            if ($password === $user['password']) {
                echo json_encode([
                    'success' => true,
                    'employeeID' => $user['employeeID'],
                    'department' => $user['department']
                ]);
            } else {
                // Password mismatch
                echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            }
        } else {
            // User not found
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
    
        exit;
    }


    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getEmployees') {
        header('Content-Type: application/json');
    
        $sql = "
            SELECT 
                e.employeeID,
                e.lastName,
                e.firstName,
                e.position,
                e.contactInformation,
                e.department,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM attendance a
                        WHERE a.employeeID = e.employeeID
                        AND DATE(a.timeIn) = CURDATE()
                    ) THEN 'Present'
                    WHEN EXISTS (
                        SELECT 1
                        FROM leaverequest l
                        WHERE l.employeeID = e.employeeID
                        AND CURDATE() BETWEEN l.startDate AND l.endDate
                    ) THEN 'On Leave'
                    ELSE 'Absent'
                END AS status
            FROM employee e
        ";
        
        $stmt = $pdo->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode($employees);
        exit();
    }   
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getPayslips') {
        header('Content-Type: application/json');
    
        $sql = "
        SELECT 
            e.employeeID, e.lastName, e.firstName, e.department,
            p.payrollID, p.paymentDate, p.startDate, p.endDate, 
            p.hoursWorked, p.ratePerHour, p.deductions, p.netPay
        FROM 
            payroll p
        JOIN 
            employee e ON e.employeeID = p.employeeID
        ";
        
        $stmt = $pdo->query($sql);
        $payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode($payslips);
        exit();
    }   

    if (isset($_GET['fetchEmployeeData']) && isset($_GET['employeeID'])) {
        header('Content-Type: application/json');
        $employeeID = $_GET['employeeID'];
    
        try {
            // Fetch employee details
            $employeeSql = "SELECT firstName, lastName, contactInformation, department, position
                            FROM employee WHERE employeeID = ?";
            $stmt = $pdo->prepare($employeeSql);
            $stmt->execute([$employeeID]);
            $employeeDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            // Query to fetch employee status
            $sql = " SELECT e.employeeID, e.firstName, e.lastName,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM attendance a 
                        WHERE a.employeeID = e.employeeID 
                        AND DATE(a.timeIn) = CURDATE()
                    ) THEN 'Present'
                    WHEN EXISTS (
                        SELECT 1 
                        FROM leaverequest l 
                        WHERE l.employeeID = e.employeeID 
                        AND CURDATE() BETWEEN l.startDate AND l.endDate
                    ) THEN 'On Leave'
                    ELSE 'Absent'
                END AS status
            FROM 
                employee e
            WHERE e.employeeID =?            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employeeID]);
            $employeeStatus = $stmt->fetch(PDO::FETCH_ASSOC);
                
                
            // Fetch leave history
            $leaveSql = "SELECT leaveType, startDate, endDate, DATEDIFF(endDate, startDate) + 1 AS days
                         FROM leaverequest WHERE employeeID = ?";
            $stmt = $pdo->prepare($leaveSql);
            $stmt->execute([$employeeID]);
            $leaveHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Fetch payment history
            $paymentSql = "SELECT startDate, endDate, paymentDate, netPay 
                           FROM payroll WHERE employeeID = ?";
            $stmt = $pdo->prepare($paymentSql);
            $stmt->execute([$employeeID]);
            $paymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($employeeDetails) {
                echo json_encode([
                    "success" => true,
                    "employeeDetails" => $employeeDetails,
                    "leaveHistory" => $leaveHistory,
                    "paymentHistory" => $paymentHistory,
                    "employeeStatus" => $employeeStatus,
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Employee not found."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
        exit;
    }

    try {
        $sql = "SELECT
                    lr.leaveID,
                    lr.employeeID, 
                    CONCAT(e.firstName, ' ', e.lastName) AS employeeName, 
                    lr.startDate, 
                    lr.endDate, 
                    lr.leaveType
                FROM leaveRequest lr
                JOIN employee e ON lr.employeeID = e.employeeID
                WHERE lr.leaveStatus = 'Pending' AND lr.startDate > CURDATE()";

        $stmt = $pdo->query($sql);
        $incomingLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching leave requests: " . $e->getMessage();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaveID'], $_POST['leaveStatus'])) {
        $leaveID = $_POST['leaveID'];
        $leaveStatus = $_POST['leaveStatus'];

        try {
            $sql = "UPDATE leaveRequest SET leaveStatus = :leaveStatus WHERE leaveID = :leaveID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['leaveStatus' => $leaveStatus, 'leaveID' => $leaveID]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    $sql = "
        SELECT 
            e.employeeID, e.lastName, e.firstName, e.department,
            p.payrollID, p.paymentDate, p.startDate, p.endDate, 
            p.hoursWorked, p.ratePerHour, p.deductions, p.netPay
        FROM 
            payroll p
        JOIN 
            employee e ON e.employeeID = p.employeeID
        ";
    $stmt = $pdo->query($sql);
    $payrollData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action'])) { //Check if action is set
        switch ($data['action']) {
            case 'fetchPayslipDetails':
                if (isset($data['payrollID'])) {
                    $stmt = $pdo->prepare("SELECT * FROM payroll WHERE payrollID = ?");
                    $stmt->execute([$data['payrollID']]);
                    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => $payslip !== false, 'payslip' => $payslip ?? null]); // Use null coalescing
                } else {
                    echo json_encode(['success' => false, 'message' => 'payrollID is missing']);
                }
                break;

            case 'fetchEmployeeDetails':
                if (isset($data['employeeID'])) {
                    $stmt = $pdo->prepare("SELECT firstName, lastName, position FROM employee WHERE employeeID = ?");
                    $stmt->execute([$data['employeeID']]);
                    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => $employee !== false, 'employee' => $employee ?? null, 'message' => $employee ? null : 'Employee not found.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'employeeID is missing']);
                }
                break;

            case 'fetchUnavailableDates':
                if (isset($data['employeeID'])) {
                    try {
                        $stmt = $pdo->prepare("SELECT startDate, endDate FROM payroll WHERE employeeID = ?");
                        $stmt->execute([$data['employeeID']]);
            
                        $ranges = [];
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $ranges[] = [
                                'startDate' => $row['startDate'],
                                'endDate' => $row['endDate']
                            ];
                        }
                        echo json_encode(['success' => true, 'ranges' => $ranges]);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'employeeID is missing']);
                }
                break;

            case 'fetchHoursWorked':
                if (isset($data['employeeID'], $data['startDate'], $data['endDate'])) {
                    $stmt = $pdo->prepare("SELECT SUM(hoursWorked) as totalHours FROM attendance WHERE employeeID = ? AND date BETWEEN ? AND ?");
                    $stmt->execute([$data['employeeID'], $data['startDate'], $data['endDate']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => $result && $result['totalHours'] !== null, 'totalHours' => $result['totalHours'] ?? null, 'message' => $result && $result['totalHours'] !== null ? null : 'No records found.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Missing employeeID, startDate, or endDate']);
                }
                break;

                if ($data['$action'] === 'generatePayslip' && isset($data['payslipData'])) {
                    $payslip = $data['payslipData'];
                    echo json_encode($payslip);
                    $stmt = $pdo->prepare("INSERT INTO payroll (employeeID, startDate, endDate, hoursWorked, ratePerHour, deductions, netPay) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $success = $stmt->execute([
                        $payslip['employeeID'], 
                        $payslip['startPayDate'], 
                        $payslip['endPayDate'], 
                        $payslip['hoursWorked'], 
                        $payslip['ratePerHour'], 
                        $payslip['deductions'], 
                        $payslip['netPay']
                    ]);
                
                    if ($success) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to insert payslip.']);
                    }
                    exit;
                }
            case 'generatePayslip':
                if (isset($data['payslipData'])) {
                    $payslip = $data['payslipData'];

                    try {
                        $stmt = $pdo->prepare("INSERT INTO payroll (employeeID, startDate, endDate, hoursWorked, ratePerHour, salary, deductions, netPay, paymentDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $success = $stmt->execute([
                            $payslip['employeeID'],
                            $payslip['startPayDate'],
                            $payslip['endPayDate'],
                            $payslip['hoursWorked'],
                            $payslip['ratePerHour'],
                            $payslip['salary'],
                            $payslip['deductions'],
                            $payslip['netPay'],
                            $payslip['paymentDate']
                        ]);

                        echo json_encode(['success' => $success, 'message' => $success ? 'Payslip generated successfully!' : 'Failed to insert payslip.']);

                    } catch (PDOException $e) {
                        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                    }

                } else {
                    echo json_encode(['success' => false, 'message' => 'payslipData is missing']);
                }
                break;
            case 'editEmployee':
                header('Content-Type: application/json');
    
                try {
                    // Decode the JSON payload
                    $input = json_decode(file_get_contents("php://input"), true);
            
                    if (!$input) {
                        throw new Exception("Invalid input data.");
                    }
            
                    // Extract and sanitize the fields
                    $employeeID = filter_var($input['employeeID'], FILTER_SANITIZE_NUMBER_INT);
                    $contactInformation = filter_var($input['contactInformation'], FILTER_SANITIZE_STRING);
                    $department = filter_var($input['department'], FILTER_SANITIZE_STRING);
                    $position = filter_var($input['position'], FILTER_SANITIZE_STRING);
            
                    // Validate required fields
                    if (empty($employeeID) || !is_numeric($employeeID)) {
                        throw new Exception("Invalid or missing employee ID.");
                    }
                    if (empty($position)) {
                        throw new Exception("Position is required.");
                    }
            
                    // Update the employee details
                    $stmt = $pdo->prepare("
                        UPDATE employee 
                        SET contactInformation = :contactInformation, 
                            department = :department, 
                            position = :position
                        WHERE employeeID = :employeeID
                    ");
                    $stmt->execute([
                        ':contactInformation' => $contactInformation,
                        ':department' => $department,
                        ':position' => $position,
                        ':employeeID' => $employeeID,
                    ]);
            
                    echo json_encode(["success" => true, "message" => "Employee updated successfully."]);
                } catch (Exception $e) {
                    // Catch errors and return an appropriate response
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
                break;

            case 'addEmployee':
                header('Content-Type: application/json');
            
                try {
                    $input = json_decode(file_get_contents("php://input"), true);
            
                    $lastName = filter_var($input['lastName'], FILTER_SANITIZE_STRING);
                    $firstName = filter_var($input['firstName'], FILTER_SANITIZE_STRING);
                    $contactInformation = filter_var($input['contactInformation'], FILTER_SANITIZE_STRING);
                    $department = filter_var($input['department'], FILTER_SANITIZE_STRING);
                    $position = filter_var($input['position'], FILTER_SANITIZE_STRING);
            
                    if (!preg_match('/^[a-zA-Z\s]+$/', $lastName)) {
                        throw new Exception("Invalid last name.");
                    }
            
                    if (!preg_match('/^[a-zA-Z\s]+$/', $firstName)) {
                        throw new Exception("Invalid first name.");
                    }
            
                    if (empty($position)) {
                        throw new Exception("Position is required.");
                    }
            
                    $stmt = $pdo->prepare("
                        INSERT INTO employee (lastName, firstName, contactInformation, department, leaveBalance, position)
                        VALUES (:lastName, :firstName, :contactInformation, :department, :leaveBalance, :position)
                    ");
                    $stmt->execute([
                        ':lastName' => $lastName,
                        ':firstName' => $firstName,
                        ':leaveBalance' => 10,
                        ':contactInformation' => $contactInformation,
                        ':department' => $department,
                        ':position' => $position,
                    ]);
            
                    echo json_encode(["success" => true]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        }
        exit; // Very important: Stop further execution
    } else {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow - IT Department</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #F6F4F0;
            margin: 0;
            padding: 0;
        }
        .main {
            width: 90%;
            height: 80vh;
            margin: 0 auto;
            padding: 30px;
            background-color: #2E5077;
            border-radius: 20px;
        }
        .container {
            background-color: #F6F4F0;
            padding: 20px;
            border-radius: 15px;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid black; /* Changed border color to black */
            overflow: auto;
        }
        td {
            border: 1px solid black; /* Changed border color to black */
            padding: 8px;
            text-align: left;
            background-color: #F6F4F0;
        }
        th {
            background-color: #2E5077;
            color: #F6F4F0;
            border: 1px solid black; /* Changed border color to black */
            padding: 8px;
            text-align: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #F6F4F0;
            color: #fff;
            padding: 10px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        select {
            padding: 10px 20px; 
            margin: 10px 10px 10px 20px;
            background-color: #F6F4F0; 
            color: #2E5077; 
            border: 1px solid #2E5077;
            border-radius: 15px; 
            font-family: "Poppins", sans-serif;
            font-style: normal;
            font-size: 16px;
        }
        nav {
            display: flex; 
            margin-left: 90px;
            margin-bottom: 0;
        }
        .navigation {
            display: inline-block;
            margin: 0 10px 0 0;
            padding: 10px 20px;
            background-color: #F6F4F0;
            color: #2E5077;
            cursor: pointer;
            border-radius: 15px 15px 0 0;
            margin-bottom: 0;
        }
        .navigation.active {
            background-color: #2E5077; /* Cream background */
            color: #F6F4F0; /* Blue font color */
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-container #left {
            display: flex;
            align-items: center;
        }
        .header-container #right {
            margin-left: auto;
        }
        #employee-table, #payroll-table, #leave-table {
            overflow: auto;
            margin: 10px;
            height: 85%;
        }
        .fa-plus, .fa-download {
            color: #F6F4F0;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }
        .fa-pen-to-square {
            color: #4DA1A9;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }
        .fa-sort {
            color: #2E5077;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }
        .header-container h1, .header-container h2 {
            margin: 0;
            color: #F6F4F0;
            font-family: "Poppins", sans-serif;
            font-size: 30px;
            font-weight: 900;
        }
        .hidden {
            display: none;
        }
        .leave-flex-container {
            display: flex;
            justify-content: space-between;
            height: 92%;
        }
        #incoming-leaves {
            width: 30%;
            height: 100%;
            overflow: auto;
        }
        #incoming-leaves h2, #leave-overview h2 {
            color: #2E5077;
            font-weight: 1000;
            margin: 5px 5px 10px;
        }
        #incoming-leaves-header, #leave-overview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #leave-overview {
            width: 70%;
            height: 100%;
        }
        .leave {
            background-color: #F6F4F0;
            padding: 10px;
            border: 1px solid black; 
            line-height: 0.8; 
        }
        #generate-payroll {
            color: #F6F4F0;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }
        .modal-content {
            background-color: #F6F4F0;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 600px; 
            overflow-y: auto;
            border-radius: 20px;
        }
        #view-employee-details {
            height: 75%;
        }
        #leave-overview-report {
            width: 1000px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal h2, .modal h3 {
            margin: 0;
            font-size: 30px;
            font-weight: bold;
            color: #4DA1A9;
        }
        input[type="text"] {
            width: 60%; 
            padding: 5px 10px; 
            margin: 8px 0; 
            box-sizing: border-box; 
            font-size: 16px; 
            font-family: "Poppins", sans-serif;
            border: 1px solid #2E5077; 
            border-radius: 5px; 
        }
        input[type="date"] {
            padding: 10px 10px; 
            margin: 10px;
            background-color: #F6F4F0; 
            color: #2E5077; 
            border: 1px solid #2E5077; 
            border-radius: 5px; 
            font-family: "Poppins", sans-serif;
            font-style: normal;
            font-size: 14px;
        }
        .form-group {
            text-align: center; 
        }
        .submit {
            width: auto; 
            padding: 10px 20px; 
            font-size: 16px; 
            font-family: "Poppins", sans-serif;
            background-color: #4DA1A9; 
            color: #F6F4F0; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
        }

        #view_employee_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .leave-request {
            width: 90%;
            margin: 10px auto; /* Center the div and add margin */
            padding: 10px; /* Add padding for better spacing */
            border-radius: 10px;
            background-color: #F6F4F0; /* Background color */
            color: #2E5077;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
            border: 1px solid #2E5077; /* Border color */
            cursor: pointer; /* Change cursor to pointer */
            transition: transform 0.2s; /* Add transition for hover effect */
            line-height: 0.6; /* Lessen line spacing */
        }

        .leave-request:hover {
            transform: scale(1.02); /* Slightly enlarge on hover */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Enhance shadow on hover */
        }
        #reqButtons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        #departmentFilter {
            padding: 10px 10px; 
            margin-left: 5px;
            background-color: #F6F4F0; 
            color: #2E5077; 
            border: 1px solid #2E5077;
            border-radius: 5px; 
            font-family: "Poppins", sans-serif;
            font-style: normal;
            font-size: 14px;
        }
        #view-icon-cell {
            text-align: center;
            vertical-align: middle;
        }
        #netPay {
            color: #F6F4F0;
            background-color: #4DA1A9;
            font-weight: bold;
            font-style: italic;
        }
        .button-container, #leave-request-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        #leave-request-buttons button {
            padding: 10px 20px;
            font-size: 16px;
            border: 1px solid #F6F4F0;
            border-radius: 10px;
            color: #F6F4F0;
            background-color: #4DA1A9;
        }
        #leave-request-buttons button:hover {
            background-color: #F6F4F0;
            color: #4DA1A9;
        }
        .profile {
            width: 90%;
            padding: 20px;
            background-color: #F6F4F0;
            margin: 0 auto;
            color: #2E5077;
            text-align: left;
            border-radius: 20px;
            margin-bottom: 50px; /* Adjust as needed */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added shadow */
            box-sizing: border-box; /* Ensure padding is included in the width */
            overflow: hidden; /* Prevent content from overflowing */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile img {
            margin-right: 20px;
            border-radius: 50%;
            height: 150px;
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile h2, .profile p {
            margin: 0;
            padding: 2px 0; /* Reduced padding to lessen spacing */
        }
        .profile h2 {
            font-size: 32px;
            font-weight: bold;
            color: #4DA1A9;
        }

        .profile-time-container {
            width: 200px;
            height: 130px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #2E5077;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added shadow */
        }

        #profile-time {
            font-size: 50px;
            font-weight: bold;
            color: #F6F4F0;
            display: flex;
            align-items: center;
        }

        button {
            font-family: "Poppins", sans-serif;
            border: 1px solid #F6F4F0;
            border-radius: 15px;
            color: #F6F4F0;
            background-color: #2E5077;
            padding: 10px 20px;
        }

        button:hover {
            background-color: #F6F4F0;
            color: #4DA1A9;
        }
        #bg employee_main, hr_main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #F6F4F0;
            color: #fff;
            padding: 10px 20px;
        }
        #user-leave {
            display: flex;
            justify-content: space-between;
            height: 50%;
        }
        #remaining-leaves {
            width: 40%;
            height: 80%;
            overflow: auto;
        }
        #remaining-leaves h2 {
            color: #2E5077;
            font-weight: 1000;
            margin: 5px 5px 10px;
        }
        #leave-request {
            width: 60%;
            height: 80%;
            overflow: auto;
        }
        #leave-request h2 {
            color: #2E5077;
            font-weight: 1000;
            margin: 5px 5px 10px;
        }
        .leave-history {
            width: 90%;
            height: 50%;
            overflow: auto;
        }
        #leave-history h2 {
            color: #2E5077;
            font-weight: 1000;
            margin: 5px 5px 10px;
        }
        #user-payroll {
            display: flex;
            justify-content: center;
            height: 100%;
        }
        .payroll-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            gap: 20px; /* Add gap between the child elements */
        }
        .payroll-overview, .payment-history {
            width: 97%;
            flex: 1;
            overflow: auto;
            background-color: #F6F4F0;
            border-radius: 20px;
            padding: 20px;
            margin: 20px;
        }
        .payroll-overview h2, .payment-history h2 {
            color: #2E5077;
            font-size: 28px;
            font-weight: 1000;
            margin: 5px 5px 10px;
        }
                .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }
        .login-form {
            width: 500px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
            background-color: #4DA1A9;
            color: #F6F4F0;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added shadow */
        }
        .login-form h2 {
            margin: 0;
            font-size: 50px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .login-form label {
            margin: 10px 0;
            font-size: 20px;
        }
        .login-form input {
            width: 100%;
            padding: 10px;
            margin: 10px auto;
            font-size: 16px;
            border: 1px solid #F6F4F0;
            border-radius: 15px;
        }
        .login-form button {
            padding: 10px 20px;
            font-size: 16px;
            border: 1px solid #F6F4F0;
            border-radius: 15px;
            color: #4DA1A9;
            background-color: #F6F4F0;
            margin-top: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="workflow logo.png" alt="Workflow Logo" width="100" align="left">
        <i class="fa-solid fa-user" id="userIcon" style="font-size: 30px; color: #4DA1A9;" align="right"></i>
    </div>

    <div class="bg" id="login">
        <div class="login-container">
            <div class="login-form">
                <h2>Login</h2>
                <form>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username"><br>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password"><br>
                    <div class="button-container">
                        <button type="submit" class="submit">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg hidden" id="hr_main">
        <nav>
            <div class="navigation active" id="employee_nav">Employee</div>
            <div class="navigation" id="leave_nav">Leave</div>
            <div class="navigation" id="payroll_nav">Payroll</div>
        </nav>

        <div class="main" id="employee_container">
            <div class="header-container">
                <div id="left">
                    <h1>Employees Overview</h1>
                    <select id="filterEmployeeDepartment">
                        <option value="IT Department">IT Department</option>
                        <option value="HR Department">HR Department</option>
                        <option value="Finance Department">Finance Department</option>
                    </select>
                </div>
                <div id="right">
                    <i class="fa-solid fa-plus" id="add_employee"></i>            
                </div>
            </div>
            <div class="container" id="employee-table">
                <table>
                    <thead>
                        <tr>
                            <th>EMPLOYEE ID</th>
                            <th>LAST NAME</th>
                            <th>FIRST NAME</th>
                            <th>POSITION</th>
                            <th>CONTACT INFORMATION</th>
                            <th>STATUS</th>
                            <th>VIEW</th>
                        </tr>
                    </thead>
                    <tbody id = "employeeOverviewTable">
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Leave Container -->
        <div class="main hidden" id="leave_container">
            <div class="leave-flex-container">
                <div class="container" id="incoming-leaves">
                    <div id="incoming-leaves-header">
                        <h2>Incoming Leave Requests</h2>
                        <i class="fa-solid fa-sort" id="sortIcon"></i>
                    </div>
                    <div id="incoming-leaves-list">
                        <!-- Leave requests will be dynamically added here -->
                    </div>
                </div>
                <div class="container" id="leave-overview">
                    <div id="leave-title">
                        <div id="leave-overview-header">
                            <h2>Leave Overview</h2>
                            <i class="fa-solid fa-download" id="generateOverview"></i>                
                        </div>
                        <div id="filter-options">
                            <form id="filterForm">
                                <label for="startDate">From:</label>
                                <input type="date" id="startDate" name="startDate">
                                <label for="endDate">&emsp; To:</label>
                                <input type="date" id="endDate" name="endDate">
                                <label for="departmentFilter">&emsp; Department:</label>
                                <select id="departmentFilter" name="department">
                                    <option value="">All Departments</option>
                                    <option value="IT Department">IT Department</option>
                                    <option value="HR Department">HR Department</option>
                                    <option value="Finance Department">Finance Department</option>
                                </select>
                                <i class="fa-solid fa-sync" id="filterRefreshIcon" style="cursor: pointer; margin-left: 10px;"></i>
                            </form>
                        </div>
                    </div>
                    <div id="leave-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>LEAVE ID</th>
                                    <th>EMPLOYEE ID</th>
                                    <th>EMPLOYEE NAME</th>
                                    <th>START DATE</th>
                                    <th>END DATE</th>
                                    <th>KIND OF LEAVE</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody id="leaveTableBody">
                                <?php
                                $sql = "SELECT lr.leaveID, lr.employeeID, lr.startDate, lr.endDate, lr.leaveType, lr.leaveStatus, e.firstName, e.lastName, e.department
                                        FROM leaverequest lr 
                                        JOIN employee e ON lr.employeeID = e.employeeID";
                                $stmt = $pdo->query($sql);
                                $leaveRequests = $stmt->fetchAll();

                                foreach ($leaveRequests as $request): ?>
                                    <tr data-leave-id="<?= htmlspecialchars($request['leaveID']); ?>">
                                        <td><?= htmlspecialchars($request['leaveID']); ?></td>
                                        <td><?= htmlspecialchars($request['employeeID']); ?></td>
                                        <td><?= htmlspecialchars($request['firstName'] . ' ' . $request['lastName']); ?></td>
                                        <td><?= htmlspecialchars($request['startDate']); ?></td>
                                        <td><?= htmlspecialchars($request['endDate']); ?></td>
                                        <td><?= htmlspecialchars($request['leaveType']); ?></td>
                                        <td><?= htmlspecialchars($request['leaveStatus']); ?></td>
                                        <td class="leave-department" style="display: none;"><?= htmlspecialchars($request['department']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <!-- Payroll Container -->
    <div class="main hidden" id="payroll_container">
        <div class="header-container">
            <div id="left">
                <h1>Payroll Overview</h1>
                <select id="filterPayrollDepartment">
                    <option value="" selected default>All Departments</option>
                    <option value="IT Department">IT Department</option>
                    <option value="HR Department">HR Department</option>
                    <option value="Finance Department">Finance Department</option>
                </select>
                <select id="payPeriod">
                    <option value="" selected default>- Choose Month -</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>
            <div id="right"> 
                <i class="fa-solid fa-plus" id="add_payslip"></i>            
            </div>
        </div>
        <div class="container" id="payroll-table">
            <table>
                <thead>
                    <tr>
                        <th>EMPLOYEE ID</th>
                        <th>LAST NAME</th>
                        <th>FIRST NAME</th>
                        <th>DATE RECEIVED</th>
                        <th>STATUS</th>
                        <th>VIEW</th>
                    </tr>
                </thead>
                <tbody id="payrollOverviewTable">

                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Employee</h2>
            <hr>
            <form id="addEmployeeForm">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" required autofocus><br>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" required><br>
                <label for="contactInfo">Contact Information:</label>
                <input type="text" id="contactInfo" name="contactInformation" required><br>
                <label for="department">Department:</label>
                <select id="department" name = "department" required>
                    <option value="IT Department">IT Department</option>
                    <option value="HR Department">HR Department</option>
                    <option value="Finance Department">Finance Department</option>
                </select><br>
                <label for="position">Position:</label>
                <input type="text" id="position" name="position" required><br>
                <div class="form-group">
                    <button id="add-employee" class="submit" type="submit">Add Employee</button>
                </div>
            </form>
        </div>
    </div>

     <!-- Edit Employee Modal -->
     <div id="editEmployeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Employee</h2>
            <hr>
            <form id="editEmployeeForm">
                <label for="editLastName">Last Name:</label>
                <input type="text" id="editLastName" name="lastName" readonly><br>
                <label for="editFirstName">First Name:</label>
                <input type="text" id="editFirstName" name="firstName" readonly><br>
                <label for="editContactInfo">Contact Information:</label>
                <input type="text" id="editContactInfo" name="contactInformation" required><br>
                <label for="editDepartment">Department:</label>
                <select id="editDepartment" name="department" required>
                    <option value="IT Department">IT Department</option>
                    <option value="HR Department">HR Department</option>
                    <option value="Finance Department">Finance Department</option>
                </select><br>
                <label for="editPosition">Position:</label>
                <input type="text" id="editPosition" name="position" required><br>
                <div class="form-group">
                    <button id="edit-employee" class="submit" type="submit">Edit Employee</button>
                </div>
            </form>
        </div>
    </div> 

    <!--View Employee Details-->
    <div id="viewEmployeeModal" class="modal">
        <div class="modal-content" id="view-employee-details">
            <span class="close">&times;</span>
            <div id="view_employee_header">
                <h2>View Employee</h2>
                <i class="fa-solid fa-pen-to-square" id="edit_employee"></i>
            </div>
            <hr>
            <p id="viewLastName"><b>Last Name</b></p>
            <p id="viewFirstName"><b>First Name</b></p>
            <p id="viewContactInfo"><b>Contact Information</b></p>
            <p id="viewDepartment"><b>Department</b></p>
            <p id="viewPosition"><b>Position</b></p>
            <p id="viewStatus"><b>Status</b></p>
            <h3>Leave History</h3>
            <table id = "leaveHistoryTable">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                    </tr> 
                </thead>
                <tbody>
                </tbody>
            </table> 
            <h3>Payslip Overview</h3>
            <table id = "paymentHistoryTable">
                <thead>
                    <tr>
                        <th>Pay Period</th>
                        <th>Pay Date</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leave Request Modal -->
    <div id="leaveRequestModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Leave Request</h2>
            <hr>
            <p id="viewName"><b>Employee Name</b></p>
            <p id="viewStartDate"><b>Start Date</b></p>
            <p id="viewEndDate"><b>End Date</b></p>
            <p id="viewLeaveType"><b>Kind of Leave</b></p>
            <div id="reqButtons">
                <button id="approve-leave" class="submit" data-leave-id="">Approve</button>
                <button id="reject-leave" class="submit" data-leave-id="">Reject</button>
            </div>
        </div>
    </div>

    <!-- Leave Overview Modal -->
    <div id="leaveOverviewModal" class="modal">
        <div class="modal-content" id="leave-overview-report">
            <span class="close">&times;</span>
            <h2>Leave Request Overview</h2>
            <hr>
            <p id="modalDepartment">Department:</p>
            <p id="modalStartDate">From:</p>
            <p id="modalEndDate">To:</p>
            <table>
                <thead>
                    <tr>
                        <th>LEAVE ID</th>
                        <th>EMPLOYEE ID</th>
                        <th>EMPLOYEE NAME</th>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>KIND OF LEAVE</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody id="leaveOverviewModalTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Generate Payslip Modal -->
    <div id="generatePayslipModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Generate Payslip</h2>
            <hr>
            <form>
                <label for="employeeID">Employee ID:</label>
                <input type="text" id="inputEmployeeID" name="employeeID" required autofocus><br>
                <label for="employeeName">Employee Name:</label>
                <input type="text" id="inputEmployeeName" name="employeeName" readonly required><br>
                <label for="position">Position:</label>
                <input type="text" id="positionPayslip" name="positionPayslip" readonly required><br>
                <label for="startDate">Start Pay Date:</label>
                <input type="date" id="startPayDate" name="startPayDate" required><br>
                <label for="endDate">End Pay Date:</label>
                <input type="date" id="endPayDate" name="endPayDate" required><br>
                <label for="hoursWorked">Hour/s Worked:</label>
                <input type="text" id="inputHoursWorked" name="hoursWorked" readonly required><br>
                <label for="payPerHour" required>Pay per hour:</label>
                <input type="text" id="inputPayPerHour" name="payPerDay" required><br>
                <label for="deduction">Deduction/s:</label>
                <input type="text" id="inputDeduction" name="deduction" readonly required><br>
                <label for="tax">Net Pay:</label>
                <input type="text" id="calculateNetPay" name="netPay" readonly required><br>
            </form>
            <div class="button-container">
                <button id="generatePayslipButton" class="submit">Generate Payslip</button>
            </div>
        </div>
    </div>

    <!-- Payslip Overview Modal -->
    <div id="PayslipOverviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Payslip Overview</h2>
            <hr>
            <p id="viewPayslipID"><b>Payslip ID</b></p>
            <p id="viewPayDate"><b>Pay Date</b></p>
            <p id="viewPayPeriod"><b>Pay Period</b></p>
            <table>
                <thead>
                    <tr>
                        <th>Payroll Breakdown</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Hours Worked: 24 hours
                            <br>Pay per Hour: $10
                            <br>Deductions: $100
                        </td>
                    </tr>  
                    <tr>
                        <td id="netPay">Net Pay: $140</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payroll Report Modal -->
    <div id="payslipReportModal" class="modal">
        <div class="modal-content" id="leave-overview-report">
            <span class="close">&times;</span>
            <h2>Payroll Report</h2>
            <hr>
            <p id="modalPayrollDepartment">Department:</p>
            <p id="modalPayPeriod">Pay Period:</p>
            <table>
                <thead>
                    <tr>
                        <th>PAYROLL ID</th>
                        <th>EMPLOYEE ID</th>
                        <th>EMPLOYEE NAME</th>
                        <th>DATE RECEIVED</th>
                        <th>AMOUNT</th>
                    </tr>
                </thead>
                <tbody id="payrollTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <!--USER/EMPLOYEE SIDE-->
    <div class="bg hidden" id="employee_main">
        <div class="profile">
            <img src="profile.png" alt="Profile Picture" height="100%" align="left">
            <div class="profile-info">
                <h2>John Doe</h2>
                <p>Junior Developer</p>
                <p>IT Department</p>
                <p>0912 345 6789</p>
            </div>
            <div class="profile-time-container">
                <div class="profile-time" id="profile-time">
                </div>
                <div class="button-container">
                    <button onclick="recordTimein()">TIME IN</button>
                    <button onclick="recordTimeout()">TIME OUT</button>
                </div>
            </div>
        </div>

        <nav>
            <div class="navigation active" id="user-attendance_nav">Attendance</div>
            <div class="navigation" id="user-leave_nav">Leave</div>
            <div class="navigation" id="user-payroll_nav">Payroll</div>
        </nav>

        <div class="main" id="user-attendance_container">
            <div class="header-container">
                <h2>Attendance History</h2>
            </div>
            <div class="container" id="attendance-table">
                <table>
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>TIME IN</th>
                            <th>TIME OUT</th>
                            <th>DURATION</th>
                            <th>HOURS</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01/09/2025</td>
                            <td>08:00:01</td>
                            <td>16:09:01</td>
                            <td>08:09:00</td>
                            <td>On Time</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leave Container -->
        <div class="main hidden" id="user-leave_container">
            <div class="leave-flex-container" id="user-leave">
                <div class="container" id="remaining-leaves">
                    <h2>Remaining Leaves</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>LEAVE TYPE</th>
                                <th>REMAINING LEAVES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Vacation</td>
                                <td>10</td>
                            </tr>
                            <tr>
                                <td>Sick</td>
                                <td>5</td>
                            </tr>
                            <tr>
                                <td>Maternity</td>
                                <td>60</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="container" id="leave-request">
                    <h2>Request for Leave</h2>              
                    <form>
                        <label for="leave-type">Leave Type:</label>
                        <select id="leave-type" name="leave-type">
                            <option value="vacation">Vacation</option>
                            <option value="sick">Sick</option>
                            <option value="maternity">Maternity</option>
                        </select><br>
                        <label for="start-date">Start Date:</label>
                        <input type="date" id="start-date" name="start-date"><br>
                        <label for="end-date">End Date:</label>
                        <input type="date" id="end-date" name="end-date"><br>
                        <div class="button-container">
                            <button type="button" class="submit" id="submitLeaveRequest">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="container" id="leave-history">
                <h2>Leave History</h2>
                <table>
                    <thead>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>LEAVE TYPE</th>
                        <th>DURATION</th>
                        <th>STATUS</th>
                        <th>VIEW</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01/09/2025</td>
                            <td>01/10/2025</td>
                            <td>Vacation</td>
                            <td>1 day</td>
                            <td>Approved</td>
                            <td id="view-icon-cell"><i class="fa-solid fa-eye view-leave-icon" data-id="1" style="cursor: pointer;"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payroll Container -->
        <div class="main hidden" id="user-payroll_container">
            <div class="payroll-container">
                    <div class="payroll-overview">
                        <h2>Payroll Overview</h2>
                        <h3>Upcoming Payroll Release: <span style="color: #4DA1A9; font-weight: bold;">December 29, 2024</span></h3>
                        <div class="breakdown">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Expected Payroll Breakdown</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Hours Worked: 24 hours
                                        <br>Pay per Hour: $10
                                        <br>Deductions: $100
                                    </td>
                                </tr>
                                <tr>
                                    <td id="netPay">Net Pay: $140</td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="payment-history">
                        <h2>Payment History</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>TRANSACTION ID</th>
                                    <th>DATE</th>
                                    <th>TIME</th>
                                    <th>TOTAL AMOUNT</th>
                                    <th>STATUS</th>
                                    <th>VIEW</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>November 25, 2024</td>
                                    <td>November 25, 2024</td>
                                    <td>8:09:30</td>
                                    <td>P800</td>
                                    <td>Completed</td>
                                    <td id="view-icon-cell"><i class="fa-solid fa-eye view-payslip-icon" data-id="1" style="cursor: pointer;"></i></td>
                                </tr>
                                <!-- Repeat rows as needed -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


        <div id="confirmLeaveModal" class="modal">
            <div class="modal-content" id="confirm-leave">
                <span class="close">&times;</span>
                <h2>Confirm Leave Request</h2>
                <hr>
                <p id="modalLeaveStartDate">From:</p>
                <p id="modalLeaveEndDate">To:</p>
                <p id="modalLeaveType">Kind of Leave:</p>
                <div class="button-container">
                    <button id="confirm-leave" class="submit">Confirm</button>
                    <button id="cancel-leave" class="submit">Cancel</button>
                </div>
            </div>
        </div>

        <div id="leaveDetailsModal" class="modal">
            <div class="modal-content" id="leave-details">
                <span class="close">&times;</span>
                <h2>Leave Details</h2>
                <hr>
                <p id="modalLeaveStartDate">From:</p>
                <p id="modalLeaveEndDate">To:</p>
                <p id="modalLeaveType">Kind of Leave:</p>
                <p id="modalLeaveStatus">Status:</p>
                <div class="button-container">
                    <button id="cancel-leave" class="submit">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get the modal
        var add_modal = document.getElementById("addEmployeeModal");
        var edit_modal = document.getElementById("editEmployeeModal");
        var view_modal = document.getElementById("viewEmployeeModal");
        var add_btn = document.getElementById("add_employee");
        var edit_btn = document.getElementById("edit_employee");
        var leave_request_modal = document.getElementById("leaveRequestModal");
        var generate_payslip_modal = document.getElementById("generatePayslipModal");
        const departmentSelect = document.getElementById('filterEmployeeDepartment');
        const employeeTableBody = document.getElementById('employeeOverviewTable');
        const payrollOverviewTableBody = document.getElementById('payrollOverviewTable');

        var spans = document.getElementsByClassName("close");
        for (var i = 0; i < spans.length; i++) {
            spans[i].onclick = function() {
                var modals = document.getElementsByClassName("modal");
                for (var j = 0; j < modals.length; j++) {
                    modals[j].style.display = "none";
                }
            }
        }

        window.onclick = function(event) {
            var modals = document.getElementsByClassName("modal");
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = "none";
                }
            }
        }

        // Function to populate the table dynamically
        function populateEmployeeTable(departmentFilter = null) {
            fetch('index.php?action=getEmployees')
                .then(response => response.json())
                .then(data => {
                    // Clear existing rows
                    employeeTableBody.innerHTML = '';

                    // Filter data based on selected department, if any
                    const filteredData = departmentFilter
                        ? data.filter(employee => employee.department === departmentFilter)
                        : data;

                    // Populate the table
                    filteredData.forEach(employee => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-department', employee.department);

                        row.innerHTML = `
                            <td>${employee.employeeID}</td>
                            <td>${employee.lastName}</td>
                            <td>${employee.firstName}</td>
                            <td>${employee.position}</td>
                            <td>${employee.contactInformation}</td>
                            <td>${employee.status}</td>
                            <td id="view-icon-cell">
                                <i class="fa-solid fa-eye view-employee-icon" data-id="${employee.employeeID}" style="cursor: pointer;"></i>
                            </td>
                        `;
                        employeeTableBody.appendChild(row);
                    });
                    attachViewEmployeeListeners();
                })
                .catch(error => {
                    console.error('Error fetching employee data:', error);
                });
        }

        populateEmployeeTable(departmentSelect.value);

        departmentSelect.addEventListener('change', function () {
            populateEmployeeTable(departmentSelect.value);
        });

        function attachViewEmployeeListeners() {
            document.querySelectorAll(".view-employee-icon").forEach(function (icon) {
                icon.addEventListener("click", function (event) {
                    event.preventDefault();
                    var employeeId = this.getAttribute("data-id");
                    console.log(employeeId);

                    // Fetch combined employee data
                    fetch(`index.php?fetchEmployeeData=true&employeeID=${employeeId}`)
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                // Populate employee details
                                var details = data.employeeDetails;
                                var status = data.employeeStatus;
                                edit_btn.value = employeeId;
                                document.getElementById("viewLastName").innerText = "Last Name: " + details.lastName;
                                document.getElementById("viewFirstName").innerText = "First Name: " + details.firstName;
                                document.getElementById("viewContactInfo").innerText = "Contact Information: " + details.contactInformation;
                                document.getElementById("viewDepartment").innerText = "Department: " + details.department;
                                document.getElementById("viewPosition").innerText = "Position: " + details.position;
                                document.getElementById("viewStatus").innerText = "Status: " + status.status;

                                // Populate leave history
                                var leaveTbody = document.querySelector("#leaveHistoryTable tbody");
                                leaveTbody.innerHTML = ""; // Clear previous rows
                                data.leaveHistory.forEach(function (leave) {
                                    var row = `
                                        <tr>
                                            <td>${leave.leaveType}</td>
                                            <td>${leave.startDate}</td>
                                            <td>${leave.endDate}</td>
                                            <td>${leave.days}</td>
                                        </tr>
                                    `;
                                    leaveTbody.innerHTML += row;
                                });

                                // Populate payment history
                                var paymentTbody = document.querySelector("#paymentHistoryTable tbody");
                                paymentTbody.innerHTML = ""; // Clear previous rows
                                data.paymentHistory.forEach(function (payment) {
                                    var row = `
                                        <tr>
                                            <td>${payment.startDate} - ${payment.endDate}</td>
                                            <td>${payment.paymentDate}</td>
                                            <td>$${payment.netPay}</td>
                                        </tr>
                                    `;
                                    paymentTbody.innerHTML += row;
                                });
                            } else {
                                alert(data.message || "Could not fetch employee data.");
                            }
                        })
                        .catch((error) => {
                            console.error("Error fetching employee data:", error);
                        });

                        // Show the modal
                        document.getElementById("viewEmployeeModal").style.display = "block";
                    });
                });
            };

        document.getElementById('addEmployeeForm').addEventListener('submit', function (event) {
            // Prevent default form submission
            event.preventDefault();

            // Gather form data
            const lastName = document.getElementById('lastName').value.trim();
            const firstName = document.getElementById('firstName').value.trim();
            const contactInfo = document.getElementById('contactInfo').value.trim();
            const department = document.getElementById('department').value.trim();
            const position = document.getElementById('position').value.trim();

            // Validate inputs (same logic as before)
            if (!/^[a-zA-Z\s]+$/.test(lastName)) {
                alert('Last Name must only contain letters and spaces.');
                return;
            }
            if (!/^[a-zA-Z\s]+$/.test(firstName)) {
                alert('First Name must only contain letters and spaces.');
                return;
            }
            if (position === '') {
                alert('Position is required.');
                return;
            }

            // Send data to the server
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'addEmployee',
                    lastName,
                    firstName,
                    contactInformation: contactInfo,
                    department,
                    position,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Employee added successfully!');

                        // Close the modal
                        document.getElementById('addEmployeeModal').style.display = 'none';
                        // Re-populate the table with the current filter applied
                        populateEmployeeTable(departmentSelect.value);
                    } else {
                        alert(data.message || 'Failed to add employee.');
                    }
                })
                .catch(error => {
                    console.error('Error adding employee:', error);
                });
        });

        add_btn.onclick = function() {
            add_modal.style.display = "block";
        }

        edit_btn.onclick = function() {
            // Get current employee details from the View Employee Modal
            const firstName = document.getElementById("viewFirstName").innerText.split(": ")[1];
            const lastName = document.getElementById("viewLastName").innerText.split(": ")[1];
            const contactInfo = document.getElementById("viewContactInfo").innerText.split(": ")[1];
            const department = document.getElementById("viewDepartment").innerText.split(": ")[1];
            const position = document.getElementById("viewPosition").innerText.split(": ")[1];
            const status = document.getElementById("viewStatus").innerText.split(": ")[1];

            document.getElementById("editFirstName").value = firstName;
            document.getElementById("editLastName").value = lastName;
            document.getElementById("editContactInfo").value = contactInfo;
            document.getElementById("editPosition").value = position;
            const departmentDropdown = document.getElementById("editDepartment");
            departmentDropdown.innerHTML = ""; // Clear existing options
            const departments = ["Finance Department", "IT Department", "HR Department"]; // Example departments
            departments.forEach((dept) => {
                const option = document.createElement("option");
                option.value = dept;
                option.textContent = dept;
                if (dept === department) {
                    option.selected = true; // Mark the current department as selected
                }
                departmentDropdown.appendChild(option);
            });
            departmentDropdown.value = department;

            edit_modal.style.display = "block";
            view_modal.style.display = "none";
        }

        document.getElementById("editEmployeeForm").addEventListener("submit", function (event) {
            event.preventDefault();

            const employeeId = edit_btn.value;
            const contactInfo = document.getElementById("editContactInfo").value.trim();
            const department = document.getElementById("editDepartment").value.trim();
            const position = document.getElementById("editPosition").value.trim();

            if (position === '') {
                alert('Position is required.');
                return;
            }

            fetch("index.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    action: 'editEmployee',
                    employeeID: employeeId,
                    contactInformation: contactInfo,
                    department: department,
                    position: position,
                }),
            }).then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Employee details updated successfully!");

                        // Update the View Employee Modal with the new values
                        document.getElementById("viewContactInfo").innerText = "Contact Information: " + contactInfo;
                        document.getElementById("viewDepartment").innerText = "Department: " + department;
                        document.getElementById("viewPosition").innerText = "Position: " + position;

                        // Update the Employee Table
                        populateEmployeeTable(document.getElementById("filterEmployeeDepartment").value);

                        // Close the Edit Modal and reopen the View Modal
                        document.getElementById("editEmployeeModal").style.display = "none";
                        document.getElementById("viewEmployeeModal").style.display = "block";
                    } else {
                        alert(data.message || "Failed to update employee details.");
                    }
                })
                .catch((error) => {
                    console.error("Error updating employee details:", error);
                });
        });


        // Sorting the incoming leave requests
        var sortAscending = true;
        document.getElementById('sortIcon').addEventListener('click', function() {
            var leaveRequests = document.querySelectorAll('#incoming-leaves-list .leave-request');
            var leaveRequestsArray = Array.from(leaveRequests);

            leaveRequestsArray.sort(function(a, b) {
                var dateA = new Date(a.querySelector('.start-date').innerText.split(": ")[1]);
                var dateB = new Date(b.querySelector('.start-date').innerText.split(": ")[1]);

                if (sortAscending) {
                    return dateA - dateB;
                } else {
                    return dateB - dateA;
                }
            });

            var incomingLeavesList = document.getElementById('incoming-leaves-list');
            incomingLeavesList.innerHTML = '';

            leaveRequestsArray.forEach(function(request) {
                incomingLeavesList.appendChild(request);
            });

            sortAscending = !sortAscending; 
        });

        const incomingLeaves = <?php echo json_encode($incomingLeaves); ?>;

        // Function to add leave requests dynamically
        function addLeaveRequest(leaveID, employeeName, startDate, endDate, leaveType) {
            var leaveRequestDiv = document.createElement("div");
            leaveRequestDiv.className = "leave-request";
            leaveRequestDiv.setAttribute("data-leave-id", leaveID);
            leaveRequestDiv.innerHTML = `
                <p><b>Employee Name:</b> ${employeeName}</p>
                <p class="start-date"><b>Start Date:</b> ${startDate}</p>
                <p><b>End Date:</b> ${endDate}</p>
                <p><b>Kind of Leave:</b> ${leaveType}</p>
            `;
            leaveRequestDiv.onclick = function() {
                document.getElementById("viewName").innerText = "Employee Name: " + employeeName;
                document.getElementById("viewStartDate").innerText = "Start Date: " + startDate;
                document.getElementById("viewEndDate").innerText = "End Date: " + endDate;
                document.getElementById("viewLeaveType").innerText = "Kind of Leave: " + leaveType;
                document.getElementById("approve-leave").setAttribute("data-leave-id", leaveID);
                document.getElementById("reject-leave").setAttribute("data-leave-id", leaveID);
                leave_request_modal.style.display = "block";
            };
            document.getElementById("incoming-leaves-list").appendChild(leaveRequestDiv);
        }

        // Populate leave requests
        incomingLeaves.forEach(leave => {
            addLeaveRequest(leave.leaveID, leave.employeeName, leave.startDate, leave.endDate, leave.leaveType);
        });

        function updateLeaveStatus(leaveID, leaveStatus) {
            const formData = new FormData();
            formData.append("leaveID", leaveID);
            formData.append("leaveStatus", leaveStatus);

            fetch("index.php", {
                method: "POST",
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // Remove leave request from the "incoming-leaves-list"
                        const leaveRequestDiv = document.querySelector(`.leave-request[data-leave-id="${leaveID}"]`);
                        if (leaveRequestDiv) {
                            leaveRequestDiv.remove();
                        }

                        // Update the leave status in the leave-table
                        const leaveTableRow = document.querySelector(`#leaveTableBody tr[data-leave-id="${leaveID}"]`);
                        if (leaveTableRow) {
                            const statusCell = leaveTableRow.cells[6]; // Assuming "STATUS" is the 7th column
                            if (statusCell) {
                                statusCell.textContent = leaveStatus;
                            }
                        }

                        alert(`Leave request ${leaveStatus.toLowerCase()}!`);
                    } else {
                        alert(`Failed to update leave status: ${data.message}`);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("An error occurred while updating leave status.");
                });
        }

        const approveButton = document.getElementById("approve-leave");
        const rejectButton = document.getElementById("reject-leave");

        approveButton.onclick = function () {
            const leaveID = this.getAttribute("data-leave-id");
            updateLeaveStatus(leaveID, "Approved");
            document.getElementById("leaveRequestModal").style.display = "none";
        };

        rejectButton.onclick = function () {
            const leaveID = this.getAttribute("data-leave-id");
            updateLeaveStatus(leaveID, "Rejected");
            document.getElementById("leaveRequestModal").style.display = "none";
        };

        // Filters leave overview in main table and leave report
        function filterLeaveOverview() {
            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;
            var department = document.getElementById('departmentFilter').value;

            var rows = document.querySelectorAll('#leaveTableBody tr');
            var filteredRows = [];

            rows.forEach(function(row) {
                var rowStartDate = new Date(row.cells[2].innerText);
                var rowEndDate = new Date(row.cells[3].innerText);
                var rowDepartment = row.querySelector('.leave-department')?.innerText.trim();

                var showRow = true;

                if (startDate && rowStartDate < new Date(startDate)) {
                    showRow = false;
                }
                if (endDate && rowEndDate > new Date(endDate)) {
                    showRow = false;
                }
                if (department && rowDepartment !== department) {
                    showRow = false;
                }

                if (showRow) {
                    row.style.display = '';
                    filteredRows.push(row.cloneNode(true));
                } else {
                    row.style.display = 'none';
                }
            });

            return filteredRows;
        }

        document.getElementById('filterRefreshIcon').addEventListener('click', function() {
            filterLeaveOverview();
        });

        document.getElementById('generateOverview').addEventListener('click', function() {
            var filteredRows = filterLeaveOverview();

            var modalTableBody = document.getElementById('leaveOverviewModalTableBody');
            modalTableBody.innerHTML = ''; // Clear existing rows
            filteredRows.forEach(function(row) {
                modalTableBody.appendChild(row);
            });

            // Set the filtered values in the modal
            document.getElementById('modalDepartment').innerText = "Department: " + document.getElementById('departmentFilter').value || 'All Departments';
            document.getElementById('modalStartDate').innerText = "From: " + document.getElementById('startDate').value || 'N/A';
            document.getElementById('modalEndDate').innerText = "To: " + document.getElementById('endDate').value || 'N/A';

            var leaveOverviewModal = document.getElementById('leaveOverviewModal');
            leaveOverviewModal.style.display = 'block';
        });

        function populatePayrollTable(departmentFilter = null){
            fetch('index.php?action=getPayslips')
                .then(response => response.json())
                .then(data => {
                    // Clear existing rows
                    payrollOverviewTableBody.innerHTML = '';

                    // Filter data based on selected department, if any
                    const filteredData = departmentFilter
                        ? data.filter(payslip => payslip.department === departmentFilter )
                        : data;

                    // Populate the table
                    filteredData.forEach(payslip => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-department', payslip.department);
                        row.innerHTML = `
                            <td>${payslip.employeeID}</td>
                            <td>${payslip.lastName}</td>
                            <td>${payslip.firstName}</td>
                            <td>${payslip.paymentDate}</td>
                            <td class="payroll-department" style="display: none;">${payslip.department}</td>
                            <td>Received</td>
                            <td id="view-icon-cell">
                                <i class="fa-solid fa-eye view-payslip-icon" data-payslip-id="${payslip.payrollID}" style="cursor: pointer;"></i>
                            </td>
                        `;
                        payrollOverviewTableBody.appendChild(row);
                    });
                    attachViewPayslipListeners();
                })
                .catch(error => {
                    console.error('Error fetching employee data:', error);
                });
        }

        populatePayrollTable();

        function attachViewPayslipListeners() {
            document.querySelectorAll(".view-payslip-icon").forEach(function (icon) {
                icon.addEventListener("click", function (event) {
                    var payslip_id = icon.getAttribute('data-payslip-id'); // Get the payslip ID from the data attribute
                    // Fetch payslip data from the server using AJAX
                    fetch('index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'fetchPayslipDetails', payrollID: payslip_id }) // Send the payslipID to the backend
                    })
                    .then(response => response.json()) // Parse the response as JSON
                    .then(data => {
                        if (data.success) {
                            // Update the modal with fetched payslip details
                            document.getElementById('viewPayslipID').innerText = `Payslip ID: ${data.payslip.payrollID}`;
                            document.getElementById('viewPayDate').innerText = `Pay Date: ${data.payslip.paymentDate}`;
                            document.getElementById('viewPayPeriod').innerText = `Pay Period: ${data.payslip.startDate} - ${data.payslip.endDate}`;

                            // Update the payroll breakdown table
                            document.querySelector('#PayslipOverviewModal table tbody').innerHTML = `
                                <tr>
                                    <td>Hours Worked: ${data.payslip.hoursWorked} hours<br>
                                        Pay per Hour: $${data.payslip.ratePerHour}<br>
                                        Deductions: $${data.payslip.deductions}</td>
                                </tr>
                                <tr>
                                    <td id="netPay">Net Pay: $${data.payslip.netPay}</td>
                                </tr>
                            `;

                            // Show the modal
                            document.getElementById('PayslipOverviewModal').style.display = 'block';
                        } else {
                            alert('Failed to fetch payslip details: ' + data.message); // Show error message
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching payslip details:', error);
                        alert('An error occurred while fetching payslip details.');
                    });
                    });
                });
            };

        function filterPayrollTable() {
            var selectedDepartment = document.getElementById('filterPayrollDepartment').value;
            var selectedPayPeriod = document.getElementById('payPeriod').value;

            var rows = document.querySelectorAll('#payrollOverviewTable tr');
            var filteredRows = [];

            rows.forEach(function(row) {
                var rowPayPeriod = row.cells[3].innerText.split('-'); // Assuming pay period is in the 4th column
                var rowDepartment = row.querySelector('.payroll-department')?.innerText.trim();
                var monthEntry = parseInt(rowPayPeriod[1]);
                var showRow = true;

                if (selectedDepartment && rowDepartment !== selectedDepartment) {
                    showRow = false;
                }
                if (selectedPayPeriod && (monthEntry != parseInt(selectedPayPeriod))) {
                    showRow = false;
                    console.log(monthEntry + ' is not ' + selectedPayPeriod);
                }

                if (showRow) {
                    row.style.display = '';
                    filteredRows.push(row.cloneNode(true));
                } else {
                    row.style.display = 'none';
                }
            });

            return filteredRows;
        }

        document.getElementById('filterPayrollDepartment').addEventListener('change', filterPayrollTable);
        document.getElementById('payPeriod').addEventListener('change', filterPayrollTable);

        const inputEmployeeID = document.getElementById('inputEmployeeID');
        const inputEmployeeName = document.getElementById('inputEmployeeName');
        const position = document.getElementById('positionPayslip');
        const startPayDate = document.getElementById('startPayDate');
        const endPayDate = document.getElementById('endPayDate');
        const inputHoursWorked = document.getElementById('inputHoursWorked');
        const inputPayPerHour = document.getElementById('inputPayPerHour');
        const inputDeduction = document.getElementById('inputDeduction');
        const calculateNetPay = document.getElementById('calculateNetPay');
        const generatePayslipButton = document.getElementById('generatePayslipButton');
        const generatePayslipModal = document.getElementById('generatePayslipModal');
        const addPayslipButton = document.getElementById('add_payslip');

        // Show Generate Payslip Modal
        addPayslipButton.onclick = function () {
            generatePayslipModal.style.display = 'block';
        };

        // Close Modal Logic
        document.querySelector('#generatePayslipModal .close').onclick = function () {
            generatePayslipModal.style.display = 'none';
        };

        // Fetch Employee Details when Employee ID loses focus
        inputEmployeeID.addEventListener('blur', function () {
            const employeeID = inputEmployeeID.value.trim();
            if (employeeID) {
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'fetchEmployeeDetails', employeeID })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        inputEmployeeName.value = `${data.employee.firstName} ${data.employee.lastName}`;
                        position.value = data.employee.position;
                                        
                        disableUnavailableDates(inputEmployeeID.value);
                    } else {
                        alert('Employee not found!');
                    }
                })
                .catch(error => console.error('Error fetching employee details:', error));
            }
        });

        // Disable unavailable dates in the date picker
        function disableUnavailableDates(employeeID) {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'fetchUnavailableDates', employeeID })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.ranges);
                    const unavailableDates = new Set();
                    
                    // Format dates as yyyy-mm-dd
                    data.ranges.forEach(range => {
                        let currentDate = new Date(range.startDate);
                        let endDate = new Date(range.endDate);

                        // Add each date in the range to unavailableDates
                        while (currentDate <= endDate) {
                            let formattedDate = currentDate.toISOString().split('T')[0]; // yyyy-mm-dd format
                            unavailableDates.add(formattedDate);
                            currentDate.setDate(currentDate.getDate() + 1); // Move to the next day
                        }
                    });

                    const dateInputFields = [startPayDate, endPayDate];

                    // Add event listeners for both start and end date pickers
                    dateInputFields.forEach(dateField => {
                        dateField.addEventListener('input', function () {
                            const selectedDate = dateField.value; // yyyy-mm-dd format
                            if (unavailableDates.has(selectedDate)) {
                                alert(`The selected date (${selectedDate}) is unavailable due to an existing payslip.`);
                                dateField.value = ''; // Clear invalid date selection
                            }
                        });
                    });

                } else {
                    console.error('Error: Could not fetch unavailable dates.', data.message);
                }
            })
            .catch(error => console.error('Error fetching unavailable dates:', error));
        }


        // When valid dates are entered, calculate hours worked
        function calculateHoursWorked(employeeID, startDate, endDate) {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'fetchHoursWorked', employeeID, startDate, endDate })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    inputHoursWorked.value = data.totalHours;
                } else {
                    inputHoursWorked.value = 0;
                    alert('No attendance records found for the selected range.');
                }
            })
            .catch(error => console.error('Error fetching hours worked:', error));
        }

        [startPayDate, endPayDate].forEach(input => {
            input.addEventListener('change', function () {
                const employeeID = inputEmployeeID.value.trim();
                if (employeeID && startPayDate.value && endPayDate.value) {
                    calculateHoursWorked(employeeID, startPayDate.value, endPayDate.value);
                }
            });
        });

        var grossPay = 0;
        // Calculate Gross Pay, Deductions, and Net Pay
        inputPayPerHour.addEventListener('input', function () {
            const ratePerHour = parseFloat(inputPayPerHour.value);
            const hoursWorked = parseFloat(inputHoursWorked.value);
            if (!isNaN(ratePerHour) && !isNaN(hoursWorked)) {
                grossPay = ratePerHour * hoursWorked;
                const deduction = grossPay * 0.15; // 15% deductions
                const netPay = grossPay - deduction;

                inputDeduction.value = deduction.toFixed(2);
                calculateNetPay.value = netPay.toFixed(2);
            }
        });

        // Generate Payslip
        generatePayslipButton.addEventListener('click', function () {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
            const day = String(today.getDate()).padStart(2, '0');
            const formattedDate = `${year}-${month}-${day}`;

            // Validate form inputs
            const form = document.querySelector('#generatePayslipModal form');
            if (!form.checkValidity()) {
                alert('Please fill out all required fields.');
                return;
            }

            // Pay Per Hour should only be numerical values
            if (!/^\d+(?:\.\d{1,2})?$/.test(inputPayPerHour.value.trim())) {
                alert('Pay Per Hour should be a numerical value with optional decimal points.');
                return;
            }

            const payslipData = {
                employeeID: inputEmployeeID.value.trim(),
                startPayDate: startPayDate.value,
                endPayDate: endPayDate.value,
                hoursWorked: inputHoursWorked.value,
                ratePerHour: inputPayPerHour.value.trim(),
                salary: grossPay,
                deductions: inputDeduction.value,
                netPay: calculateNetPay.value,
                paymentDate: formattedDate
            };

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'generatePayslip', payslipData })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payslip generated successfully!');
                    // Close modal and clear form
                    generatePayslipModal.style.display = 'none';
                    document.querySelector('#generatePayslipModal form').reset();
                    populatePayrollTable();
                    document.getElementById('filterPayrollDepartment').value = "";
                    document.getElementById('payPeriod').value = "";
                } else {
                    alert('Failed to generate payslip: ' + data.message);
                }
            })
            .catch(error => console.error('Error generating payslip:', error));
        });

        // Generate Payslip
        var generate_btn = document.getElementById("add_payslip");

        generate_btn.onclick = function() {
            generate_payslip_modal.style.display = 'block';
        }

        // View Payslip Details
        var view_payslip_icons = document.querySelectorAll('.view-payslip-icon');
        view_payslip_icons.forEach(function(icon) {
            icon.addEventListener('click', function() {
                var payslip_id = icon.getAttribute('data-payslip-id');
                // Fetch payslip data based on payslip_id (this is just a placeholder, you need to fetch the actual data)
                document.getElementById('viewPayslipID').innerText = "Payslip ID: 1"; // Replace with actual data
                document.getElementById('viewPayDate').innerText = "Pay Date: 01/15/2021"; // Replace with actual data
                document.getElementById('viewPayPeriod').innerText = "Pay Period: 01/01/2021 - 01/15/2021"; // Replace with actual data
                document.getElementById('PayslipOverviewModal').style.display = 'block';
            });
        });

        document.getElementById('submitLeaveRequest').addEventListener('click', function() {
            // Get the values from the form
            var leaveType = document.getElementById('leave-type').value;
            var startDate = document.getElementById('start-date').value;
            var endDate = document.getElementById('end-date').value;

            // Set the values in the confirm leave modal
            document.getElementById('modalLeaveStartDate').innerText = "From: " + startDate;
            document.getElementById('modalLeaveEndDate').innerText = "To: " + endDate;
            document.getElementById('modalLeaveType').innerText = "Kind of Leave: " + leaveType;

            // Display the confirm leave modal
            document.getElementById('confirmLeaveModal').style.display = 'block';
        });

        // Event listener for the view icons in the leave history table
        var viewIcons = document.querySelectorAll('.view-leave-icon');
        viewIcons.forEach(function(icon) {
            icon.addEventListener('click', function() {
                // Fetch the leave details based on the data-id attribute (this is just a placeholder, you need to fetch the actual data)
                var leaveId = icon.getAttribute('data-id');
                // Set the leave details in the modal (replace with actual data)
                document.getElementById('modalLeaveStartDate').innerText = "From: 01/01/2021"; // Replace with actual data
                document.getElementById('modalLeaveEndDate').innerText = "To: 01/15/2021"; // Replace with actual data
                document.getElementById('modalLeaveType').innerText = "Kind of Leave: Vacation"; // Replace with actual data
                document.getElementById('modalLeaveStatus').innerText = "Status: Approved"; // Replace with actual data

                // Display the leave details modal
                document.getElementById('leaveDetailsModal').style.display = 'block';
            });
        });

        // Add active class to the first navigation
        document.getElementById('leave_nav').addEventListener('click', function() {
            document.getElementById('employee_container').classList.add('hidden');
            document.getElementById('payroll_container').classList.add('hidden');
            document.getElementById('leave_container').classList.remove('hidden');
            document.getElementById('leave_nav').classList.add('active');
            document.getElementById('employee_nav').classList.remove('active');
            document.getElementById('payroll_nav').classList.remove('active');
        });

        // Add active class to the second navigation
        document.getElementById('employee_nav').addEventListener('click', function() {
            document.getElementById('leave_container').classList.add('hidden');
            document.getElementById('payroll_container').classList.add('hidden');
            document.getElementById('employee_container').classList.remove('hidden');
            document.getElementById('employee_nav').classList.add('active');
            document.getElementById('leave_nav').classList.remove('active');
            document.getElementById('payroll_nav').classList.remove('active');
        });

        // Add active class to the third navigation
        document.getElementById('payroll_nav').addEventListener('click', function() {
            document.getElementById('employee_container').classList.add('hidden');
            document.getElementById('leave_container').classList.add('hidden');
            document.getElementById('payroll_container').classList.remove('hidden');
            document.getElementById('payroll_nav').classList.add('active');
            document.getElementById('employee_nav').classList.remove('active');
            document.getElementById('leave_nav').classList.remove('active');
        });

        // JavaScript to update the time
        function updateTime() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var seconds = now.getSeconds().toString().padStart(2, '0');
            var timeString = hours + ':' + minutes + ':' + seconds;
            document.getElementById('profile-time').innerText = timeString;
        }

        setInterval(updateTime, 1000); // Update time every second
        updateTime(); // Initial call to display time immediately

        function recordTimein() {
            alert('Time in recorded!');
        }

        function recordTimeout() {
            alert('Time out recorded!');
        }

        // Add active class to the first navigation
        document.getElementById('user-leave_nav').addEventListener('click', function() {
            document.getElementById('user-attendance_container').classList.add('hidden');
            document.getElementById('user-payroll_container').classList.add('hidden');
            document.getElementById('user-leave_container').classList.remove('hidden');
            document.getElementById('user-leave_nav').classList.add('active');
            document.getElementById('user-attendance_nav').classList.remove('active');
            document.getElementById('user-payroll_nav').classList.remove('active');
        });

        // Add active class to the second navigation
        document.getElementById('user-attendance_nav').addEventListener('click', function() {
            document.getElementById('user-leave_container').classList.add('hidden');
            document.getElementById('user-payroll_container').classList.add('hidden');
            document.getElementById('user-attendance_container').classList.remove('hidden');
            document.getElementById('user-attendance_nav').classList.add('active');
            document.getElementById('user-leave_nav').classList.remove('active');
            document.getElementById('user-payroll_nav').classList.remove('active');
        });

        // Add active class to the third navigation
        document.getElementById('user-payroll_nav').addEventListener('click', function() {
            document.getElementById('user-attendance_container').classList.add('hidden');
            document.getElementById('user-leave_container').classList.add('hidden');
            document.getElementById('user-payroll_container').classList.remove('hidden');
            document.getElementById('user-payroll_nav').classList.add('active');
            document.getElementById('user-attendance_nav').classList.remove('active');
            document.getElementById('user-leave_nav').classList.remove('active');
        });


        //login
        document.addEventListener('DOMContentLoaded', function () {
    const loginButton = document.querySelector('.login-form button');
    const loginContainer = document.getElementById('login');
    const hrMainContainer = document.getElementById('hr_main');
    const employeeMainContainer = document.getElementById('employee_main'); // Make sure this is correct
    const userIcon = document.getElementById('userIcon'); // Assuming this is your logout icon

    loginButton.addEventListener('click', function (event) {
        event.preventDefault();  // Prevent form submission

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        // Validate inputs
        if (!username || !password) {
            alert('Please enter both username and password');
            return;
        }

        // Send login data to the server
        fetch('http://localhost/workflow2Copy/index.php', { // Use URL, not file path
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'login', username, password })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Login response:', data); // Debugging the response
            if (data.success) {
                loginContainer.classList.add('hidden');
                if (data.department === 'HR Department') {
                    hrMainContainer.classList.remove('hidden');
                    console.log('HR department: showing HR main container');
                } else if (data.department === 'IT Department' || data.department === 'Finance Department') {
                    employeeMainContainer.classList.remove('hidden');
                    console.log('Employee department: showing employee main container');
                } else {
                    console.log('Unknown department:', data.department);
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Logout functionality
    userIcon.addEventListener('click', function() {
        hrMainContainer.classList.add('hidden');
        employeeMainContainer.classList.add('hidden');
        loginContainer.classList.remove('hidden');
    });
});




    </script>
    </body>
