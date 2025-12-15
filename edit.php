<?php
include("config.php");  // Database connection details for local DB
include("config2.php"); // Database connection details for Node2
include("config3.php"); // Database connection details for Node3

// Initialize variables
$id = $isbn = $nazov = $autor = $vydanie = $pocet_kusov = $cena = $vydavatel = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the submitted form data
    $id = $_POST['id'];
    $isbn = $_POST['isbn'];
    $nazov = $_POST['nazov'];
    $autor = $_POST['autor'];
    $vydanie = $_POST['vydanie'];
    $pocet_kusov = $_POST['pocet_kusov'];
    $cena = $_POST['cena'];
    $vydavatel = $_POST['vydavatel'];

    // Connect to the local database
    $conn_local = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn_local) {
        die("Connection failed to local database: " . mysqli_connect_error());
    }

    // Begin a transaction on the local DB
    mysqli_begin_transaction($conn_local);

    // Prepare the SQL query for the local DB
    $sql_local = "UPDATE knihy SET isbn = ?, nazov = ?, autor = ?, vydanie = ?, pocet_kusov = ?, cena = ?, vydavatel = ? WHERE id = ?";
    $stmt_local = mysqli_prepare($conn_local, $sql_local);
    if (!$stmt_local) {
        die("Error preparing query for local database: " . mysqli_error($conn_local));
    }

    mysqli_stmt_bind_param($stmt_local, "ssssdsis", $isbn, $nazov, $autor, $vydanie, $pocet_kusov, $cena, $vydavatel, $id);

    if (!mysqli_stmt_execute($stmt_local)) {
        mysqli_rollback($conn_local);
        die("Error executing query for local database: " . mysqli_error($conn_local));
    }

    mysqli_stmt_close($stmt_local);

    // Connect to the remote databases (Node2 and Node3)
    $conn_remote2 = mysqli_connect($remote_servername, $remote_username, $remote_password, $remote_dbname);
    $conn_remote3 = mysqli_connect($remote_servername2, $remote_username2, $remote_password2, $remote_dbname2);

    if (!$conn_remote2 || !$conn_remote3) {
        mysqli_rollback($conn_local);
        if ($conn_remote2) mysqli_close($conn_remote2);
        if ($conn_remote3) mysqli_close($conn_remote3);
        mysqli_close($conn_local);
        die("ERROR: Nemôžete upraviť záznam bez pripojenia ku všetkým uzlom.");
    }

    // Begin a transaction on the remote databases
    mysqli_begin_transaction($conn_remote2);
    mysqli_begin_transaction($conn_remote3);

    // Prepare the SQL queries for the remote DBs
    $sql_remote = "UPDATE knihy SET isbn = ?, nazov = ?, autor = ?, vydanie = ?, pocet_kusov = ?, cena = ?, vydavatel = ? WHERE id = ?";

    // Execute for Node2
    $stmt_remote2 = mysqli_prepare($conn_remote2, $sql_remote);
    if (!$stmt_remote2) {
        mysqli_rollback($conn_local);
        mysqli_rollback($conn_remote2);
        mysqli_rollback($conn_remote3);
        mysqli_close($conn_remote2);
        mysqli_close($conn_remote3);
        mysqli_close($conn_local);
        die("Error preparing query for Node2: " . mysqli_error($conn_remote2));
    }
    mysqli_stmt_bind_param($stmt_remote2, "ssssdsis", $isbn, $nazov, $autor, $vydanie, $pocet_kusov, $cena, $vydavatel, $id);
    if (!mysqli_stmt_execute($stmt_remote2)) {
        mysqli_rollback($conn_local);
        mysqli_rollback($conn_remote2);
        mysqli_rollback($conn_remote3);
        mysqli_close($conn_remote2);
        mysqli_close($conn_remote3);
        mysqli_close($conn_local);
        die("Error executing query for Node2: " . mysqli_error($conn_remote2));
    }
    mysqli_stmt_close($stmt_remote2);

    // Execute for Node3
    $stmt_remote3 = mysqli_prepare($conn_remote3, $sql_remote);
    if (!$stmt_remote3) {
        mysqli_rollback($conn_local);
        mysqli_rollback($conn_remote2);
        mysqli_rollback($conn_remote3);
        mysqli_close($conn_remote2);
        mysqli_close($conn_remote3);
        mysqli_close($conn_local);
        die("Error preparing query for Node3: " . mysqli_error($conn_remote3));
    }
    mysqli_stmt_bind_param($stmt_remote3, "ssssdsis", $isbn, $nazov, $autor, $vydanie, $pocet_kusov, $cena, $vydavatel, $id);
    if (!mysqli_stmt_execute($stmt_remote3)) {
        mysqli_rollback($conn_local);
        mysqli_rollback($conn_remote2);
        mysqli_rollback($conn_remote3);
        mysqli_close($conn_remote2);
        mysqli_close($conn_remote3);
        mysqli_close($conn_local);
        die("Error executing query for Node3: " . mysqli_error($conn_remote3));
    }
    mysqli_stmt_close($stmt_remote3);

    // Commit all transactions
    mysqli_commit($conn_local);
    mysqli_commit($conn_remote2);
    mysqli_commit($conn_remote3);

    // Close all database connections
    mysqli_close($conn_local);
    mysqli_close($conn_remote2);
    mysqli_close($conn_remote3);

    // Redirect to the book list page
    header("Location: index.php?menu=8");
    exit;
} else if (isset($_GET['e'])) {
    $id = $_GET['e'];

    // Connect to the local database to fetch original values
    $conn_local = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn_local) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql_fetch = "SELECT * FROM knihy WHERE id = ?";
    $stmt_fetch = mysqli_prepare($conn_local, $sql_fetch);
    if (!$stmt_fetch) {
        die("Error preparing query to fetch record: " . mysqli_error($conn_local));
    }

    mysqli_stmt_bind_param($stmt_fetch, "s", $id);
    mysqli_stmt_execute($stmt_fetch);
    $result = mysqli_stmt_get_result($stmt_fetch);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $isbn = $row['isbn'];
        $nazov = $row['nazov'];
        $autor = $row['autor'];
        $vydanie = $row['vydanie'];
        $pocet_kusov = $row['pocet_kusov'];
        $cena = $row['cena'];
        $vydavatel = $row['vydavatel'];
    } else {
        die("Record not found in the local database.");
    }

    mysqli_stmt_close($stmt_fetch);
    mysqli_close($conn_local);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Úprava knihy</title>
    <style>
        /* Form styling */
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
        <b style="font-size: 1.5rem; font-weight: bold;">Úprava knihy</b>
        <form action="edit.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>"> <!-- Hidden ID field -->
            
            <div class="form-row">
                <label for="isbn">ISBN:</label>
                <input name="isbn" type="text" value="<?php echo htmlspecialchars($isbn); ?>" required />
            </div>
            <div class="form-row">
                <label for="nazov">Názov:</label>
                <input name="nazov" type="text" value="<?php echo htmlspecialchars($nazov); ?>" required />
            </div>
            <div class="form-row">
                <label for="autor">Autor:</label>
                <input name="autor" type="text" value="<?php echo htmlspecialchars($autor); ?>" required />
            </div>
            <div class="form-row">
                <label for="vydanie">Vydanie:</label>
                <input name="vydanie" type="number" value="<?php echo htmlspecialchars($vydanie); ?>" required />
            </div>
            <div class="form-row">
                <label for="pocet_kusov">Počet kusov:</label>
                <input name="pocet_kusov" type="number" value="<?php echo htmlspecialchars($pocet_kusov); ?>" required />
            </div>
            <div class="form-row">
                <label for="cena">Cena:</label>
                <input name="cena" type="number" step="0.01" value="<?php
echo htmlspecialchars($cena); ?>" required />
            </div>
            <div class="form-row">
                <label for="vydavatel">Vydavateľ:</label>
                <input name="vydavatel" type="text" value="<?php echo htmlspecialchars($vydavatel); ?>" required />
            </div>
            <div class="form-actions">
                <input type="submit" value="Uložiť">
                <input type="reset" value="Resetovať">
            </div>
        </form>
    </center>
</body>
</html>