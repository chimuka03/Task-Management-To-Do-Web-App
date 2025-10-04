   <?php
   require_once 'db.php';  // Starts session if needed

   if (isset($_SESSION['user_id'])) {
       header("Location: dashboard.php");
   } else {
       header("Location: login.php");
   }
   exit();
   ?>
   