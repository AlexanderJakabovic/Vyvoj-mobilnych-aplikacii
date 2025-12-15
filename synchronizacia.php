<?php
// synchronizacia.php
include("config.php");    // Local DB configuration
include("config2.php");   // Remote DB 2 configuration
include("config3.php");   // Remote DB 3 configuration

echo "<h1>Synchronizácia údajov</h1>";

try {
    $local_conn = new mysqli($servername, $username, $password, $dbname);
    if ($local_conn->connect_error) {
        throw new Exception("Local DB connection failed: " . $local_conn->connect_error);
    }

    $remote_conn2 = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);
    if ($remote_conn2->connect_error) {
        echo "Remote DB 2 connection failed: " . $remote_conn2->connect_error . "<br />";
        $remote_conn2 = null;
    }

    $remote_conn3 = new mysqli($remote_servername2, $remote_username2, $remote_password2, $remote_dbname2);
    if ($remote_conn3->connect_error) {
        echo "Remote DB 3 connection failed: " . $remote_conn3->connect_error . "<br />";
        $remote_conn3 = null;
    }

    // Fetch failed transactions from the log file
    $failed_transactions = file_get_contents('failed_transactions.txt');
    if ($failed_transactions === false || empty($failed_transactions)) {
        echo "Žiadne údaje na synchronizáciu.<br />";
        exit;
    }

    // Split the log file into individual queries
    $queries = explode(";\n", $failed_transactions);
    $queries = array_filter($queries); // Remove empty lines

    foreach ($queries as $query) {
        if (trim($query) === "") {
            continue;
        }

        // Attempt to execute the query on the local database
        if ($local_conn->query($query) === true) {
            echo "Synchronizované lokálne: " . htmlspecialchars($query) . "<br />";
        } else {
            echo "Nepodarilo sa synchronizovať lokálne: " . htmlspecialchars($query) . "<br />";
        }

        // Attempt to execute the query on remote database 2
        if ($remote_conn2 && $remote_conn2->query($query) === true) {
            echo "Synchronizované na remote DB 2: " . htmlspecialchars($query) . "<br />";
        } else if ($remote_conn2) {
            echo "Nepodarilo sa synchronizovať na remote DB 2: " . htmlspecialchars($query) . "<br />";
        }

        // Attempt to execute the query on remote database 3
        if ($remote_conn3 && $remote_conn3->query($query) === true) {
            echo "Synchronizované na remote DB 3: " . htmlspecialchars($query) . "<br />";
        } else if ($remote_conn3) {
            echo "Nepodarilo sa synchronizovať na remote DB 3: " . htmlspecialchars($query) . "<br />";
        }
    }

    // Clear the log file after synchronization
    file_put_contents('failed_transactions.txt', '');

    echo "Synchronizácia dokončená.<br />";

    // Close all connections
    $local_conn->close();
    if ($remote_conn2) {
        $remote_conn2->close();
    }
    if ($remote_conn3) {
        $remote_conn3->close();
    }
} catch (Exception $e) {
    echo "Chyba pri synchronizácii: " . $e->getMessage();
    exit;
}
?>
