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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #F6F4F0;
            color: #fff;
            padding: 10px 20px;
        }
        h2 {
            margin: 0;
            font-family: "Poppins", sans-serif;
            color: #4DA1A9;
            font-size: 30px;
            font-weight: 900;
        }
        .header-container h2 {
            margin: 0;
            color: #F6F4F0;
            font-family: "Poppins", sans-serif;
            font-size: 30px;
            font-weight: 900;
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
        .hidden {
            display: none;
        }
        /* Profile container styles */
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
            height: 100px;
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile h2, .profile p {
            margin: 0;
            padding: 2px 0; /* Reduced padding to lessen spacing */
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
        .button-container {
            display: flex;
            justify-content: space-around;
            width: 100%;
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
            color: #2E5077;
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
        .leave-flex-container {
            display: flex;
            justify-content: space-between;
            height: 50%;
        }
        #remaining-leaves {
            width: 40%;
            height: 80%;
            overflow: auto;
        }
        #leave-request {
            width: 60%;
            height: 80%;
            overflow: auto;
        }
        #leave-overview {
            width: 90%;
            height: 50%;
            overflow: auto;
        }
        #leave-overview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        input[type="date"] {
            padding: 10px 10px; 
            margin: 10px;
            background-color: #F6F4F0; 
            color: #2E5077; 
            border: 1px solid #2E5077; /* Added border */
            border-radius: 15px; 
            font-family: "Poppins", sans-serif;
            font-style: normal;
            font-size: 14px;
        }
        #leave-request-buttons {
            text-align: center;
            margin-top: 20px;
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
        /* Payroll Section */
        .payroll-section {
            background-color: #F6F4F0;  
            border-radius: 10px;
            font-family: "Poppins", sans-serif;
        }

        .payroll-section h1 {
            color: #2E5077;
            margin-bottom: 10px;
        }

        .payroll-section .highlighted {
            color: #4DA1A9;
            font-weight: bold;
        }

        .breakdown {
            margin-top: 15px;
            background-color: #F6F4F0;
            padding: 20px;
            border-radius: 10px;
        }

        .breakdown .section-header {
            background-color: #2E5077;
            color: #F6F4F0;
            padding: 10px;
            border-radius: 5px;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .breakdown table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            border: 1px solid black; /* Outer border */
        }

        .breakdown table td {
            padding: 8px 10px;
            vertical-align: top;
            border: none; /* Removed inner border */
        }

        .breakdown .right-align {
            text-align: right;
        }

        .breakdown .highlighted {
            color: #4DA1A9;
            font-weight: bold;
        }

        .breakdown .total td {
            background-color: #4DA1A9;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 18px;
            padding: 10px;
        }

        /* Payment History Section */
        .payment-history-section {
            margin-top: 40px;
            background-color: #F6F4F0;
            padding: 20px;
            border-radius: 10px;
        }

        .payment-history-section h3 {
            font-size: 22px;
            color: #2E5077;
            margin-bottom: 15px;
        }

        .payment-history-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-history-section table th,
        .payment-history-section table td {
            padding: 10px;
            text-align: left;
        }

        .payment-history-section table th {
            background-color: #2E5077;
            color: #F6F4F0;
            font-size: 16px;
        }

        .payment-history-section table td {
            font-size: 14px;
            border-bottom: 1px solid #D1D5DB;
        }

        .payment-history-section table td button {
            background-color: #2E5077;
            color: #FFFFFF;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .payment-history-section table td button:hover {
            background-color: #4DA1A9;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="workflow logo.png" alt="Workflow Logo" width="100" align="left">
    </div>

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
                <!-- Time will be displayed here -->
            </div>
            <div class="button-container">
                <button onclick="recordBreak()">BREAK</button>
                <button onclick="recordTimeout()">TIME OUT</button>
            </div>
        </div>
    </div>

    <nav>
        <div class="navigation active" id="attendance_nav">Attendance</div>
        <div class="navigation" id="leave_nav">Leave</div>
        <div class="navigation" id="payroll_nav">Payroll</div>
    </nav>

    <div class="main" id="attendance_container">
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
    <div class="main hidden" id="leave_container">
        <div class="leave-flex-container">
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
                    <input type="date" id="end-date" name="end-date"><br><br>
                    <div class="button-container" id="leave-request-buttons">
                        <button type="submit">Submit</button>
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
                </thead>
                <tbody>
                    <tr>
                        <td>01/09/2025</td>
                        <td>01/10/2025</td>
                        <td>Vacation</td>
                        <td>1 day</td>
                        <td>Approved</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payroll Container -->
    <div class="main hidden" id="payroll_container">
    <!-- Payroll Overview Section -->
    <div class="payroll-section">
        <h1>Payroll Overview</h1>
        <h3 class="upcoming-payroll">
            Upcoming Payroll Release: <span class="highlighted">December 29, 2024</span>
        </h3>
        <div class="breakdown">
            <h3 class="section-header">EXPECTED BREAKDOWN OF PAYSLIP</h3>
            <table>
                <tr>
                    <td>Hours worked</td>
                    <td class="right-align">24 hrs</td>
                </tr>
                <tr>
                    <td>Pay per hour</td>
                    <td class="right-align">P300.00/hr</td>
                </tr>
                <tr>
                    <td>Deductions:</td>
                    <td>- Taxes (10%)</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="right-align highlighted">P30.00</td>
                </tr>
                <tr class="total">
                    <td>TOTAL AMOUNT:</td>
                    <td class="right-align total-amount">P770.00</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Payment History Section -->
    <div class="payment-history-section">
        <h3>Payment History</h3>
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
                    <td>#123456</td>
                    <td>November 25, 2024</td>
                    <td>8:09:30</td>
                    <td>P800</td>
                    <td>Completed</td>
                    <td><button>View</button></td>
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>
    </div>
</div>

    <script>
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

        function recordBreak() {
            alert('Break recorded!');
        }

        function recordTimeout() {
            alert('Time out recorded!');
        }

        // Add active class to the first navigation
        document.getElementById('leave_nav').addEventListener('click', function() {
            document.getElementById('attendance_container').classList.add('hidden');
            document.getElementById('payroll_container').classList.add('hidden');
            document.getElementById('leave_container').classList.remove('hidden');
            document.getElementById('leave_nav').classList.add('active');
            document.getElementById('attendance_nav').classList.remove('active');
            document.getElementById('payroll_nav').classList.remove('active');
        });

        // Add active class to the second navigation
        document.getElementById('attendance_nav').addEventListener('click', function() {
            document.getElementById('leave_container').classList.add('hidden');
            document.getElementById('payroll_container').classList.add('hidden');
            document.getElementById('attendance_container').classList.remove('hidden');
            document.getElementById('attendance_nav').classList.add('active');
            document.getElementById('leave_nav').classList.remove('active');
            document.getElementById('payroll_nav').classList.remove('active');
        });

        // Add active class to the third navigation
        document.getElementById('payroll_nav').addEventListener('click', function() {
            document.getElementById('attendance_container').classList.add('hidden');
            document.getElementById('leave_container').classList.add('hidden');
            document.getElementById('payroll_container').classList.remove('hidden');
            document.getElementById('payroll_nav').classList.add('active');
            document.getElementById('attendance_nav').classList.remove('active');
            document.getElementById('leave_nav').classList.remove('active');
        });

    </script>


</body>
</html>
