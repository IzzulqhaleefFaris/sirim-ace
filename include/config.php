
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
    if (isset($conn)) return; // guard: only run once

    require_once __DIR__ . '/../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();

    $dbhost = $_ENV['DB_HOST'];
    $dbuser = $_ENV['DB_USER'];
    $dbpass = $_ENV['DB_PASS'];
    $dbname = $_ENV['DB_NAME'];

    /** @var mysqli $conn */
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // make available in $GLOBALS for any callers expecting it
    $GLOBALS['conn'] = $conn;

    mysqli_set_charset($conn, "utf8mb4");

    date_default_timezone_set('Asia/Kuala_Lumpur');
?>