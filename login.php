<?php
ini_set('display_errors', 1); // Display all errors
error_reporting(E_ALL); // Show all errors

$host = ''; // Hostname or IP address
$db = 'u415861906_infosec2222'; // Database name
$user = 'root'; // MySQL username
$port = "3307"; // MySQL port
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
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve username and password from POST request
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Sanitize user input to prevent SQL injection
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    // Validate the credentials
    try {
        $sql = "SELECT * FROM USERCREDENTIALS WHERE username = :username AND password = :password";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'password' => $password]);
        $user = $stmt->fetch();

        if ($user) {
            // If user exists, fetch the department of the employee
            $employeeId = $user['employeeID'];
            $sql = "SELECT department FROM EMPLOYEE WHERE employeeID = :employeeID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['employeeID' => $employeeId]);
            $employee = $stmt->fetch();

            if ($employee) {
                // Return the appropriate response based on department
                echo json_encode(['status' => 'success', 'department' => $employee['department']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Department not found.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } catch (PDOException $e) {
        // Handle any database query errors
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
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
            <form id="loginForm">
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
                    <select id="department">
                        <option value="it">IT Department</option>
                        <option value="hr">HR Department</option>
                        <option value="finance">Finance Department</option>
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
                            <td>1</td>
                            <td>Doe</td>
                            <td>John</td>
                            <td>Web Developer</td>
                            <td>+09123456789</td>
                            <td>Active</td>
                            <td id="view-icon-cell"><i class="fa-solid fa-eye view-employee-icon" data-id="1" style="cursor: pointer;"></i></td>
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
                            <tbody id="leaveOverviewTableBody">
                                <tr>
                                    <td>1</td>
                                    <td>1</td>
                                    <td>John Doe</td>
                                    <td>01/06/2021</td>
                                    <td>01/10/2021</td>
                                    <td>Vacation</td>
                                    <td>Pending</td>
                                </tr>
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
                        <tr>
                            <td>3</td>
                            <td>Johnson</td>
                            <td>Emily</td>
                            <td>12/09/2020</td>
                            <td>Received</td>
                            <td id="view-icon-cell"><i class="fa-solid fa-eye view-payslip-icon" data-id="1" style="cursor: pointer;"></i></td>
                        </tr>
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
                <input type="text" id="lastName" name="lastName"><br>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName"><br>
                <label for="contactInfo">Contact Information:</label>
                <input type="text" id="contactInfo" name="contactInfo"><br>
                <label for="department">Department:</label>
                <select id="department">
                    <option value="it">IT Department</option>
                    <option value="hr">HR Department</option>
                    <option value="finance">Finance Department</option>
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
                <input type="text" id="editContactInfo" name="contactInfo" readonly><br>
                <label for="editDepartment">Department:</label>
                <select id="editDepartment" name="department">
                    <option value="it">IT Department</option>
                    <option value="hr">HR Department</option>
                    <option value="finance">Finance Department</option>
                </select><br>
                <label for="editPosition">Position:</label>
                <input type="text" id="editPosition" name="position"><br>
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
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                    </tr> 
                </thead>
                <tbody>
                    <tr> 
                        <td>Vacation</td>
                        <td>01/06/2021</td>
                        <td>01/10/2021</td>
                        <td>5</td>
                    </tr> 
                    <tr> 
                        <td>Vacation</td>
                        <td>01/06/2021</td>
                        <td>01/10/2021</td>
                        <td>5</td>
                    </tr> 
                    <tr> 
                        <td>Vacation</td>
                        <td>01/06/2021</td>
                        <td>01/10/2021</td>
                        <td>5</td>
                    </tr> 
                </tbody>
            </table> 
            <h3>Payslip Overview</h3>
            <table>
                <thead>
                    <tr>
                        <th>Pay Period</th>
                        <th>Pay Date</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01/01/2021 - 01/15/2021</td>
                        <td>01/15/2021</td>
                        <td>$5000</td>
                    </tr>           
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
                <button id="approve-leave" class="submit">Approve</button>
                <button id="reject-leave" class="submit">Reject</button>
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
            var viewIcons = document.querySelectorAll('.view-employee-icon');
            viewIcons.forEach(function(icon) {
                icon.addEventListener('click', function() {
                    var employeeId = this.getAttribute('data-id');
                    // Fetch employee data based on employeeId (this is just a placeholder, you need to fetch the actual data)
                    document.getElementById('viewLastName').innerText = "Last Name: Doe"; // Replace with actual data
                    document.getElementById('viewFirstName').innerText = "First Name: John"; // Replace with actual data
                    document.getElementById('viewPosition').innerText = "Position: Web Developer"; // Replace with actual data
                    document.getElementById('viewContactInfo').innerText = "Contact Information: +09123456789"; // Replace with actual data
                    document.getElementById('viewDepartment').innerText = "Department: IT Department"; // Replace with actual data
                    document.getElementById('viewStatus').innerText = "Status: Active"; // Replace with actual data
                    document.getElementById('viewEmployeeModal').style.display = 'block';
                });
            });

            var add_btn = document.getElementById("add_employee");
            var edit_btn = document.getElementById("edit_employee");

            add_btn.onclick = function() {
                add_modal.style.display = "block";
            }

            edit_btn.onclick = function() {
                document.getElementById("editLastName").value = "Doe"; // Replace with actual data
                document.getElementById("editFirstName").value = "John"; // Replace with actual data
                document.getElementById("editContactInfo").value = "+09123456789"; // Replace with actual data
                document.getElementById("editDepartment").value = "it"; // Replace with actual data
                document.getElementById("editPosition").value = "Web Developer"; // Replace with actual data
                document.getElementById("editStatus").value = "Active"; // Replace with actual data
                edit_modal.style.display = "block";
                view_modal.style.display = "none";
            }
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

        // Function to add a leave request to the incoming leaves list
        function addLeaveRequest(employeeName, startDate, endDate, leaveType) {
            var leaveRequestDiv = document.createElement("div");
            leaveRequestDiv.className = "leave-request";
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
                leave_request_modal.style.display = "block";
            };
            document.getElementById("incoming-leaves-list").appendChild(leaveRequestDiv);
        }

        // Example usage
        addLeaveRequest("John Doe", "2021-03-06", "2021-03-10", "Vacation Leave");
        addLeaveRequest("Jane Smith", "2021-02-06", "2021-02-10", "Sick Leave");
        addLeaveRequest("Alice Johnson", "2024-12-30", "2025-01-03", "Vacation Leave");

        var approve_leave = document.getElementById("approve-leave");
        var reject_leave = document.getElementById("reject-leave");

        // Add function to approve leave request
        approve_leave.onclick = function() {
            alert("Leave request approved!");
            leave_request_modal.style.display = "none";
        }

        // Add function to reject leave request
        reject_leave.onclick = function() {
            alert("Leave request rejected!");
            leave_request_modal.style.display = "none";
        }

        // Filters leave overview in main table and leave report
        function filterLeaveOverview() {
            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;
            var department = document.getElementById('departmentFilter').value;

            var rows = document.querySelectorAll('#leaveOverviewTableBody tr');
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
        document.addEventListener('DOMContentLoaded', function() {
    const loginButton = document.querySelector('.login-form button');
    const loginContainer = document.getElementById('login');
    const hrMainContainer = document.getElementById('hr_main');
    const employeeMainContainer = document.getElementById('employee_main');

    document.getElementById('loginForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        // Perform AJAX request to validate the user credentials
        fetch('login.php', {
            method: 'POST',
            body: new URLSearchParams({
                'username': username,
                'password': password
            })
        })
        .then(response => response.text())  // Get the raw response as text first
        .then(data => {
            try {
                const jsonData = JSON.parse(data);  // Attempt to parse the response as JSON
                if (jsonData.status === 'success') {
                    loginContainer.classList.add('hidden');
                    if (jsonData.department === 'HR Department') {
                        hrMainContainer.classList.remove('hidden');
                    } else {
                        employeeMainContainer.classList.remove('hidden');
                    }
                } else {
                    alert('Error: ' + jsonData.message);
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                alert('An error occurred while processing the response.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
    });
});

    </script>
    </body>
