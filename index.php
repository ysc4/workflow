<?php
    $nonce = base64_encode(random_bytes(16)); // Generate nonce for CSP

    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data; img-src 'self' data:; connect-src 'self'; frame-src 'self'; form-action 'self'; frame-ancestors 'none';");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    header("X-Frame-Options: DENY");
    header("Server:");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: no-referrer-when-downgrade");
    header_remove('X-Powered-By'); // Hide PHP version for security

    // Start Secure Session    
    ini_set('session.cookie_httponly', true);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.cookie_secure', true);
    ini_set('session.cookie_expires', time() + 3600);
    session_regenerate_id(true); // Regenerate session ID to prevent session fixation attacks
    session_start();

	$host = ''; // Hostname or IP address
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
        $stmt = $pdo->prepare("SELECT uc.username, uc.password, e.department, e.employeeID, e.position, 
                                e.firstName, e.lastName, e.contactInformation
                            FROM usercredentials uc
                            JOIN employee e ON uc.employeeID = e.employeeID
                            WHERE uc.username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            // Compare the plain text password directly
            if ($password === $user['password']) {
                // Send employeeID to JavaScript
                $_SESSION['employeeID'] = $user['employeeID'];
                echo json_encode([
                    'success' => true,
                    'employeeID' => $user['employeeID'],
                    'department' => $user['department'],
                    'employeeName' => $user['firstName'] . ' ' . $user['lastName'],
                    'contactInformation' => $user['contactInformation'],
                    'position' => $user['position']
                ]);

                // Get current attendance in the attendance record
                $attendanceStmt = $pdo->prepare("SELECT date FROM attendance WHERE employeeID = :employeeID AND DATE(TimeIn) = CURDATE() ORDER BY attendanceID DESC LIMIT 1;");
                $attendanceStmt->execute(['employeeID' => $user['employeeID']]);

                // Fetch the result
                $attendanceData = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

                // Check if the user has already time-in today
                if ($attendanceData && $attendanceData['date'] != date('Y-m-d')) {
                    // Insert the time-in record in the attendance table
                    $timeInStmt = $pdo->prepare("INSERT INTO attendance (employeeID, TimeIn, TimeOut, date, hoursWorked) 
                    VALUES (:employeeID, CURRENT_TIMESTAMP, '00:00:00', CURDATE(), 0)");
                    $timeInStmt->execute(['employeeID' => $user['employeeID']]);
                } 

            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
        exit;
    }

    // LOGOUT
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
        $employeeID = $_SESSION['employeeID'] ?? null;

        if ($employeeID) {
            try {
                // Update the attendance record with the current timestamp as TimeOut
                $updateQuery = "UPDATE attendance 
                                SET TimeOut = CURRENT_TIMESTAMP, hoursWorked = TIMESTAMPDIFF(HOUR, TimeIn, CURRENT_TIMESTAMP)
                                WHERE employeeID = :employeeID
                                AND date = CURDATE()
                                AND TimeOut = '00:00:00'OR TimeOut > '17:00:00'
                                ORDER BY attendanceID DESC LIMIT 1";
                $stmt = $pdo->prepare($updateQuery);
                $stmt->execute(['employeeID' => $employeeID]);

                // Respond with success
                echo json_encode(['success' => true, 'message' => 'Logout successful']);
            } catch (Exception $e) {
                // Log the error and respond with failure
                error_log("Logout Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Logout failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No employee ID found']);
        }
        exit;
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

    // LEAVE REQUEST (without session, using POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'requestLeave') {
        // Get leave request data from POST
        $leaveType = $_POST['leaveType'] ?? '';
        $startDate = $_POST['startDate'] ?? '';
        $endDate = $_POST['endDate'] ?? '';
        $employeeID = $_POST['employeeID'] ?? ''; // Employee ID passed in the POST request

        // Validate input
        if (empty($leaveType) || empty($startDate) || empty($endDate) || empty($employeeID)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        if ($startDate < $endDate && $startDate >= date('Y-m-d')) {
            try {
                // Insert the leave request into the database
                $stmt = $pdo->prepare("INSERT INTO leaverequest (leaveType, startDate, endDate, employeeID, leaveStatus) 
                                        VALUES (:leaveType, :startDate, :endDate, :employeeID, 'Pending')");
                $stmt->execute([
                    'leaveType' => $leaveType,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'employeeID' => $employeeID
                ]);

                // Success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Leave request submitted successfully.',
                    'leaveStatus' => 'Pending' // Default status
                ]);
            } catch (PDOException $e) {
                // Handle database error
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'End date must be greater than start date and start date must be in the future.']);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getEmployeeAttendance') {
        header('Content-Type: application/json');
        $selectedEmployeeID = $_SESSION['employeeID'];

        $query = "SELECT date, TimeIn, TimeOut, hoursWorked FROM attendance WHERE employeeID = :employeeID ORDER BY date DESC";
        $preparedStatement = $pdo->prepare($query);
        $preparedStatement->execute([":employeeID" => $selectedEmployeeID]);
        $attendanceRecords = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($attendanceRecords);
        exit();
    }
    
    // Count leaves for a specific employee
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'countLeaves') {
        header('Content-Type: application/json');
        $selectedEmployeeID = $_SESSION['employeeID'];

        $sql = "SELECT leaveType, 
        SUM(DATEDIFF(endDate, startDate) + 1) as leaveCount
        FROM leaverequest
        WHERE employeeID = :selectedEmployeeID AND leaveStatus = 'Approved'
        GROUP BY leaveType;";
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['selectedEmployeeID' => $selectedEmployeeID]);
        $employeeLeaveCountRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($employeeLeaveCountRecords);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getEmployeeLeaveHistory') {
        header('Content-Type: application/json');
        $selectedEmployeeID = $_SESSION['employeeID'];

        // Fetch leave data for the specified employee
        $sql = "SELECT leaveID, startDate, endDate, leaveType, leaveStatus, DATEDIFF(endDate, startDate) + 1 AS leaveDuration
        FROM leaverequest 
        WHERE employeeID = :selectedEmployeeID
        ORDER BY startDate DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['selectedEmployeeID' => $selectedEmployeeID]);
        $employeeLeaveRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($employeeLeaveRecords);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getCurrentPayroll'){
        header('Content-Type: application/json');
        $selectedEmployeeID = $_SESSION['employeeID'];

        // Get the current date and extract the current month and year dynamically
        $currentMonth = date('m');  // Current month in numeric format (01 to 12)
        $currentYear = date('Y');   // Current year in 4-digit format (e.g., 2025)

        // SQL query to get payroll for the current month and year dynamically
        $sql = "SELECT hoursWorked AS totalHoursWorked, ratePerHour, deductions, netPay
            FROM payroll
            WHERE employeeID = :selectedEmployeeID 
            AND MONTH(paymentDate) = :currentMonth 
            AND YEAR(paymentDate) = :currentYear
            ORDER BY paymentDate DESC 
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'selectedEmployeeID' => $selectedEmployeeID,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear
        ]);

        $payrollData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($payrollData);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getEmployeePaymentHistory'){
        header('Content-Type: application/json');
        $selectedEmployeeID = $_SESSION['employeeID'];


        // Fetch payment records for the specified employee
        $sql = "SELECT payrollID, paymentDate, netPay 
            FROM payroll 
            WHERE employeeID = :selectedEmployeeID 
            ORDER BY paymentDate DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['selectedEmployeeID' => $selectedEmployeeID]);
        $employeePaymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($employeePaymentHistory);
        exit();
    }

    // Delete leave request employee
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'deleteLeave') {
        header('Content-Type: application/json');
        $leaveID = $_GET['leaveID'];

        try {
            $stmt = $pdo->prepare("DELETE FROM leaverequest WHERE leaveID = :leaveID");
            $stmt->execute(['leaveID' => $leaveID]);
            echo json_encode(['success' => true, 'message' => 'Leave request deleted successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }


    // HR SIDE
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

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getLeaves') {
        header('Content-Type: application/json');
    
        $sql = "SELECT lr.leaveID, lr.employeeID, lr.startDate, lr.endDate, lr.leaveType, lr.leaveStatus, e.firstName, e.lastName, e.department
                FROM leaverequest lr 
                JOIN employee e ON lr.employeeID = e.employeeID";
        
        $stmt = $pdo->query($sql);
        $leaveEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode($leaveEntries);
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
            $leaveSql = "SELECT leaveID, leaveType, startDate, endDate, DATEDIFF(endDate, startDate) + 1 AS days
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
                FROM leaverequest lr
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
            $sql = "UPDATE leaverequest SET leaveStatus = :leaveStatus WHERE leaveID = :leaveID";
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
            case 'fetchLeaveDetails':
                if (isset($data['leaveID'])) {
                    $stmt = $pdo->prepare("SELECT * FROM leaverequest WHERE leaveID = ?");
                    $stmt->execute([$data['leaveID']]);
                    $leave = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => $leave !== false, 'leave' => $leave ?? null]); // Use null coalescing
                } else {
                    echo json_encode(['success' => false, 'message' => 'leaveID is missing']);
                }
                break;
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
    <link rel="stylesheet" href="style.css">
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
                    <input type="text" id="username" name="username" required><br>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required><br>
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
                <h2 id = "employeeFullName">Default</h2>
                <p id = "jobTitle">Default</p>
                <p id = "workDepartment">Default</p>
                <p id = "employeeContactInformation">Default</p>
            </div>
            <div class="profile-time-container">
                <div class="profile-time" id="profile-time"></div>
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
                        </tr>
                    </thead>
                    <tbody id = "employee-attendance">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leave Container -->
        <div class="main hidden" id="user-leave_container">
            <div class="leave-flex-container" id="user-leave">
                <div class="container" id="remaining-leaves">
                <h2>Leave History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>LEAVE TYPE</th>
                            <th>DAYS TAKEN</th>
                        </tr>
                    </thead>
                    <tbody id = "employee-leave-counts">
                    </tbody>
                </table>
                </div>

                <div class="container" id="leave-request">
                    <h2>Request for Leave</h2>              
                    <form>
                        <label for="leave-type">Leave Type:</label>
                        <select id="leave-type" name="leave-type">
                            <option value="Vacation Leave">Vacation</option>
                            <option value="Sick Leave">Sick</option>
                            <option value="Maternity Leave">Maternity</option>
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
                        <th>LEAVE ID</th>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>LEAVE TYPE</th>
                        <th>DURATION</th>
                        <th>STATUS</th>
                        <th>VIEW</th>
                    </thead>
                    <tbody id = "employee-leave-history">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payroll Container -->
        <div class="main hidden" id="user-payroll_container">
            <div class="payroll-container">
                    <div class="payroll-overview">
                    <h2>Payroll Overview</h2>
                        <?php
                            // Get the last day of the current month
                            $date = new DateTime('last day of this month');
                            $payrollReleaseDate = $date->format('F j, Y');
                        ?>
                        <h3>Upcoming Payroll Release: <span style="color: #4DA1A9; font-weight: bold;"><?= $payrollReleaseDate ?></span></h3>
                        
                        <div class="breakdown">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Expected Payroll Breakdown</th>
                                    </tr>
                                </thead>
                                <tbody id = "employee-payroll-breakdown">
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
                                    <th>TOTAL AMOUNT</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody id = "employee-payment-history">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


        <div id="confirmLeaveModal" class="modal">
            <div class="modal-content">
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
                <p id="viewLeaveID">Leave ID:</p>
                <p id="viewLeaveStartDate">From:</p>
                <p id="viewLeaveEndDate">To:</p>
                <p id="viewTypeOfLeave">Kind of Leave:</p>
                <p id="viewLeaveStatus">Status:</p>
                <div class="button-container">
                    <button id="delete-leave" class="submit">Delete Request</button>
                </div>
            </div>
        </div>
    </div>

      
    <script nonce="<?php echo $nonce; ?>">
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
        const leaveTableBody = document.getElementById('leaveTableBody');
        
        const attendanceTable = document.getElementById('employee-attendance')
        const leaveCountsTable = document.getElementById('employee-leave-counts');
        const employeeLeaveHistoryTable = document.getElementById('employee-leave-history');
        const payrollBreakdownTable = document.getElementById('employee-payroll-breakdown');
        const employeePaymentHistoryTable = document.getElementById('employee-payment-history');

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

        departmentSelect.addEventListener('change', function () {
            populateEmployeeTable(departmentSelect.value);
        });

        function attachViewEmployeeListeners() {
            document.querySelectorAll(".view-employee-icon").forEach(function (icon) {
                icon.addEventListener("click", function (event) {
                    event.preventDefault();
                    var employeeId = this.getAttribute("data-id");
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

        //Populating Leave Table
        function populateLeaveTable(departmentFilter = null){
            fetch('index.php?action=getLeaves')
                .then(response => response.json())
                .then(data => {
                    // Clear existing rows
                    leaveTableBody.innerHTML = '';

                    // Filter data based on selected department, if any
                    const filteredData = departmentFilter
                        ? data.filter(leaveEntry => leaveEntry.department === departmentFilter )
                        : data;

                    // Populate the table
                    filteredData.forEach(leaveEntry => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-department', leaveEntry.department);
                        row.innerHTML = `
                            <td>${leaveEntry.leaveID}</td>
                            <td>${leaveEntry.employeeID}</td>
                            <td>${leaveEntry.firstName} ${leaveEntry.lastName}</td>
                            <td>${leaveEntry.startDate}</td>
                            <td>${leaveEntry.endDate}</td>
                            <td>${leaveEntry.leaveType}</td>
                            <td>${leaveEntry.leaveStatus}</td>
                            <td class="leave-department" style="display: none;">${leaveEntry.department}</td>
                        `;
                        leaveTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching leave data:', error);
                });
        }

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


    //LOGIN AND LEAVE REQUEST
    document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const submitButton = document.getElementById('submitLeaveRequest');
    const confirmLeaveButton = document.getElementById('confirm-leave');
    const confirmLeaveModal = document.getElementById('confirmLeaveModal');
    const closeButtons = document.querySelectorAll('.close');
    const cancelLeaveButton = document.getElementById('cancel-leave');
    const deleteLeaveButton = document.getElementById('delete-leave');
    
    // Elements related to login form
    const loginButton = document.querySelector('.login-form button');
    const loginContainer = document.getElementById('login');
    const hrMainContainer = document.getElementById('hr_main');
    const employeeMainContainer = document.getElementById('employee_main');
    const userIcon = document.getElementById('userIcon');

    let leaveData = {};  // Temporary data for leave request
    let requestData = null;  // Holds leave request data for later submission
    let employeeID = null; // Employee ID will be set after login

    // Disable submit button initially for leave request
    submitButton.disabled = true;

    // Function to enable submit button if both dates are filled in
    function checkDates() {
        if (startDateInput.value && endDateInput.value) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

    startDateInput.addEventListener('change', checkDates);
    endDateInput.addEventListener('change', checkDates);
    
    function populateAttendanceTable() {
        fetch('index.php?action=getEmployeeAttendance')
        .then(response => response.json())
            .then(data => {
                // Clear existing rows
                attendanceTable.innerHTML = '';

                // Populate the table
                data.forEach(attendance => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${attendance.date}</td>
                        <td>${attendance.TimeIn}</td>
                        <td>${attendance.TimeOut}</td>
                        <td>${attendance.hoursWorked}</td>
                    `;
                    attendanceTable.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching employee data:', error);
            });
    }

    function populateLeaveCounts(){
        fetch('index.php?action=countLeaves')
        .then(response => response.json())
            .then(data => {
                // Clear existing rows
                leaveCountsTable.innerHTML = '';
                // Populate the table
                data.forEach(employeeLeave => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${employeeLeave.leaveType}</td>
                        <td>${employeeLeave.leaveCount}</td>
                    `;
                    leaveCountsTable.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching employee data:', error);
            });
    }

    function populateLeaveHistory(){
        fetch('index.php?action=getEmployeeLeaveHistory')
        .then(response => response.json())
            .then(data => {
                // Clear existing rows
                employeeLeaveHistoryTable.innerHTML = '';
                // Populate the table
                data.forEach(leaveEntry => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${leaveEntry.leaveID}</td>
                        <td>${leaveEntry.startDate}</td>
                        <td>${leaveEntry.endDate}</td>
                        <td>${leaveEntry.leaveType}</td>
                        <td>${leaveEntry.leaveDuration}</td>
                        <td>${leaveEntry.leaveStatus}</td>
                        <td id="view-icon-cell"><i class="fa-solid fa-eye view-leave-icon" data-employeeLeave-id=${leaveEntry.leaveID} style="cursor: pointer;"></i></td>
                    `;
                    employeeLeaveHistoryTable.appendChild(row);
                });
                attachViewLeaveListeners()
            })
            .catch(error => {
                console.error('Error fetching employee data:', error);
            });
    }

    
    function attachViewLeaveListeners() {
        document.querySelectorAll(".view-leave-icon").forEach(function (icon) {
            icon.addEventListener("click", function (event) {
                var leave_id = icon.getAttribute('data-employeeLeave-id');
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'fetchLeaveDetails', leaveID: leave_id }) // Send the payslipID to the backend
                })
                .then(response => response.json()) // Parse the response as JSON
                .then(data => {
                    if (data.success) {                        
                        // Set the leave details in the modal (replace with actual data)
                        document.getElementById('viewLeaveID').innerText = `Leave ID: ${data.leave.leaveID}`; // Replace with actual data
                        document.getElementById('viewLeaveStartDate').innerText = `From: ${data.leave.startDate}` // Replace with actual data
                        document.getElementById('viewLeaveEndDate').innerText = `To: ${data.leave.endDate}`; // Replace with actual data
                        document.getElementById('viewTypeOfLeave').innerText = `Kind of Leave: ${data.leave.leaveType}`; // Replace with actual data
                        document.getElementById('viewLeaveStatus').innerText = `Status: ${data.leave.leaveStatus}`; // Replace with actual data

                        // Display the leave details modal
                        document.getElementById('leaveDetailsModal').style.display = 'block';
                    } else {
                        alert('Failed to fetch leave details: ' + data.message); // Show error message
                    }
                })
                .catch(error => {
                    console.error('Error fetching leave details:', error);
                    alert('An error occurred while fetching leave details.');
                });
                });
            });
        };

    function populateEmployeePayrollOverview(){
        fetch('index.php?action=getCurrentPayroll')
        .then(response => response.json())
            .then(data => {
                // Clear existing rows
                payrollBreakdownTable.innerHTML = '';
                // Populate the table
                const breakdown = document.createElement('tr');
                breakdown.innerHTML = `
                    <td>Hours Worked: ${data.totalHoursWorked} hours
                    <br>Pay per Hour: $ ${data.ratePerHour}
                    <br>Deductions: $ ${data.deductions}
                    </td>
                `;
                payrollBreakdownTable.appendChild(breakdown);
                const netPayRow = document.createElement('tr');
                netPayRow.innerHTML = `<td id="netPay">Net Pay: ${data.netPay}</td>`;
                payrollBreakdownTable.appendChild(netPayRow);
            })
            .catch(error => {
                console.error('Error fetching payroll data:', error);
            });
    }

    function populateEmployeePaymentHistoryTable(){
        fetch('index.php?action=getEmployeePaymentHistory')
        .then(response => response.json())
            .then(data => {
                employeePaymentHistoryTable.innerHTML = '';
                // Populate the table
                data.forEach(paymentEntry => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${paymentEntry.payrollID}</td>
                        <td>${paymentEntry.paymentDate}</td>
                        <td>$ ${paymentEntry.netPay}</td>
                        <td>Completed</td>
                    `;
                    employeePaymentHistoryTable.appendChild(row);
                });
                attachViewLeaveListeners()
            })
            .catch(error => {
                console.error('Error fetching employee data:', error);
            });
    }

    // LOGIN - handling login logic
    loginButton.addEventListener('click', function (event) {
        event.preventDefault();  // Prevent form submission
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        // Validate username and password inputs
        if (!username || !password) {
            alert('Please enter both username and password');
            return;
        }

        // Send login data to the server
        fetch('index.php', { // change URL
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'login', username, password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Save employee ID
                employeeID = data.employeeID;
                //Populate Dynamic Employee Details
                document.getElementById("employeeFullName").innerText = data.employeeName;
                document.getElementById("jobTitle").innerText = data.position;
                document.getElementById("workDepartment").innerText = data.department;
                document.getElementById("employeeContactInformation").innerText = data.contactInformation;
                
                // Hide login container and show the relevant main container based on department
                loginContainer.classList.add('hidden');
                if (data.department === 'HR Department') {
                    populateEmployeeTable(departmentSelect.value);
                    populatePayrollTable();
                    populateLeaveTable();
                    hrMainContainer.classList.remove('hidden');
                } else {
                    populateAttendanceTable();
                    populateLeaveCounts();
                    populateLeaveHistory();
                    populateEmployeePayrollOverview();
                    populateEmployeePaymentHistoryTable();
                    employeeMainContainer.classList.remove('hidden');
                }
            } else {
                alert(data.message); // Show message if login failed
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // LOGOUT - handling logout logic
    userIcon.addEventListener('click', function () {
        // Make an API call to log out the user
        fetch('index.php', { // Change URL as needed
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'logout' }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide main containers and show the login container
                hrMainContainer.classList.add('hidden');
                employeeMainContainer.classList.add('hidden');
                loginContainer.classList.remove('hidden');

                // Clear employee ID and refresh the page
                employeeID = null;
                location.reload();
            } else {
                alert(data.message); // Show error message on failure
            }
        })
        .catch(error => {
            console.error('Error during logout:', error);
        });
    });

    // Handle the delete leave button click of the leave details modal
    deleteLeaveButton.addEventListener('click', function() {
        alert('Delete button clicked');

        // Fetch the leave ID from the modal
        const leaveID = document.getElementById('viewLeaveID').innerText.split(': ')[1];
        
        // Send the leave ID to the server to delete the leave
        fetch('index.php?action=deleteLeave&leaveID=' + leaveID, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide the leave details modal
                document.getElementById('leaveDetailsModal').style.display = 'none';
                // Refresh the leave history table
                populateLeaveHistory();
                alert('Leave deleted successfully!');
            } else {
                alert('Failed to delete leave: ' + data.message);
            }
            })
        .catch(error => {
            console.error('Error during delete leave:', error);
            alert('An error occurred while deleting the leave.');
        });
    });


    // Handle the "Submit Leave Request" button click to show the confirmation modal
    submitButton.addEventListener('click', function () {
        leaveData.startDate = startDateInput.value;
        leaveData.endDate = endDateInput.value;
        leaveData.leaveType = document.getElementById('leave-type').value;

        // Store the leave request data (but do not submit yet)
        requestData = {
            leaveType: leaveData.leaveType,
            startDate: leaveData.startDate,
            endDate: leaveData.endDate,
            employeeID: employeeID  // Include employeeID in the request data
        };

        // Update modal content with leave details
        document.getElementById('modalLeaveStartDate').innerText = `From: ${leaveData.startDate}`;
        document.getElementById('modalLeaveEndDate').innerText = `To: ${leaveData.endDate}`;
        document.getElementById('modalLeaveType').innerText = `Kind of Leave: ${leaveData.leaveType}`;

        // Show the confirmation modal
        confirmLeaveModal.style.display = 'block';
    });

    // Close the modal when the close button (x) or cancel button is clicked
    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            confirmLeaveModal.style.display = 'none'; // Close modal
            requestData = null;  // Do not submit the request if modal is closed
        });
    });

    // Close modal and reset requestData if cancel button is clicked
    cancelLeaveButton.addEventListener('click', function () {
        confirmLeaveModal.style.display = 'none';  // Close modal
        requestData = null;  // Reset the request data so it doesn't submit
    });

    // Handle confirmation of the leave request
    confirmLeaveButton.addEventListener('click', function () {
        if (requestData) {
            // Close the modal before submitting data
            confirmLeaveModal.style.display = 'none';

            // Submit the leave request to the server
            fetch('index.php', { // change URL
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'  // URL-encoded content type
                },
                body: new URLSearchParams({
                    action: 'requestLeave',  // Identify the action on the server
                    leaveType: requestData.leaveType,
                    startDate: requestData.startDate,
                    endDate: requestData.endDate,
                    employeeID: requestData.employeeID  // Pass employee ID along with the request data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message if request was submitted successfully
                    alert("Your leave request has been successfully submitted!");
                } else {
                    // Show error message if there was an issue
                    alert('Error requesting leave: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error during fetch:', error);
                alert('An error occurred while submitting your leave request.');
            });
        }
    });
});

    </script>
    </body>
</html>