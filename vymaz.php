<?php
// Zabezpečte, že máte správne pripojenie k databázam
include("config.php"); // Local DB configuration
include("config2.php"); // Remote DB configuration
include("config3.php"); // Third DB configuration

// Skontrolujte, či je ID v URL a či je platné
if (isset($_GET['k'])) {
    $id = $_GET['k'];

    // Skontrolujte pripojenie ku všetkým uzlom skôr, než začnete operácie
    $connections = [];

    try {
        $local_conn = new mysqli($servername, $username, $password, $dbname);
        if ($local_conn->connect_error) {
            throw new Exception("Local DB connection failed: " . $local_conn->connect_error);
        }
        $connections['local'] = $local_conn;

        $remote_conn = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);
        if ($remote_conn->connect_error) {
            throw new Exception("Remote DB connection failed: " . $remote_conn->connect_error);
        }
        $connections['remote'] = $remote_conn;

        $third_conn = new mysqli($remote_servername2, $remote_username2, $remote_password2, $remote_dbname2);
        if ($third_conn->connect_error) {
            throw new Exception("Third DB connection failed: " . $third_conn->connect_error);
        }
        $connections['third'] = $third_conn;
    } catch (Exception $e) {
        // Zavrite existujúce pripojenia pred ukončením
        foreach ($connections as $conn) {
            $conn->close();
        }
        die("Error: " . $e->getMessage());
    }

    // Vykonajte operáciu iba ak sú všetky pripojenia aktívne
    try {
        // Lokálny uzol
        $local_sql = "DELETE FROM knihy WHERE id = ?";
        $local_stmt = $connections['local']->prepare($local_sql);
        $local_stmt->bind_param("s", $id);
        if (!$local_stmt->execute()) {
            throw new Exception("Error executing local delete: " . $local_stmt->error);
        }
        $local_stmt->close();

        // Vzdialený uzol
        $remote_sql = "DELETE FROM knihy WHERE id = ?";
        $remote_stmt = $connections['remote']->prepare($remote_sql);
        $remote_stmt->bind_param("s", $id);
        if (!$remote_stmt->execute()) {
            throw new Exception("Error executing remote delete: " . $remote_stmt->error);
        }
        $remote_stmt->close();

        // Tretí uzol
        $third_sql = "DELETE FROM knihy WHERE id = ?";
        $third_stmt = $connections['third']->prepare($third_sql);
        $third_stmt->bind_param("s", $id);
        if (!$third_stmt->execute()) {
            throw new Exception("Error executing third delete: " . $third_stmt->error);
        }
        $third_stmt->close();

        echo "Kniha bola úspešne vymazaná zo všetkých databáz.<br />";

    } catch (Exception $e) {
        die("Error during delete operation: " . $e->getMessage());
    } finally {
        // Zavrite všetky pripojenia
        foreach ($connections as $conn) {
            $conn->close();
        }
    }

    // Presmerovanie na stránku s tabuľkou po vymazaní
    header("Location: index.php?menu=8");
    exit;
} else {
    echo "ID knihy nebolo poskytnuté.";
}
?>
