<?php 
// Display the form for adding books
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pridanie knihy</title>
    <style>
        form {
            display: flex;
            flex-direction: column;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 2px solid #220075;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .form-row label {
            width: 120px;
            font-weight: bold;
        }
        .form-row input {
            flex: 1;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #aaa;
            border-radius: 5px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .form-actions input {
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #220075;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .form-actions input:hover {
            background-color: #aa0055;
        }
    </style>
</head>
<body>
    <br><br><br>
    <center>
        <b style="font-size: 1.5rem; font-weight: bold;">Pridanie knihy</b>
        <form action="" method="POST">
            <div class="form-row">
                <label for="isbn">ISBN:</label>
                <input name="isbn" type="text" required />
            </div>
            <div class="form-row">
                <label for="nazov">Názov:</label>
                <input name="nazov" type="text" required />
            </div>
            <div class="form-row">
                <label for="autor">Autor:</label>
                <input name="autor" type="text" required />
            </div>
            <div class="form-row">
                <label for="vydanie">Vydanie:</label>
                <input name="vydanie" type="number" required />
            </div>
            <div class="form-row">
                <label for="pocet_kusov">Počet kusov:</label>
                <input name="pocet_kusov" type="number" required />
            </div>
            <div class="form-row">
                <label for="cena">Cena:</label>
                <input name="cena" type="number" step="0.01" required />
            </div>
            <div class="form-row">
                <label for="vydavatel">Vydavateľ:</label>
                <input name="vydavatel" type="text" required />
            </div>
            <div class="form-actions">
                <input type="submit" name="submit" value="Odoslať" />
                <input type="reset" value="Vymazať" />
            </div>
        </form>
    </center>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("config.php"); // Local DB configuration
    include("config2.php"); // Remote DB configuration
    include("config3.php"); // Third node DB configuration

    // Retrieve and sanitize form data
    $isbn = htmlspecialchars($_POST["isbn"]);
    $nazov = htmlspecialchars($_POST["nazov"]);
    $autor = htmlspecialchars($_POST["autor"]);
    $vydanie = intval($_POST["vydanie"]);
    $pocet_kusov = intval($_POST["pocet_kusov"]);
    $cena = floatval($_POST["cena"]);
    $vydavatel = htmlspecialchars($_POST["vydavatel"]);

    // Generate unique ID
    $id = date('YmdHis');

    // Prepare the SQL query
    $sql = "INSERT INTO knihy (id, isbn, nazov, autor, vydanie, pocet_kusov, cena, vydavatel) 
            VALUES ('$id', '$isbn', '$nazov', '$autor', $vydanie, $pocet_kusov, $cena, '$vydavatel')";

    $logFile = 'failed_transactions.txt';
    $queryLogged = false; // Ensure the query is logged only once

    // Function to log failed queries
    function log_failed_query($logFile, $sql, &$queryLogged) {
        if (!$queryLogged) {
            file_put_contents($logFile, $sql . "\n", FILE_APPEND);
            $queryLogged = true;
        }
    }

    // Function to execute SQL query
    function insert_into_db($conn, $sql) {
        if (!$conn->query($sql)) {
            throw new Exception("Query execution failed");
        }
    }

    // Local DB insertion
    try {
        $local_conn = new mysqli($servername, $username, $password, $dbname);
        if ($local_conn->connect_error) {
            throw new Exception("Local DB connection failed: " . $local_conn->connect_error);
        }
        insert_into_db($local_conn, $sql);
        echo "Record successfully written to local DB.<br />";
        $local_conn->close();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }

    // Remote DB insertion (config2)
    try {
        $remote_conn = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);
        if ($remote_conn->connect_error) {
            throw new Exception("Remote DB (config2) connection failed: " . $remote_conn->connect_error);
        }
        insert_into_db($remote_conn, $sql);
        echo "Record successfully written to remote DB (config2).<br />";
        $remote_conn->close();
    } catch (Exception $e) {
        log_failed_query($logFile, $sql, $queryLogged);
        echo "Error with remote DB (config2): " . $e->getMessage() . "<br />";
    }

    // Third node DB insertion (config3)
    try {
        $third_conn = new mysqli($remote_servername2, $remote_username2, $remote_password2, $remote_dbname2);
        if ($third_conn->connect_error) {
            throw new Exception("Third DB (config3) connection failed: " . $third_conn->connect_error);
        }
        insert_into_db($third_conn, $sql);
        echo "Record successfully written to third DB (config3).<br />";
        $third_conn->close();
    } catch (Exception $e) {
        log_failed_query($logFile, $sql, $queryLogged);
        echo "Error with third DB (config3): " . $e->getMessage() . "<br />";
    }

    // Redirect to a confirmation or summary page
    header('Location: index.php?menu=8');
    exit;
}
?>
