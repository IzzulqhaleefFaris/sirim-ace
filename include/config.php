
<?php
    // $dbhost = '165.99.199.24';
    // $dbuser = 'izzul';
    // $dbpass = 'Sirimace@2026';
    // $dbname = 'sirimace';

    // $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    // if (!$conn) {
    //     die("Database connection failed: " . mysqli_connect_error());
    // }

    // mysqli_set_charset($conn, "utf8mb4");

    // date_default_timezone_set('Asia/Kuala_Lumpur');
?> 

<?php
    // $dbhost = '103.117.20.189'; 
    // $dbuser = 'sirimsen_soljar_u';
    // $dbpass = 'pbBEgKE#lL1^kJ^AnhNT{MD%NRrV3aiV';
    // $dbname = 'sirimsen_soljar';

    $dbhost = 'localhost'; 
    $dbuser = 'root';
    $dbpass = '';
    $dbname = 'sirimsen_soljar';

    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8mb4");

    date_default_timezone_set('Asia/Kuala_Lumpur');
?>