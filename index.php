<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumententitel</title>
  </head>
  <style>
    body {font-family: Arial, Calibri, sans-serif; font-size: 21px;}
    .report, .logo {display: flex; justify-content: center; align-items: center;}
    .logo-img {max-width: 250px;}
    .form, .csv-form {max-width: 600px; margin: auto;}
    input, select {float: right; font-size: 75%; padding: 5px;}
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {-webkit-appearance: none; margin: 0;}
    input[type=number] {-moz-appearance:textfield;}
    .csv-button {margin-right: 5px;}
  </style>
  <body>
    <?php
      $servername = "localhost";
      $username = "tbf";
      $password = "R8b%a4q2";
      $dbname = "tbf_";

      $conn = new mysqli($servername, $username, $password, $dbname);

      $selectid = "SELECT id FROM filename ORDER BY id DESC LIMIT 1";
      $fetchedId = str_pad($conn->query($selectid)->fetch_row()[0]+1, 5, '0', STR_PAD_LEFT);

      $message = "";
      if(isset($_POST['submit'])) {

        $selectid = "SELECT id FROM filename ORDER BY id DESC LIMIT 1";
        $newid = str_pad($conn->query($selectid)->fetch_row()[0]+1, 5, '0', STR_PAD_LEFT);

        $project = mysqli_real_escape_string($conn, $_POST['project']);
        $creator = mysqli_real_escape_string($conn, $_POST['creator']);
        $id = $newid;
        $revision = mysqli_real_escape_string($conn, $_POST['revision']);
        $classification = mysqli_real_escape_string($conn, $_POST['classification']);
        $add_text = mysqli_real_escape_string($conn, $_POST['add_text']);

        $insertid = "INSERT INTO filename (
          project,
          creator,
          id,
          revision,
          classification,
          add_text,
          filename
        ) VALUES (
          '$project',
          '$creator',
          '$id',
          '$revision',
          '$classification',
          '$add_text'
          '$project."-".$creator."-".$id."-".$revision."-".$classification."-".$add_text'
        )";

        $conn->query($insertid);

        if ($conn->connect_errno) {
          $errorMessage = "Irgendwas lief schief, mit folgender Fehlernummer: " . $conn->connect_errno;
        } else {
          $filename = $project."-".$creator."-".$id."-".$revision."-".$classification."-".$add_text;
        }
      } elseif (isset($_POST['create-csv'])) {
        $selectall = "SELECT * FROM filename";
        $query = $conn->query($selectall);

        if ($query->num_rows > 0) {
          $delimiter = ",";
          $filename = date("Ymd") . "_" . time() . "_datenexport.csv";

          /*
            https://www.andrerinas.de/tutorials/php-umlautesonderzeichen-in-csv-export-encoding.html
          */
          function convertToWindowsCharset($string) {
            $charset =  mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true);
            $string =  mb_convert_encoding($string, "Windows-1252", $charset);
            return $string;
          }


          $file = fopen('php://memory', 'w');
          $header = array("Timestamp", "Projekt", "Ersteller", "Laufnummer", "Revision", "Detailklassifizierung", "Titel");
          fputcsv($file, $header, $delimiter);

          while ($row = $query->fetch_assoc()) {
            $rowdata = array(
              $row['timestamp'],
              $row['project'],
              $row['creator'],
              $row['id'],
              $row['revision'],
              $row['classification'],
              convertToWindowsCharset($row['add_text'])
            );
            fputcsv($file, $rowdata, $delimiter);
          }

          fseek($file, 0);

          header('Content-Type: text/csv; charset=UTF-8');
          header('Content-Disposition: attachment; filename="' . $filename . '";');

          ob_clean();
          fpassthru($file);

          exit;
        }
      }
    ?>
    <div class="logo">
      <img alt="kva-logo" class="logo-img" src="data:image/jpeg;base64,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Q+0H0g1XEYlZA1FnU0t4pZqTQesxiapCMN55LeeH2JmBazlZxLHehNN42fvH8TDDlufXKBXNaukpyzWh9ri6ZAQXu5flxUWwF/4HeYpZ3GYGGkvC8AdcR+pQAgI9IuK7ABGpS2Rpzv8AuXrci7i11xhkZNZ7feoAPFj5hiu08FvCZZaw7RsBUdxWB9wn4bBn9Si1VAonxOyd3g397wF9A6Hyyuou4zAwlmXgA2cn5wjwQCljiwTRy7KwuGsVtAGbvFxUQW6nZYKKSmVZTy1tNFuj7juVa5s10j0UBhp0Hd+CGUq7m1gfcJYm/Z6fEEBWVRDtlWp5OKiozmvawP2wYGgWy8e0RLDxwpmwwOUBGp4TeeH2JldPvO4/QisiVk4wsFUnhzjYVzBKYfMUv5ReeQTI8KFGT8pwEUF27rxtAAwO5H4lVxNVVzL88I85tOwfsmAKTHKhSdpUrnOgPvM1iOJV3/c8VvPI7Rf4tRu183g3EQ0Ka9YJFBmbBPpEZ3+KdzgV5lBdu68bQFhHcj8QZUlRnU14fiiU9ZUN20Sf8QFcULBywyJ2eHZSqxte2Rw/bKupRGaYNABkVrEgPiF5QtXcYKycYFnbxVRcE3ibJm/faNNGA+58n9xDywBrTA6jktXpcbSVDrtGsrBI+YRBuTiq1+JjwY4cYDMjyyjIk1Dcf0QUAjAwQUxCrTgMfyjjcjQme6SCF0rG62aJNw5GFS9skUtNT2H9BFYv0xltjIPeIBIC2pQGgsH7GFbqIQ7zeWLyly43mIdj+IMtetKrxr2jwDxXTM8zHLv2CCtyGoZ7pIXfTTXbfDFMt03MhigUHDPRNjL3GBRW0C4dQWvdyoKnaFjNyICAKyDHz08HeZWi5AOhDAz+wOkelKeh87cGR6kPaYUjnAXLHkG0YrlDjahXPhlMKG4DTH2ggtC2GvUYmavkZUEpXEcqilgoktmxJvda/QSpwV80Yv8A0VhiMBGYl+zhQoAsw2v/AA4XerAMFuBjV7AJUAAFAafisnuMDWMxLp5SWjW8FLXv/wDHT//aAAwDAQACAAMAAAAQ88888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888+y88888D888888q8y88888880+888ap8888kaB8888Xxaj8888/482z888Uqoaw6WLLIr88CB894znsDPgpTSW8VWXBGdEN9Cw88CB0Xlg8QB+4rXX38VlauQhwsb4DV8UBWWVpDaJx8pAya8VujGj+V7FOZV8BafWVpCoY0opD/r8hib2Np7M6QWU8oDcVvFGHpRMaGT88usO+8sJ0K04wY4OiIC4YAwWw+KOG88888888YMA44U88008EAEEY8UkEU888888QI8wAMY048kccgUEksoMQQU88888888888sM88888888cc8888s888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888//EACgRAQACAgEDBAEEAwAAAAAAAAEAESExECBxoUFRYYHBQHCAsZHR4f/aAAgBAwEBPxD90r/QmY9BkioB3PSFVlMeAX7D89/ECrGTpIo3Cz0nBwR76bDcGYeBQmo99/o0cJLC1AOmfNxUKkW3QPY9Wa8Av/PvUr1VdLPjf5gCxvomfZi56l4WYobm2EXwOMJa3Cu6YQZp7R2Ksz18lcaIPhgu4iW2JY2TsxHHbQfW/wCyXkyvBn+6lHcSzCFRcPRvB4CU5iinvxhzAxFFbn5Y3hFQTT2jBMoumZxvjRFgtekyOjfHwtlvYJ9y9cNW93LDKsHhvzD2PzLW/wAReqGud4PA1TR3m/tBbZo+5+WJuvxxp7RN+8JZ7zyuNEMq0ltNkFSzZsipGGXsZ/0T1z6O7qJT1YgNMTUJHOMNcFUjT3wXHxNU0d5v7cLR9x/iRqasfb6mntNvfi+dw2ijZVw7amXZdjEHcPnuF19ajgezXeeE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" />
    </div>
    <div class="report">
      <p><?php echo $errorMessage ?></p>
    </div>
    <div class="form">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <p>Projektbezeichnung: <input type="text" name="project" value="Linth" placeholder="Linth" readonly></p><br>
        <p>Ersteller: <select name="creator">
          <?php
          $optionen = array(
            '0010' => "0010 - Bauherr (BH)",
            '0100' => "0100 - GP allgemein/interdisziplinär (GPL / TPL)",
            '0200' => "0200 - EMT (RGR, Feuerung, Kessel, WDK, Nebenbetriebe)",
            '0300' => "0300 - Bau (Hoch- und Tiefbau)",
            '0400' => "0400 - EMSRL-T (Elektro EMT und Gebäudesteuerung)",
            '0500' => "0500 - Gebäudetechnik (HLKSE)",
            '0600' => "0600 - Bewilligung / Raumplanung / Umwelt (Sonstige)"
          );
          foreach ( $optionen as $value => $beschreibung ) {
            echo "<option value=" . $value . ">" . $beschreibung . "</option>";
          };
           ?>
          </select></p><br>
        <p>Laufnummer: <input type="text" name="id" value="<?php echo $fetchedId; ?>" readonly> </p><br>
        <p>Revision: <input type="number" name="revision" placeholder="00" maxlength="2"> </p><br>
        <p>Klassifizierung: <select name="classification">
          <?php
            $classOptions = array(
              'BD' => "BD - Bewilligungsrelevante Dokumente",
              'BG' => "BG - Berichte / Gutachten",
              'FV' => "FV - Formatvorlagen",
              'KM' => "KM - Kostenmanagement",
              'KO' => "KO - Korrespondenz",
              'PQ' => "PQ - PQM",
              'PZ' => "PZ - Pläne, Zeichnungen, Modelle",
              'SB' => "SB - Sitzungen / Besprechungen",
              'SL' => "SL - Schemata und Listen",
              'TD' => "TD - Technische Dokumente",
              'VM' => "VM - Vertragsmanagement"
            );
            foreach ( $classOptions as $value => $beschreibung ) {
              echo "<option value=" . $value . ">" . $beschreibung . "</option>";
            };
           ?>
        </select></p><br>
        <p>Titel: <input type="text" name="add_text" pattern="[0-9a-zA-ZäöüÄÖÜ _-]{0,30}" placeholder="max. 30 Zeichen" /></p><br>
        <p>Dateiname: <input type="text" value="<?php echo $filename; ?>" size="45" readonly /></p><br>
        <input type="submit" name="submit" value="Dateiname generieren" />
      </form>
    </div>
    <div class="csv-form">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <input type="submit" name="create-csv" value="Dokumentenverzeichnis anzeigen" class="csv-button" />
      </form>
    </div>
    <?php mysqli_close($conn); ?>
  </body>
</html>
