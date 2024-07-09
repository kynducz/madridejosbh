 <?php
$query = "SELECT profile_photo FROM landlords WHERE id = ?";
$stmt = $dbconnection->prepare($query);
$stmt->bind_param("i", $login_session);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profile_photo = $row['profile_photo'];
} else {
    // Default profile photo path or handle error as needed
    $profile_photo = 'default_profile_photo.jpg';
}
?>
<style>
    .sidebar {
      width: 230px;
      background-color: #333; /* Adjust background color as needed */
    }

    .sidebar a {
      font-family: 'Roboto', serif;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 13px;
      text-decoration: none;
      font-size: 18px;
      color: white; /* Adjust text color as needed */
    }

    .sidebar a i {
      margin-left: 10px; /* Adjust the margin as needed */
    }

    .sidebar a:hover {
      background-color: red; /* Add hover effect if needed */
      color: #000; /* Adjust hover text color if needed */
    }

    .sidebar a:active, .sidebar a:focus {
      background-color: red; /* Change background color when clicked or focused */
      color: #fff; /* Change text color when clicked or focused */
      outline: none; /* Remove default outline for better appearance */
    }

    .sidebar a.active {
      background-color: red; /* Set different background color for the active link */
      color: #fff; /* Set different text color for the active link */
    }
     .profile-photo {
      width: 120px; /* Adjust width as needed */
      height: 120px; /* Adjust height as needed */
      border-radius: 50%; /* Rounded shape for the photo */
      position: absolute; /* Position absolute for precise placement */
      top: 10px; /* Adjust top position */
      left:   100px; /* Adjust left position */
      transform: translateX(-50%);
    }
  </style>
 
  <div class="sidebar">
    <img src="../uploads/<?php echo $profile_photo; ?>" alt="Profile Photo" class="profile-photo">

    <a style="margin-top: 100px;" href="dashboard.php" onclick="setActive(event)">Dashboard <i class="fa fa-tachometer" aria-hidden="true"></i></a>
    <a href="create.php" onclick="setActive(event)">Create New <i class="fa fa-plus-circle" aria-hidden="true"></i></a>
    <a href="bhouse.php" onclick="setActive(event)">BHouse List <i class="fa fa-home" aria-hidden="true"></i></a>
    <a href="booker.php" onclick="setActive(event)">Booker List <i class="fa fa-list-ul" aria-hidden="true"></i></a>
    <a href="report.php" onclick="setActive(event)">Reports <i class="fa fa-file-text" aria-hidden="true"></i></a>
    <a href="logout.php" onclick="setActive(event)">Logout <i class="fa fa-power-off" aria-hidden="true"></i></a>
  </div>

  <script>
    // Set active class based on the current page
    document.addEventListener("DOMContentLoaded", function() {
      var activeLink = localStorage.getItem("activeLink");
      if (activeLink) {
        var link = document.querySelector('.sidebar a[href="' + activeLink + '"]');
        if (link) {
          link.classList.add("active");
        }
      }
    });

    function setActive(event) {
      // Prevent default link behavior
      event.preventDefault();

      // Get the clicked link element
      var element = event.currentTarget;

      // Remove 'active' class from all sidebar links
      var links = document.querySelectorAll('.sidebar a');
      links.forEach(function(link) {
        link.classList.remove('active');
      });

      // Add 'active' class to the clicked link
      element.classList.add('active');

      // Store the active link in localStorage
      localStorage.setItem("activeLink", element.getAttribute("href"));

      // Navigate to the clicked link
      window.location.href = element.getAttribute("href");
    }
  </script>
 
