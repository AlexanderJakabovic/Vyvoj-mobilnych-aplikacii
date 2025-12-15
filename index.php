<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title></title>
 <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  html, body {
    height: 100%;
    width: 100%;
    margin: 0;
    overflow: hidden;
  }
  body {
    display: flex;
    flex-direction: column;
    background-color: #f0f0f0;
    font-family: Arial, sans-serif;
  }
  table {
    width: 100%;
    height: 100%;
    border-collapse: collapse;
  }
  td {
    padding: 10px;
  }
  .header {
    background-color: #1E90FF;
    color: #FFD700;
    text-align: center;
    padding: 20px 0;
border: 3px solid #000080;
text-shadow: 4px 4px 5px rgba(0, 0, 0, 1); /* Black shadow */  }
  .menu {
    background-color: #4682B4;
    color: #FFFF00;
    vertical-align: top;
    width: 20%;
border: 3px solid #000080;

  }
  .content {
    background-color: #FFFFCC;
    width: 80%;
  }
  .menu, .content {
    height: calc(100vh - 80px); /* Adjust height minus header */
  }
</style>
</head>
<body>
  <table border="0" cellspacing="0">
    <tr>
      <td colspan="2" class="header">
        <h1>Distribuovaná databáza - Knihy</h1>
      </td>
    </tr>
    <tr>
      <td width="200" class="menu">
        <?php 
        include ("menu.php"); // Menu file, should list options for the knihy database
        ?>
      </td>
      <td class="content">
        <div class="dolezite">
        <?php
          $m = $_GET["menu"] ?? 3; // Default menu value is 3 if not set
          if (!in_array($m, range(1, 12))) $m = 3;

          // Switch case to include the appropriate file based on the menu parameter
          switch ($m) {    
            case 2:
              include ("pridaj-knihu.php"); // Add a new book
              break;
            case 3:
              include ("vypis-db-knihy.php"); // Display all books in the knihy table
              break;
            case 4:
              include ("synchronizacia.php"); // Synchronization logic for distributed database
              break;
            case 5:
              include ("form-hladaj-knihu.php"); // Search form for books
              break;    
            case 6:
              include ("hladaj-knihu.php"); // Handle search logic
              break;        
            case 7:
              include ("pridaj-knihu-ok.php"); // Confirmation after adding a book
              break;
            case 8:
              include ("vypis-knihy.php"); // Display specific book data
              break; 
            case 9:
              include ("hladaj-knihu-cena.php"); // Search books by price
              break;  
            case 10:
              include ("vypis-knihu-cena.php"); // Display books by price
              break; 
            case 11:
              include ("edit-knihu-ok.php"); // Confirmation after editing a book
              break;     
            case 12:
              include ("edit.php"); // Edit book details
              break;     
            default:
              include ("vypis-knihy.php"); // Default: display books
              break;
          }
        ?>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>