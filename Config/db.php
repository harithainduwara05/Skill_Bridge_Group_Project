 <?php 

// require_once __DIR__ . '/env_loader.php';

// $host     = $_ENV['DB_HOST'] ?? 'localhost';
// $user     = $_ENV['DB_USERNAME'] ?? 'root';
// $password = $_ENV['DB_PASSWORD'] ?? '';
// $dbname   = $_ENV['DB_DATABASE'] ?? 'skillbridge_db';
// $port     = intval($_ENV['DB_PORT']);

// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// try {
//     $conn = mysqli_init();
//     $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

//     $conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

// } catch (Exception $e) {
//     die("Database Connection failed: " . $e->getMessage());
// }

    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'skillbridge_db';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try{
         $conn = new mysqli($host, $user, $password, $dbname);
         if($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
         }
    }
    catch(Exception $e){
        echo "Connection failed: " . $e->getMessage();
    }
?> 