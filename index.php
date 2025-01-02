<?php
	$host = ''; // Hostname or IP address
	$db = 'u415861906_infosec2222'; // Database name
	$user = 'root'; // MySQL username
	$port = "3307";
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
		
	} catch (PDOException $e) {
		// Handle connection errors
		echo "Connection failed: " . $e->getMessage();
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
    
            // Fetch leave history
            $leaveSql = "SELECT leaveType, startDate, endDate, DATEDIFF(startDate, endDate) + 1 AS days
                         FROM leaverequest WHERE employeeID = ?";
            $stmt = $pdo->prepare($leaveSql);
            $stmt->execute([$employeeID]);
            $leaveHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Fetch payment history
            $paymentSql = "SELECT paymentDate, netPay 
                           FROM payroll WHERE employeeID = ?";
            $stmt = $pdo->prepare($paymentSql);
            $stmt->execute([$employeeID]);
            $paymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($employeeDetails) {
                echo json_encode([
                    "success" => true,
                    "employeeDetails" => $employeeDetails,
                    "leaveHistory" => $leaveHistory,
                    "paymentHistory" => $paymentHistory
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Employee not found."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
        exit;
    }

    if (isset($_GET['editEmployee'])) {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents("php://input"), true);
    
        $employeeID = $input['employeeID'];
        $contactInfo = $input['contactInformation'];
        $department = $input['department'];
        $position = $input['position'];
    
        try {
            $sql = "UPDATE employee 
                    SET contactInformation = ?, department = ?, position = ?
                    WHERE employeeID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contactInfo, $department, $position, $employeeID]);
    
            echo json_encode(["success" => true]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
        exit;
    }
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lastName'])) {
        $ln = $_POST['lastName'];
        $fn = $_POST['firstName'];
        $ci = $_POST['contactInformation'];
        $dp = $_POST['department'];
        $p = $_POST['position'];
    
        // Insert into the database
        $sql = "INSERT INTO employee (lastName, firstName, department, contactInformation, position, leaveBalance) VALUES (:ln, :fn, :dp, :ci, :p, 10)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['ln' => $ln, 'fn' => $fn, 'ci' => $ci, 'dp' => $dp, 'p' => $p]);
    
        // Redirect to the same page to prevent resubmission
        header("Location: index.php");
        exit();
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
        e.employeeID, e.lastName, e.firstName, 
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
        if ($data['action'] === 'fetchPayslipDetails' && isset($data['payrollID'])) {
            $payslipID = $data['payrollID'];
            $stmt = $pdo->prepare("SELECT * FROM payroll WHERE payrollID = ?");
            $stmt->execute([$payslipID]);
            $payslip = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($payslip) {
                echo json_encode(['success' => true, 'payslip' => $payslip]);
            } else {
                echo json_encode(['success' => false]);
            }
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
        table {
            overflow: auto;
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
        #employee-table {
            overflow: auto;
            margin: 10px;
            height: 86%;
        }
        .fa-plus {
            color: #F6F4F0;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }
        .header-container h1 {
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
            width: 460px;
            height: 100%;
            overflow: auto;
        }
        #incoming-leaves h2 {
            color: #2E5077;
            font-weight: 1000;
            margin: 5px 5px 10px;
        }
        #incoming-leaves-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #leave-overview {
            width: 910px;
            height: 100%;
        }
        .leave {
            background-color: #F6F4F0;
            padding: 10px;
            border: 1px solid black; 
            line-height: 0.8; 
        }
        .fa-sort {
            color: #2E5077;
            cursor: pointer;
            margin: 5px 10px 10px;
        }
        #leave-overview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #leave-overview h2 {
            color: #2E5077;
            font-size: 30px;
            font-weight: 1000;
            margin: 5px 5px 5px;
        }
        .fa-download {
            color: #2E5077;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }
        #leave-table {
            overflow: auto;
            margin-top: 0;
        }
        #generate-payroll {
            color: #F6F4F0;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
        }
        #payroll-table {
            overflow: auto;
            margin-top: 10;
            height: 86%;
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
            height: 75%
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
        .modal h2 {
            margin: 0;
            font-size: 30px;
            font-weight: bold;
            color: #4DA1A9;
        }
        .modal h3 {
            margin: 0;
            font-size: 24px;
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
        }
        .form-group {
            text-align: center; /* Center align the form group */
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
        .fa-pen-to-square {
            color: #4DA1A9;
            cursor: pointer;
            margin: 5px 10px 5px;
            font-size: 1.5em;
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
        input[type="date"] {
            padding: 10px 10px; 
            margin: 10px;
            background-color: #F6F4F0; 
            color: #2E5077; 
            border: 1px solid #2E5077; /* Added border */
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
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="workflow logo.png" alt="Workflow Logo" width="100" align="left">
    </div>

    <nav>
        <div class="navigation active" id="employee_nav">Employee</div>
        <div class="navigation" id="leave_nav">Leave</div>
        <div class="navigation" id="payroll_nav">Payroll</div>
    </nav>

    <div class="main" id="employee_container">
        <div class="header-container">
            <div id="left">
                <h1>Employees Overview</h1>
                <select id="department">
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
                <tbody>
                    <tr>
                        <?php
                            $sql = "SELECT * FROM employee";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll();

                            foreach ($result as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['employeeID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['firstName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contactInformation']); ?></td>
                                    <td>Present</td>
                                    <td id="view-icon-cell"><i class="fa-solid fa-eye view-employee-icon" data-id="<?php echo $row['employeeID']; ?>" style="cursor: pointer;"></i></td>
                                </tr>
                        <?php endforeach; ?>
                    </tr>
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
                                <option value="it">IT Department</option>
                                <option value="hr">HR Department</option>
                                <option value="finance">Finance Department</option>
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
                            $sql = "SELECT lr.leaveID, lr.employeeID, lr.startDate, lr.endDate, lr.leaveType, lr.leaveStatus, e.firstName, e.lastName 
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
                <select id="department">
                    <option value="it">IT Department</option>
                    <option value="hr">HR Department</option>
                    <option value="finance">Finance Department</option>
                </select>
                <select id="payPeriod">
                    <option value="jan">January</option>
                    <option value="feb">February</option>
                    <option value="mar">March</option>
                    <option value="apr">April</option>
                    <option value="may">May</option>
                    <option value="june">June</option>
                    <option value="july">July</option>
                    <option value="aug">August</option>
                    <option value="sep">September</option>
                    <option value="oct">October</option>
                    <option value="nov">November</option>
                    <option value="dec">December</option>
                </select>
            </div>
            <div id="right">
                <i class="fa-solid fa-download" id="generate-payroll"></i>  
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
                    <?php foreach ($payrollData as $payroll): ?>
                        <tr>
                            <td><?= htmlspecialchars($payroll['employeeID']) ?></td>
                            <td><?= htmlspecialchars($payroll['lastName']) ?></td>
                            <td><?= htmlspecialchars($payroll['firstName']) ?></td>
                            <td><?= htmlspecialchars($payroll['paymentDate']) ?></td>
                            <td>Received</td>
                            <td id="view-icon-cell">
                                <i class="fa-solid fa-eye view-payslip-icon" data-payslip-id="<?= htmlspecialchars($payroll['payrollID']) ?>" style="cursor: pointer;"></i>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Employee</h2>
            <hr>
            <form id="addEmployeeForm" method = "POST">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName"><br>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName"><br>
                <label for="contactInfo">Contact Information:</label>
                <input type="text" id="contactInfo" name="contactInformation"><br>
                <label for="department">Department:</label>
                <select id="department" name = "department">
                    <option value="IT Department">IT Department</option>
                    <option value="HR Department">HR Department</option>
                    <option value="Finance Department">Finance Department</option>
                </select><br>
                <label for="position">Position:</label>
                <input type="text" id="position" name="position"><br>
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="onLeave">On Leave</option>
                </select><br>
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
                <label for="editStatus">Status:</label>
                <select id="editStatus" name="status">
                    <option value="active">Active</option>
                    <option value="onLeave">On Leave</option>
                </select><br>
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
                <input type="text" id="inputEmployeeID" name="employeeID"><br>
                <label for="employeeName">Employee Name:</label>
                <input type="text" id="inputEmployeeName" name="employeeName" readonly><br>
                <label for="position">Position:</label>
                <input type="text" id="position" name="position" readonly><br>
                <label for="startDate">Start Pay Date:</label>
                <input type="date" id="startPayDate" name="startPayDate"><br>
                <label for="endDate">End Pay Date:</label>
                <input type="date" id="endPayDate" name="endPayDate"><br>
                <label for="hoursWorked">Hour/s Worked:</label>
                <input type="text" id="inputHoursWorked" name="hoursWorked" readonly><br>
                <label for="payPerHour">Pay per hour:</label>
                <input type="text" id="inputPayPerDay" name="payPerDay"><br>
                <label for="deduction">Deduction/s:</label>
                <input type="text" id="inputDeduction:" name="deduction"><br>
                <label for="tax">Net Pay:</label>
                <input type="text" id="calculateNetPay" name="netPay" readonly><br>
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

    <script>
        // Get the modal
        var add_modal = document.getElementById("addEmployeeModal");
        var edit_modal = document.getElementById("editEmployeeModal");
        var view_modal = document.getElementById("viewEmployeeModal");
        var leave_request_modal = document.getElementById("leaveRequestModal");
        var generate_payslip_modal = document.getElementById("generatePayslipModal");

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

        document.addEventListener('DOMContentLoaded', function() {
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
                            document.getElementById("viewLastName").innerText = "Last Name: " + details.lastName;
                            document.getElementById("viewFirstName").innerText = "First Name: " + details.firstName;
                            document.getElementById("viewContactInfo").innerText = "Contact Information: " + details.contactInformation;
                            document.getElementById("viewDepartment").innerText = "Department: " + details.department;
                            document.getElementById("viewPosition").innerText = "Position: " + details.position;
                            document.getElementById("viewStatus").innerText = "Status: " + details.status;

                            // Populate leave history
                            var leaveTbody = document.querySelector("#leaveHistoryTable tbody");
                            leaveTbody.innerHTML = ""; // Clear previous rows
                            data.leaveHistory.forEach(function (leave) {
                                var row = `
                                    <tr>
                                        <td>${leave.leaveType}</td>
                                        <td>${leave.endDate}</td>
                                        <td>${leave.startDate}</td>
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
                                        <td>${payment.paymentPeriod}</td>
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

            var add_btn = document.getElementById("add_employee");
            var edit_btn = document.getElementById("edit_employee");

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
                document.getElementById("editStatus").value = status;
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
        });

        document.getElementById("editEmployeeForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent form submission from reloading the page

            const employeeId = document.querySelector(".view-employee-icon").getAttribute("data-id");
            const contactInfo = document.getElementById("editContactInfo").value;
            const department = document.getElementById("editDepartment").value;
            const position = document.getElementById("editPosition").value;

            // Send the data to the server
            fetch("index.php?editEmployee=true", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    employeeID: employeeId,
                    contactInformation: contactInfo,
                    department: department,
                    position: position,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Employee details updated successfully!");

                        // Update the View Employee Modal with the new values
                        document.getElementById("viewContactInfo").innerText = "Contact Information: " + contactInfo;
                        document.getElementById("viewDepartment").innerText = "Department: " + department;
                        document.getElementById("viewPosition").innerText = "Position: " + position;

                        const employeeRow = document.querySelector(`.view-employee-icon[data-id='${employeeId}']`).closest("tr");
                        if (employeeRow) {
                            employeeRow.cells[3].innerText = position;           // Update position cell
                            employeeRow.cells[4].innerText = contactInfo;       // Update contact information cell
                        }

                        // Close the Edit Modal and show the View Modal
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
                        const leaveTableRow = document.querySelector(`tr[data-leave-id="${leaveID}"] td:last-child`);
                        if (leaveTableRow) {
                            leaveTableRow.textContent = leaveStatus;
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

        // Event listeners for Approve and Reject buttons
        document.addEventListener("DOMContentLoaded", function () {
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
        });


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
                var rowDepartment = row.cells[4].innerText;

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

        // Generate Payslip
        var generate_btn = document.getElementById("add_payslip");

        generate_btn.onclick = function() {
            generate_payslip_modal.style.display = 'block';
        }

        // View Payslip Details
        var view_payslip_icons = document.querySelectorAll('.view-payslip-icon');
        view_payslip_icons.forEach(function(icon) {
            icon.addEventListener('click', function() {
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

        // Close modal when clicking the close button
        document.querySelector('#PayslipOverviewModal .close').addEventListener('click', function() {
            document.getElementById('PayslipOverviewModal').style.display = 'none';
        });


        // Filter Payroll Table
        // function filterPayrollTable() {
        //     var selectedDepartment = document.getElementById('department').value;
        //     var selectedPayPeriod = document.getElementById('payPeriod').value;

        //     var rows = document.querySelectorAll('#payrollTableBody tr');
        //     var filteredRows = [];

        //     rows.forEach(function(row) {
        //         var rowDepartment = row.cells[4].innerText; // Assuming department is in the 5th column
        //         var rowPayPeriod = row.cells[3].innerText; // Assuming pay period is in the 4th column

        //         var showRow = true;

        //         if (selectedDepartment && rowDepartment !== selectedDepartment) {
        //             showRow = false;
        //         }
        //         if (selectedPayPeriod && !rowPayPeriod.includes(selectedPayPeriod)) {
        //             showRow = false;
        //         }

        //         if (showRow) {
        //             row.style.display = '';
        //             filteredRows.push(row.cloneNode(true));
        //         } else {
        //             row.style.display = 'none';
        //         }
        //     });

        //     return filteredRows;
        // }

        // document.getElementById('generatePayrollReport').addEventListener('click', function() {
        //     var filteredRows = filterPayrollTable();

        //     var modalTableBody = document.getElementById('payrollTableBody');
        //     modalTableBody.innerHTML = ''; // Clear existing rows
        //     filteredRows.forEach(function(row) {
        //         modalTableBody.appendChild(row);
        //     });

        //     // Set the filtered values in the modal
        //     document.getElementById('modalPayrollDepartment').innerText = "Department: " + document.getElementById('department').value || 'All Departments';
        //     document.getElementById('modalPayPeriod').innerText = "Pay Period: " + document.getElementById('payPeriod').value || 'N/A';

        //     var payrollReportModal = document.getElementById('payslipReportModal');
        //     payrollReportModal.style.display = 'block';
        // });

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
    </script>
</body>
</html>