<?php
session_start(); // Start session to maintain logged-in status

// Check if the admin is logged in
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit;
}

// Include header and other necessary files
include('header.php');
include('../encryption_helper.php'); // Include encryption helper file
$encryption_key = 'YourSecureKeyHere';

// Enable MySQLi error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Handle delete operation
if (isset($_POST["delete_id"])) {
    $id = $_POST['delete_id'];
    
    // Start a transaction to ensure data integrity
    $dbconnection->begin_transaction();

    try {
        // Prepared statement to delete associated boarders
        $delete_boarders_sql = $dbconnection->prepare("DELETE FROM book WHERE bhouse_id = ?");
        $delete_boarders_sql->bind_param("i", $id);
        $delete_boarders_sql->execute();

        // Prepared statement to delete the rental record
        $delete_rental_sql = $dbconnection->prepare("DELETE FROM rental WHERE rental_id = ?");
        $delete_rental_sql->bind_param("i", $id);
        $delete_rental_sql->execute();

        $dbconnection->commit(); // Commit the transaction
        echo "<script>Swal.fire('Deleted!', 'Record has been deleted.', 'success');</script>";
    } catch (Exception $e) {
        $dbconnection->rollback(); // Rollback transaction on error
        echo "<script>Swal.fire('Error!', '" . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
    }
}

// Pagination logic
$pageno = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
$no_of_records_per_page = 8;
$offset = ($pageno - 1) * $no_of_records_per_page;

// Get total pages count securely
$total_pages_sql = "SELECT COUNT(*) FROM rental";
$result_pages = $dbconnection->query($total_pages_sql);
$total_rows = $result_pages->fetch_array()[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);

// Fetch rentals with pagination
$sql = "SELECT * FROM rental ORDER BY id DESC LIMIT ?, ?";
$fetch_rentals = $dbconnection->prepare($sql);
$fetch_rentals->bind_param("ii", $offset, $no_of_records_per_page);
$fetch_rentals->execute();
$result = $fetch_rentals->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boarding House List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
/* Table styles */
.table {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    font-size: 14px;
}

.table thead th {
    background: #007bff;
    color: #fff;
    text-align: center;
}

.table tbody td {
    vertical-align: middle;
    text-align: center;
    padding: 10px;
}
/* Main layout container */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    width: 100%;
}

/* Sidebar styles */
.sidebar-container {
    width: 250px;
    background: #fff;
    border-right: 1px solid #e3e6f0;
    flex-shrink: 0;
}

/* Main content area */
.main-content {
    flex-grow: 1;
    padding: 20px;
    background: #f8f9fc;
    overflow-x: hidden;
}

/* Dashboard cards row */
.dashboard-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

/* Card styles */
.card-box {
    flex: 1;
    min-width: 240px;
    background-color: #ffffff;
    border: 1px solid #e3e6f0;
    border-radius: 5px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,.15);
    padding: 20px;
}

/* Chart containers */
.chart-container1, .chart-container2, .chart-container3 {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,.15);
    margin-bottom: 30px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }
    
    .sidebar-container {
        width: 100%;
        position: static;
        height: auto;
    }
    
    .main-content {
        padding: 15px;
    }
    
    .card-box {
        min-width: 100%;
    }
    
    .chart-container1, .chart-container2, .chart-container3 {
        width: 100% !important;
        height: 300px !important;
    }
}

/* Existing styles with improvements */
.widget-style3 {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.widget-data {
    flex-grow: 1;
}

.widget-icon {
    margin-left: 15px;
}

.font-24 {
    font-size: 20px !important;
}

.animated-icon {
    animation: pulse 1.3s infinite;
}

@keyframes pulse {
    0% { transform: scale(1.5); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

h3 {
    margin: 0 0 20px 0;
    color: #5a5c69;
    font-weight: 500;
}
  </style>
</head>

<div class="dashboard-container">
    <div class="sidebar-container">
        <?php include('sidebar.php'); ?>
    </div>
    <div class="main-content">  
        <br><br>
        <h3>Boarding House List</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Owner</th>
                        <th>View</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) {
                        $rent_id = $row['rental_id'];
                        $landlord_id = $row['register1_id'];
                        $encrypted_bh_id = encrypt($rent_id, $encryption_key);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <?php
                                $sql_ll = "SELECT * FROM register2 WHERE register1_id = ?";
                                $fetch_landlord = $dbconnection->prepare($sql_ll);
                                $fetch_landlord->bind_param("i", $landlord_id);
                                $fetch_landlord->execute();
                                $result_ll = $fetch_landlord->get_result();

                                while ($row_ll = $result_ll->fetch_assoc()) {
                                    echo htmlspecialchars($row_ll['firstname']);
                                    if (!empty($row_ll['middlename'])) {
                                        echo " " . htmlspecialchars($row_ll['middlename']);
                                    }
                                    echo " " . htmlspecialchars($row_ll['lastname']);
                                }
                                ?>
                            </td>
                            <td class="col-md-1"> 
                                <a href="../view.php?bh_id=<?php echo urlencode($encrypted_bh_id); ?>" class="btn btn-success">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>
                            </td>
                            <td class="col-md-1">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo $rent_id; ?>')">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <ul class="pagination">
                <li><a href="?pageno=1"><i class="fa fa-fast-backward" aria-hidden="true"></i> First</a></li>
                <li class="<?php if ($pageno <= 1) { echo 'disabled'; } ?>">
                    <a href="<?php if ($pageno > 1) { echo "?pageno=" . ($pageno - 1); } ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i> Prev</a>
                </li>
                <li class="<?php if ($pageno >= $total_pages) { echo 'disabled'; } ?>">
                    <a href="<?php if ($pageno < $total_pages) { echo "?pageno=" . ($pageno + 1); } ?>">Next <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
                </li>
                <li><a href="?pageno=<?php echo $total_pages; ?>">Last <i class="fa fa-fast-forward" aria-hidden="true"></i></a></li>
            </ul>
        </div>
    </div>
</div>

<form id="delete-form" action="" method="POST" style="display: none;">
    <input type="hidden" name="delete_id" id="delete_id">
</form>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete-form').submit();
        }
    })
}
</script>

<?php include('footer.php'); ?>
</body>
</html>