<?php include('header.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php 
$rental_id = $_GET['bh_id'];
$sql_register1 = "SELECT * FROM rental WHERE rental_id='$rental_id'";
$result_register1 = mysqli_query($dbconnection, $sql_register1);
while ($row_register1 = $result_register1->fetch_assoc()) {
    $register1_id = $row_register1['id'];
}

if (isset($_POST["booknow"])) {
    // Escape user inputs for security
    $name = mysqli_real_escape_string($dbconnection, $_POST['name']);
    $age = mysqli_real_escape_string($dbconnection, $_POST['age']);
    $gender = mysqli_real_escape_string($dbconnection, $_POST['gender']);
    $contact_number = "+63" . mysqli_real_escape_string($dbconnection, $_POST['contact_number']);
    $address = mysqli_real_escape_string($dbconnection, $_POST['Address']);

    // Insert booking details into the book table
    $sql_book = "INSERT INTO book (name, age, gender, contact_number, register1_id, bhouse_id, Address) VALUES ('$name', '$age', '$gender', '$contact_number', '$register1_id', '$rental_id', '$address')";

    if ($dbconnection->query($sql_book) === TRUE) {
        echo '<script type="text/javascript">
            Swal.fire({
                icon: "success",
                title: "Success",
                text: "Successfully Booked, wait for register1 to call you."
            });
        </script>';
    } else {
        echo '<script type="text/javascript">
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error in database: ' . $dbconnection->error . '"
            });
        </script>';
    }
}

if (isset($_POST['submitfeedback'])) {
    $boardernumber = "+63" . mysqli_real_escape_string($dbconnection, $_POST['boardersnumber']);
    $sqlfdbck = "SELECT * FROM book WHERE bhouse_id = '$rental_id' AND contact_number = '$boardernumber'";
    $resultfdbck = mysqli_query($dbconnection, $sqlfdbck);
    $countfdbck = mysqli_num_rows($resultfdbck);

    if ($countfdbck == 1) {
        $ratings = floatval($_POST['rate']); // Ensure the rating is treated as float
        $feedback = mysqli_real_escape_string($dbconnection, $_POST['feedbackmsg']);
        mysqli_query($dbconnection, "UPDATE book SET ratings = '$ratings', feedback = '$feedback' WHERE bhouse_id = '$rental_id' AND contact_number = '$boardernumber'");
        echo '<script type="text/javascript">
            Swal.fire({
                icon: "success",
                title: "Success",
                text: "Your feedback has been submitted."
            });
        </script>';
    } else {
        echo '<script type="text/javascript">
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Sorry! We couldn\'t find your number. You might not be a boarder here or your registered contact number is incorrect."
            });
        </script>';
    }
}
?>

<div class="container">
<br />
<br />

<?php
$sql = "SELECT * FROM rental WHERE rental_id='$rental_id'";
$result = mysqli_query($dbconnection, $sql);
while ($row = $result->fetch_assoc()) {
    $register1id = $row['register1_id'];
?>
<br />
<h2><?php echo $row['title']; ?></h2>
<div class="wrap">
    <div class="gallery">
        <?php 
        $sql_gallery = "SELECT * FROM gallery WHERE rental_id='$rental_id'";
        $result_gallery = mysqli_query($dbconnection, $sql_gallery);
        while ($row_gallery = $result_gallery->fetch_assoc()) { ?>
            <a href="uploadss/<?php echo $row_gallery['file_name']; ?>"><img src="uploadss/<?php echo $row_gallery['file_name']; ?>"></a>
        <?php } ?>
    </div>
</div>
<div class="slidebtn">
    <button class="prev"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
    <button class="next"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
</div>
<br />
<h5>₱ <?php echo $row['monthly']; ?> / Monthly</h5>
<h6><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo $row['address']; ?></h6>
<br />
<br />

<?php 
$freewifi = $row['wifi'] == 'yes' ? '<i class="fa fa-check-circle text-success" aria-hidden="true"></i>' : '<i class="fa fa-times-circle text-danger" aria-hidden="true"></i>';
$freewater = $row['water'] == 'yes' ? '<i class="fa fa-check-circle text-success" aria-hidden="true"></i>' : '<i class="fa fa-times-circle text-danger" aria-hidden="true"></i>';
$freekuryente = $row['kuryente'] == 'yes' ? '<i class="fa fa-check-circle text-success" aria-hidden="true"></i>' : '<i class="fa fa-times-circle text-danger" aria-hidden="true"></i>';
?>

<div class="row text-center">
    <div class="col">
        <div class="alert alert-success" role="alert">
            <i class="fa fa-wifi" aria-hidden="true"></i> FREE WIFI <?php echo $freewifi; ?>
        </div>
    </div>
    <div class="col">
        <div class="alert alert-success" role="alert">
            <i class="fa fa-tint" aria-hidden="true"></i> FREE WATER <?php echo $freewater; ?>
        </div>
    </div>
    <div class="col">
        <div class="alert alert-success" role="alert">
            <i class="fa fa-lightbulb-o" aria-hidden="true"></i> FREE KURYENTE <?php echo $freekuryente; ?>
        </div>
    </div>
</div>
<br />
<br />

<div class="row">
    <div class="col-md-8">
        <h3>Description</h3>
        <div class="card mb-4">
            <div class="card-body">
                <?php echo $row['description']; ?>
            </div>
        </div>
        <div class="map">
            <iframe width="600" height="450" src="<?php echo $row['map']; ?>"></iframe>
        </div>
    </div>
    <div class="col-md-4">
        <h3>Landlord's INFO</h3>
<?php
 
// Fetch Landlord's details
$sql_ll = "SELECT CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) AS name, email, contact_number, profile_photo 
           FROM register2 
           JOIN register1 ON register2.register1_id = register1.id 
           WHERE register1_id = ?";

// Use prepared statement to prevent SQL injection
if ($stmt = $dbconnection->prepare($sql_ll)) {
    $stmt->bind_param("i", $register1id); // Assuming $register1id is an integer
    $stmt->execute();
    $result_ll = $stmt->get_result();

    if ($result_ll && $row_ll = $result_ll->fetch_assoc()) {
        $name = $row_ll['name'];
        $email = $row_ll['email'];
        $contact_number = $row_ll['contact_number'];
        $profile_photo = $row_ll['profile_photo'];
    } else {
        echo "Error fetching register1 details.";
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $dbconnection->error;
}


// Fetch rental details to check slots
$sql_rental = "SELECT CAST(slots AS UNSIGNED) AS slots FROM rental WHERE rental_id = ?";
if ($stmt_rental = $dbconnection->prepare($sql_rental)) {
    $stmt_rental->bind_param("i", $rental_id); // Assuming $rental_id is an integer
    $stmt_rental->execute();
    $result_rental = $stmt_rental->get_result();

    if ($result_rental && $rental = $result_rental->fetch_assoc()) {
        $availableSlots = (int) $rental['slots']; // Cast to integer

        // Fetch current number of bookings
        $sql_bookings = "SELECT COUNT(*) AS booked_count FROM book WHERE bhouse_id = ?";
        if ($stmt_bookings = $dbconnection->prepare($sql_bookings)) {
            $stmt_bookings->bind_param("i", $rental_id); // Assuming $rental_id is used for bookings as well
            $stmt_bookings->execute();
            $result_bookings = $stmt_bookings->get_result();

            if ($result_bookings && $bookings = $result_bookings->fetch_assoc()) {
                $bookedCount = (int) $bookings['booked_count'];
                $slotsAvailable = $availableSlots - $bookedCount;
                $bookNowButtonDisabled = $slotsAvailable <= 0; // Disable button if no slots available
            } else {
                echo "Error fetching booking details.";
                $bookNowButtonDisabled = true; // Default to disabled if there’s an error
            }
            $stmt_bookings->close();
        } else {
            echo "Error preparing bookings statement: " . $dbconnection->error;
        }
    } else {
        echo "Error fetching rental details.";
    }
    $stmt_rental->close();
} else {
    echo "Error preparing rental statement: " . $dbconnection->error;
}

?>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-3">
                <p class="mb-0"><i class="fa fa-user" aria-hidden="true"></i></p>
            </div>
            <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo htmlspecialchars($name); ?></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-3">
                <p class="mb-0"><i class="fa fa-envelope" aria-hidden="true"></i></p>
            </div>
            <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo htmlspecialchars($email); ?></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-3">
                <p class="mb-0"><i class="fa fa-phone-square" aria-hidden="true"></i></p>
            </div>
            <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo htmlspecialchars($contact_number); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Conditionally Disable the "BOOK NOW" Button -->
<!-- Replace the modal button with a simple link -->
<a href="book.php?bh_id=<?php echo $rental_id; ?>" class="btn btn-primary">
    BOOK NOW
</a>



<button data-toggle="modal" data-target="#feedback" class="btn btn-danger">FEEDBACK</button>

    </div>
</div>

<br>
<hr>
<br>

<div class="reviews">
    <h2 class="text-center">Boarders Review</h2>
    <br>
    <div class="row">
        <?php
        $sqlreview = "SELECT * FROM book WHERE ratings IS NOT NULL AND bhouse_id = '$rental_id'";
        $resultreview = mysqli_query($dbconnection, $sqlreview);
        while ($rowreview = $resultreview->fetch_assoc()) {
            $name = $rowreview['name'];
            $feedback = $rowreview['feedback'];
            $date = $rowreview['date_posted'];
            $ratings = $rowreview['ratings'];
        ?>

        <div class="col-md-6 col-sm-12">
            <div class="card h-100 card-review">
                <div class="card-header p-10 d-flex flex-row justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img class="rounded-circle me-2 p-1" width="60" src="https://www.worldfuturecouncil.org/wp-content/uploadss/2020/06/blank-profile-picture-973460_1280-1-705x705.png">
                        <div class="d-flex flex-column justify-content-center align-items-start fs-5 lh-sm">
                            <b class="text-primary"><?php echo $name; ?></b>
                            <small class="text-muted"><?php echo $date; ?></small>
                        </div>
                    </div>
                    <span class="fs-1 my-0 fw-bolder text-success">
                        <select name="star_rating_option" class="ratings" data-fratings="<?php echo $ratings; ?>">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </span>
                </div>
                <div class="card-body py-2">
                    <p class="card-text"><?php echo $feedback; ?></p>
                </div>
                <a href="#" class="stretched-link"></a>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<?php } ?>

</div>

<!-- The Modal -->
<div class="modal" id="bookNow">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal body -->
            <div class="modal-body">
                <form action="" method="post">
                    <div class="form-row">
                        <div class="form-group col">
                            <label>Name</label>
                            <input name="name" type="text" class="form-control" placeholder="Full Name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label>Address</label>
                            <input name="Address" type="text" class="form-control" placeholder="Purok/Brgy" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label>Contact Number</label>
                            <div class="form-group">
                                <div class="input-group-text">+63
                                    <input onkeypress='phnumber(event)' type="text" maxlength="10" minlength="10" name="contact_number" class="form-control" placeholder="Contact Number" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Age</label>
                            <input name="age" type="number" class="form-control" placeholder="Age" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="booknow" class="btn btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"></i> Request Reservation</button>
                </form>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle" aria-hidden="true"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- The Modal Feedback -->
<div class="modal" id="feedback">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text-center">GIVE US A FEEDBACK</h3>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <span class="text-muted"><i class="fa fa-info-circle" aria-hidden="true"></i> We use your mobile number to validate if you're a boarder</span>
                        <div class="input-group-text">+63 &nbsp
                            <input onkeypress='phnumber(event)' type="text" maxlength="10" minlength="10" name="boardersnumber" class="form-control" placeholder="Your Registered Number" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <center>
                            <select class="torate" name="rate" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </center>
                    </div>
                    <div class="form-group">
                        <label class="text-muted">Feedback:</label>
                        <textarea class="form-control" name="feedbackmsg"></textarea>
                    </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <input type="submit" name="submitfeedback" class="btn btn-success">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times-circle" aria-hidden="true"></i> Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
