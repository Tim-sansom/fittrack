   <?php
   // Force errors to show
   ini_set('display_errors', 1);
   error_reporting(E_ALL);

   $host = 'localhost';
   $dbname = 'k66x4ro_fittrack';
   $user = 'k66x4ro_uni_tim';
   $pass = 'T3chn0-2026!'; // <-- Ensure this is your exact password

   try {
       $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
       $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
       echo "SUCCESS: Database connected perfectly!";
   } catch (PDOException $e) {
       echo "FAILED: " . $e->getMessage();
   }
   ?>