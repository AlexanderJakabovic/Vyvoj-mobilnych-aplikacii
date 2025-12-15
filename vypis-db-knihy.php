<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <title>Vyhľadaj Knihy</title>
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<p align="left">

<?php
include("config.php");
echo "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#111111' width='700'>";

$var = mysqli_connect("$servername", "$username", "$password", "$dbname") or die("Connect error");
$sql = "SELECT id, isbn, nazov, autor, vydanie, pocet_kusov, cena, vydavatel FROM knihy";
$result = mysqli_query($var, $sql) or exit("Chybný dotaz");

// Načítanie hodnôt do pola
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $isbn = $row['isbn'];
    $nazov = $row['nazov'];
    $autor = $row['autor'];
    $vydanie = $row['vydanie'];
    $pocet_kusov = $row['pocet_kusov'];
    $cena = $row['cena'];
    $vydavatel = $row['vydavatel'];

    // Výpis hodnôt
    echo "<tr>
        <td width='200' bgcolor='#ffffff' height='22'>ID: <b> $id</b></td>
        <td width='200' bgcolor='#ffffff' height='22'>ISBN: <b> $isbn</b></td>
        <td width='300' bgcolor='#ffffff' height='22'>Názov: <b> $nazov</b></td>
        <td width='100' bgcolor='#ffffff' height='22'>Cena: <b> $cena</b></td>
        <td width='100' bgcolor='#ffffff' height='22'><b><a href='edit.php?menu=12&e=$id'>edituj</b></a></td>
    </tr>
    <tr>
        <td width='200' bgcolor='#FFFFee' height='32'>Vydavateľ: <b> $vydavatel</b></td>
        <td width='300' bgcolor='#FFFFee' height='32'>Autor: <b> $autor</b></td>
        <td width='100' bgcolor='#FFFFee' height='32'>Počet kusov: <b> $pocet_kusov</b></td>
        <td width='100' bgcolor='#FFFFee' height='32'><b><a href='zmazanietov.php?k=$id'>X</b></a></td>
    </tr>
    <tr>
        <td width='200' bgcolor='#000000' height='1'></td>
        <td width='300' bgcolor='#000000' height='1'></td>
        <td width='100' bgcolor='#000000' height='1'></td>
        <td width='100' bgcolor='#000000' height='1'></td>
    </tr>";
}
echo "</table>";
?>

</body>
</html>