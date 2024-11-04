<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database configuration
require_once "config.php";

// Fetch patients with their appointments (LEFT JOIN)
$sql_patients = "SELECT Patients.id, Patients.name, Patients.age, Patients.gender, Patients.address, 
                    Appointments.appointment_date 
                 FROM Patients 
                 LEFT JOIN Appointments ON Patients.id = Appointments.patient_id";

$stmt_patients = $pdo->prepare($sql_patients);
$stmt_patients->execute();
$patients = $stmt_patients->fetchAll(PDO::FETCH_ASSOC);

// Fetch doctors with their appointments (RIGHT JOIN)
$sql_doctors = "SELECT Doctors.id, Doctors.name, Doctors.specialty, Doctors.email, 
                    Appointments.appointment_date 
                FROM Doctors 
                RIGHT JOIN Appointments ON Doctors.id = Appointments.doctor_id";

$stmt_doctors = $pdo->prepare($sql_doctors);
$stmt_doctors->execute();
$doctors = $stmt_doctors->fetchAll(PDO::FETCH_ASSOC);

// Fetch combined list of patients and doctors (UNION)
$sql_union = "SELECT id, name, 'Patient' AS type FROM Patients 
              UNION 
              SELECT id, name, 'Doctor' AS type FROM Doctors";

$stmt_union = $pdo->prepare($sql_union);
$stmt_union->execute();
$individuals = $stmt_union->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions for adding patients, doctors, and appointments
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_patient'])) {
        // Add new patient
        $name = $_POST['patient_name'];
        $age = $_POST['patient_age'];
        $gender = $_POST['patient_gender'];
        $address = $_POST['patient_address'];

        $insert_sql = "INSERT INTO Patients (name, age, gender, address) VALUES (:name, :age, :gender, :address)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':age', $age);
        $insert_stmt->bindParam(':gender', $gender);
        $insert_stmt->bindParam(':address', $address);
        $insert_stmt->execute();
    } elseif (isset($_POST['add_doctor'])) {
        // Add new doctor
        $name = $_POST['doctor_name'];
        $specialty = $_POST['doctor_specialty'];
        $email = $_POST['doctor_email'];
        $added_by = $_SESSION["id"]; // ID of the logged-in user who added the doctor

        $insert_sql = "INSERT INTO Doctors (name, specialty, email, added_by) VALUES (:name, :specialty, :email, :added_by)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':specialty', $specialty);
        $insert_stmt->bindParam(':email', $email);
        $insert_stmt->bindParam(':added_by', $added_by);
        $insert_stmt->execute();
    } elseif (isset($_POST['schedule_appointment'])) {
        // Schedule a new appointment
        $patient_id = $_POST['patient_id'];
        $doctor_id = $_POST['doctor_id'];
        $appointment_date = $_POST['appointment_date'];

        $insert_sql = "INSERT INTO Appointments (patient_id, doctor_id, appointment_date) VALUES (:patient_id, :doctor_id, :appointment_date)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->bindParam(':patient_id', $patient_id);
        $insert_stmt->bindParam(':doctor_id', $doctor_id);
        $insert_stmt->bindParam(':appointment_date', $appointment_date);
        $insert_stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hospital Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000; /* Black background */
            color: #fff; /* White text */
        }
        .navbar {
            background-color: #000; /* Black navbar */
        }
        .navbar-brand, .logout-btn {
            color: #ff69b4; /* Pink color */
        }
        .container {
            margin-top: 20px;
        }
        .table {
            background-color: #fff; /* White background for table */
            color: #000; /* Black text for table */
        }
        .table th, .table td {
            text-align: center;
        }
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-section {
            background-color: #222; /* Dark gray background for form sections */
            padding: 20px;
            border-radius: 5px;
        }
        .btn-success {
            background-color: #ff69b4; /* Pink button */
            border-color: #ff69b4; /* Pink border */
        }
        .btn-success:hover {
            background-color: #d15599; /* Darker pink on hover */
            border-color: #d15599; /* Darker pink border on hover */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Hospital Management System</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link logout-btn" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2>Add New Patient and Doctor</h2>
    <div class="form-container">
        <div class="form-section">
            <h4>Add New Patient</h4>
            <form action="" method="post">
                <div class="form-group">
                    <label for="patient_name">Name</label>
                    <input type="text" name="patient_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="patient_age">Age</label>
                    <input type="number" name="patient_age" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="patient_gender">Gender</label>
                    <select name="patient_gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="patient_address">Address</label>
                    <input type="text" name="patient_address" class="form-control" required>
                </div>
                <button type="submit" name="add_patient" class="btn btn-success">Add Patient</button>
            </form>
        </div>

        <div class="form-section">
            <h4>Add New Doctor</h4>
            <form action="" method="post">
                <div class="form-group">
                    <label for="doctor_name">Name</label>
                    <input type="text" name="doctor_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="doctor_specialty">Specialty</label>
                    <input type="text" name="doctor_specialty" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="doctor_email">Email</label>
                    <input type="email" name="doctor_email" class="form-control" required>
                </div>
                <button type="submit" name="add_doctor" class="btn btn-success">Add Doctor</button>
            </form>
        </div>
    </div>

    <h2>Schedule Appointment</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="patient_id">Select Patient</label>
            <select name="patient_id" class="form-control" required>
                <option value="">Select a Patient</option>
                <?php foreach ($patients as $patient): ?>
                    <option value="<?php echo $patient['id']; ?>"><?php echo htmlspecialchars($patient['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="doctor_id">Select Doctor</label>
            <select name="doctor_id" class="form-control" required>
                <option value="">Select a Doctor</option>
                <?php foreach ($doctors as $doctor): ?>
                    <option value="<?php echo $doctor['id']; ?>"><?php echo htmlspecialchars($doctor['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="appointment_date">Appointment Date</label>
            <input type="date" name="appointment_date" class="form-control" required>
        </div>
        <button type="submit" name="schedule_appointment" class="btn btn-success">Schedule Appointment</button>
    </form>

    <h2>Data View</h2>
    <button id="leftJoinBtn" class="btn btn-primary">LEFT JOIN</button>
    <button id="rightJoinBtn" class="btn btn-primary">RIGHT JOIN</button>
    <button id="unionJoinBtn" class="btn btn-primary">UNION</button>

    <div id="dataTable" class="table-responsive mt-4">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated here using JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('leftJoinBtn').addEventListener('click', function() {
    let tableBody = document.querySelector('#dataTable tbody');
    tableBody.innerHTML = ''; // Clear previous data

    <?php foreach ($patients as $patient): ?>
        tableBody.innerHTML += `<tr>
            <td><?php echo $patient['id']; ?></td>
            <td><?php echo htmlspecialchars($patient['name']); ?></td>
            <td><?php echo htmlspecialchars($patient['appointment_date'] ? $patient['appointment_date'] : 'No appointment'); ?></td>
        </tr>`;
    <?php endforeach; ?>
});

document.getElementById('rightJoinBtn').addEventListener('click', function() {
    let tableBody = document.querySelector('#dataTable tbody');
    tableBody.innerHTML = ''; // Clear previous data

    <?php foreach ($doctors as $doctor): ?>
        tableBody.innerHTML += `<tr>
            <td><?php echo $doctor['id']; ?></td>
            <td><?php echo htmlspecialchars($doctor['name']); ?></td>
            <td><?php echo htmlspecialchars($doctor['appointment_date'] ? $doctor['appointment_date'] : 'No patients'); ?></td>
        </tr>`;
    <?php endforeach; ?>
});

document.getElementById('unionJoinBtn').addEventListener('click', function() {
    let tableBody = document.querySelector('#dataTable tbody');
    tableBody.innerHTML = ''; // Clear previous data

    <?php foreach ($individuals as $individual): ?>
        tableBody.innerHTML += `<tr>
            <td><?php echo $individual['id']; ?></td>
            <td><?php echo htmlspecialchars($individual['name']); ?></td>
            <td><?php echo htmlspecialchars($individual['type']); ?></td>
        </tr>`;
    <?php endforeach; ?>
});
</script>

</body>
</html>
