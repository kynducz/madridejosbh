<?php
   session_start();
   
   $user_check = $_SESSION['login_user'];
   
   $ses_sql = mysqli_query($dbconnection,"select * from register1 where email = '$user_check' ");
   
   $row = mysqli_fetch_array($ses_sql,MYSQLI_ASSOC);
   
   $login_session = $row['id'];
   
   // if(!isset($_SESSION['login_user'])){
   //    header("location:index.php");
   //    die();
   // }
?>
