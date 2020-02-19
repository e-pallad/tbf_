<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Dokumentennummer</title>
  </head>
  <style>
    .report, .overview {display: flex; justify-content: center; align-items: center;}
    .form {max-width: 450px; margin: auto; padding: 5% 0;}
    input, select {float: right;}
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {-webkit-appearance: none; margin: 0;}
    input[type=number] {-moz-appearance:textfield;}
    .overview {overflow-y: auto; height: 200px;}
    .sticky-head thead th {position: sticky; top: 0;}
    table, th, td {border: 1px solid black;}
    th, td {padding: 3px;}
  </style>
  <body>
    <?php
      $servername = "localhost";
      $username = "tbf";
      $password = "R8b%a4q2";
      $dbname = "tbf_";

      $conn = new mysqli($servername, $username, $password, $dbname);

      $message = "";
      if(isset($_POST['submit'])) {
        $mysqlifilter = $_POST['creator'];

        $selectid = "SELECT id FROM dokumentennummer WHERE creator = " . $mysqlifilter . " ORDER BY id DESC LIMIT 1";
        $newid = str_pad($conn->query($selectid)->fetch_row()[0]+1, 6, '0', STR_PAD_LEFT);

        $project = mysqli_real_escape_string($conn, $_POST['project']);
        $creator = mysqli_real_escape_string($conn, $_POST['creator']);
        $id = $newid;
        $revision = mysqli_real_escape_string($conn, $_POST['revision']);
        $doctype = mysqli_real_escape_string($conn, $_POST['doctype']);

        $insertid = "INSERT INTO dokumentennummer (project, creator, id, revision, doctype) VALUES ('$project', '$creator', '$id', '$revision', '$doctype')";
        $conn->query($insertid);

        if ($conn->connect_errno) {
          $message = "Irgendwas lief schief, mit folgender Fehlernummer: " . $conn->connect_errno;
        } else {
          $message = "Erfolgreich! Das Dokument hat diesen Namen: <br>".$project."-".$creator."-".$id."-".$revision."-".$doctype."-";
        }
      }
    ?>
    <div class="report">
      <p><?php echo $message ?></p>
    </div>
    <div class="form">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <p>Projektbezeichnung: <input type="text" name="project" value="Linth" readonly="readonly" /></p><br>
        <p>Ersteller: <select name="creator">
          <?php
          $optionen = array(
            '0100' => "TBF allgemein/interdisziplinär (GPL / TPL)",
            '0200' => "TBF EMT (RGR, Feuerung, Kessel, WDK, Nebenbetriebe)",
            '0300' => "TBF Bau (Hoch- und Tiefbau)",
            '0400' => "TBF EMSRL-T",
            '0500' => "TBF Gebäudetechnik",
            '0600' => "TBF Reserve",
            '0700' => "TBF Bewilligung / Raumplanung / Umwelt"
          );
          foreach ( $optionen as $value => $beschreibung ) {
            echo "<option value=" . $value . ">" . $beschreibung . "</option>";
          };
          ?>
          </select></p><br>
        <p>Revision: <input type="number" name="revision" /></p><br>
        <p>Detailklassifizierung: <input type="text" name="doctype" /></p><br>
        <input type="submit" name="submit" />
      </form>
    </div>
    <div class="overview">
      <table class="sticky-head">
        <thead>
          <tr>
            <th>Projektbezeichnung</th>
            <th>Ersteller</th>
            <th>Laufnummer</th>
            <th>Revision</th>
            <th>Detailklassifizierung</th>
            <th>Dateiname</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $allids = "SELECT project, creator, id, revision, doctype FROM dokumentennummer ORDER BY timestamp DESC";
            $row = $conn->query($allids)->fetch_all(MYSQLI_ASSOC);

            foreach ($row as $rows) {
              echo "<tr>";
              echo "<td>" . $rows['project'] . "</td>";
              echo "<td>" . $rows['creator'] . "</td>";
              echo "<td>" . $rows['id'] . "</td>";
              echo "<td>" . $rows['revision'] . "</td>";
              echo "<td>" . $rows['doctype'] . "</td>";
              echo "<td>" . $rows['project'] . "-" . $rows['creator'] . "-" . $rows['id'] . "-" . $rows['revision'] . "-"  . $rows['doctype'] .  "</td>";
              echo "</tr>";
            }
           ?>
        </tbody>
      </table>
    </div>
    <?php mysqli_close($conn); ?>
  </body>
</html>
