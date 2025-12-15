<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <title>vyhladaj</title>
    <link href="style.css" rel=stylesheet type=text/css>
    <style>
        .record-frame {
            border: 2px solid #000000;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #FFFFEE;
        }
        .record-frame td {
            padding: 5px;
        }
        .record-frame td a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>
<p align="left">

<?php
include("config.php");   

// Connect to the local database
$var = mysqli_connect($servername, $username, $password, $dbname) or die("connect error");

// Prepare and execute the SQL query to fetch records
$sql = "SELECT id, isbn, nazov, autor, vydanie, pocet_kusov, cena, vydavatel FROM knihy";
$result = mysqli_query($var, $sql) or exit("chybny dotaz");

// Display the records in table format
echo "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#111111' width='700'>";
while ($row = mysqli_fetch_assoc($result)) { 
    $id = $row['id'];
    $isbn = $row['isbn'];
    $nazov = $row['nazov'];
    $autor = $row['autor'];
    $vydanie = $row['vydanie'];
    $pocet_kusov = $row['pocet_kusov'];
    $cena = $row['cena'];
    $vydavatel = $row['vydavatel'];

    // Create a frame for each record
    echo "<tr><td colspan='5' class='record-frame'>
        <table width='100%'>
            <tr>
                <td width='200'>ID: <b>".$id."</b></td>
                <td width='200'>ISBN: <b>".$isbn."</b></td>
                <td width='300'>Názov: <b>".$nazov."</b></td>
                <td width='100'>Cena: <b>".$cena."</b></td>
                <td width='100'><b><a href='index.php?menu=12&e=".$id."'>Editovať</a></b></td>
            </tr>
            <tr>
                <td width='200'>Autor: <b>".$autor."</b></td>
                <td width='300'>Vydanie: <b>".$vydanie."</b></td>
                <td width='100'>Počet kusov: <b>".$pocet_kusov."</b></td>
                <td width='300'>Vydavateľ: <b>".$vydavatel."</b></td>
                <td width='100'><b><a href='vymaz.php?k=".$id."'>Zmazať</a></b></td>
            </tr>
        </table>
    </td></tr>";
}
echo "</table>";

// Close the connection
mysqli_close($var);
?>

</body>
</html>