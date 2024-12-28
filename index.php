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
            border: none;
            border-radius: 5px; 
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
            width: 550px; 
            border-radius: 20px;
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
        input[type="text"] {
            width: 60%; 
            padding: 5px 10px; 
            margin: 8px 0; 
            box-sizing: border-box; 
            font-size: 16px; 
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
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#viewEmployeeModal" id="view-employee-link" data-id="1">1</a></td>
                        <td>Doe</td>
                        <td>John</td>
                        <td>Web Developer</td>
                        <td>+09123456789</td>
                        <td>Active</td>
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
                    <i class="fa-solid fa-sort"></i>
                </div>
                <table id="incoming-leaves-table">
                    <tbody>
                        <tr>
                            <td class="leave">
                                <p><b>Employee ID: 1</b></p>
                                <p>Start Date: 01/06/2021</p>
                                <p>End Date: 01/10/2021</p>
                                <p>Kind of Leave: Vacation</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="container" id="leave-overview">
                <div id="leave-overview-header">
                    <h2>Leave Overview</h2>
                    <i class="fa-solid fa-download"></i>                
                </div>
                <div id="leave-table">
                    <table>
                        <thead>
                            <tr>
                                <th>LEAVE ID</th>
                                <th>EMPLOYEE ID</th>
                                <th>START DATE</th>
                                <th>END DATE</th>
                                <th>KIND OF LEAVE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>1</td>
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
            </div>
            <div id="right">
                <i class="fa-solid fa-download" id="generate-payroll"></i>             
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
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>3</td>
                        <td>Johnson</td>
                        <td>Emily</td>
                        <td>12/09/2020</td>
                        <td>Received</td>
                    </tr>
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
                <input type="text" id="status" name="status"><br><br>
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
                <input type="text" id="status" name="status"><br><br>
                <div class="form-group">
                    <button id="edit-employee" class="submit" type="submit">Edit Employee</button>
                </div>
            </form>
        </div>
    </div>

    <!--View Employee Details-->
    <div id="viewEmployeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="view_employee_header">
                <h2>View Employee</h2>
                <i class="fa-solid fa-pen-to-square" id="edit_employee"></i>
            </div>
            <hr>
            <p>Last Name</p>
            <p>First Name</p>
            <p>Contact Information</p>
            <p>Department</p>
            <p>Position</p>
            <p>Status</p>
            <h3>Leave History</h3>
            <table>
                <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                </tr>  
            </table> 
            <h3>Payslip Overview</h3>
            <table>
                <tr>
                    <th>Pay Period</th>
                    <th>Pay Date</th>
                    <th>Net Pay</th>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // Get the modal
        var add_modal = document.getElementById("addEmployeeModal");
        var edit_modal = document.getElementById("editEmployeeModal");
        var view_modal = document.getElementById("viewEmployeeModal");

        // Get the button that opens the add modal
        var add_btn = document.getElementById("add_employee");
        

        // Get the <span> element that closes the modals
        var spans = document.getElementsByClassName("close");

        // When the user clicks the button, open the add modal 
        add_btn.onclick = function() {
            add_modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modals
        for (var i = 0; i < spans.length; i++) {
            spans[i].onclick = function() {
                add_modal.style.display = "none";
                edit_modal.style.display = "none";
                view_modal.style.display = "none";
            }
        }

        // When the user clicks anywhere outside of the modals, close them
        window.onclick = function(event) {
            if (event.target == add_modal) {
                add_modal.style.display = "none";
            }
            if (event.target == edit_modal) {
                edit_modal.style.display = "none";
            }
            if (event.target == view_modal) {
                view_modal.style.display = "none";
            }
        }

        // Get all view links
        var view_links = document.getElementById("view-employee-link");

        // When the user clicks a view link, open the view modal
        view_links.onclick = function(event) {
            // event.preventDefault();
            // var employeeId = this.getAttribute("data-id");

            //     // Populate the view modal with employee data (this is just a placeholder, you need to fetch the actual data)
            // document.getElementById("viewLastName").innerText = "Last Name: Doe"; // Replace with actual data
            // document.getElementById("viewFirstName").innerText = "First Name: John"; // Replace with actual data
            // document.getElementById("viewContactInfo").innerText = "Contact Information: +09123456789"; // Replace with actual data
            // document.getElementById("viewDepartment").innerText = "Department: IT Department"; // Replace with actual data
            // document.getElementById("viewPosition").innerText = "Position: Web Developer"; // Replace with actual data
            // document.getElementById("viewStatus").innerText = "Status: Active"; // Replace with actual data
            view_modal.style.display = "block";
            
        }

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