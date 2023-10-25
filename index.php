<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Leitura de Arquivo TXT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container" style="max-width: 90vw !important;">
    <h1 class="mt-4">Leitura de Arquivo TXT</h1>

    <form method="post" enctype="multipart/form-data" class="mt-4">
      <div class="mb-3">
        <input type="file" name="file" accept=".txt" class="form-control-file">
        <button type="submit" class="btn btn-primary">Enviar Arquivo</button>
      </div>
    </form>

    <?php
    function parseDate($string, $export = 'mysql')
    {
      $parts = explode(" ", $string);
      $partdate = $parts[0];
      $parthour = $parts[1];
      $date = (explode("\t", $partdate)[4]);

      $hourexplode = explode(':', $parthour);
      $hour_h = str_pad(trim($hourexplode[0]), 2, 0, STR_PAD_RIGHT);
      $hour_m = str_pad(trim($hourexplode[1]), 2, 0, STR_PAD_RIGHT);
      $hour = "$hour_h:$hour_m";

      $date = explode("/", $date);
      $day = $date[0];
      $month = $date[1];
      $year = $date[2];

      $normalDate = "$day/$month/$year $hour";
      $mysqlDate = "$year-$month-$day $hour";

      if ($export === 'mysql') {
        return $mysqlDate;
      }

      return $normalDate;
    }

    $queryInsert = "INSERT INTO printdata(user, printer, date_export, copy_bw, copy_color, print_bw, print_color, status) VALUES ";
    $largePagePay2 = true;

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
      $filename = $_FILES['file']['tmp_name'];
      $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

      if ($lines !== false) {
        $printerName =  (explode("\t", substr($lines[0], 2)))[4];
        $serialNumber = $lines[1];
        $fileDate = $lines[2];
        $tableHeaders = explode("\t", $lines[3]);
        $tableHeaders2 = explode("\t", $lines[4]);
        $tableHeaders3 = explode("\t", $lines[5]);

        $dateExport = parseDate($fileDate);

    ?>

        <h2 class='mt-4'>Informações da Impressora</h2>
        <div class='row'>
          <div class='col-md-4'>
            <p class='mb-0'><strong>Nome da Impressora:</strong><?= $printerName; ?></p>
          </div>
          <div class='col-md-4'>
            <p class='mb-0'><strong>Número de Série:</strong><?= $serialNumber; ?></p>
          </div>
          <div class='col-md-4'>
            <p class='mb-0'><strong>Data da Geração do Arquivo:</strong><?= parseDate($fileDate, 'normal'); ?></p>
          </div>
        </div>

        <h2 class='mt-4'>Dados Coletados</h2>
        <table class="table table-striped table-bordered table-responsive mt-2 w-100">
          <thead style='backgroud-color: gray;'>
            <tr>

          <?

          foreach ($tableHeaders as $header) {
            echo "<th>$header</th>";
          }
          echo "</tr>";

          echo "<tr>";
          foreach ($tableHeaders2 as $header2) {
            echo "<th>$header2</th>";
          }
          echo "</tr>";

          echo "<tr>";
          foreach ($tableHeaders3 as $header3) {
            echo "<th>$header3</th>";
          }
          echo "</tr></thead><tbody>";

          for ($i = 7; $i < count($lines); $i++) {
            $rowData = explode("\t", $lines[$i]);

            $accoutnName = trim($rowData[0]);

            echo "<pre>";
            var_dump(($rowData[1]));
            var_dump(intval($rowData[1]));
            echo "</pre>";
            die;

            if (count($rowData) > 1 && $accoutnName != '') {
              echo "<tr>";
              echo "<td>{$accoutnName}</td>";
              "<td>{$accoutnName}</td>";

              $copy_bw = 0;
              $copy_color = 0;
              $print_bw = 0;
              $print_color = 0;


              for ($j = 1; $j < count($rowData); $j++) {

                $valueRow = $rowData[$j];
                $valor = (int)$valueRow;

                echo "<td>" . $valor . "</td>";

                switch ($j) {
                  case 6: {
                      $copy_color += $valor;
                      break;
                    }
                  case 7: {
                      $copy_bw += $valor;
                      break;
                    }
                  case 8: {
                      $copy_color += $valor;
                      break;
                    }
                  case 9: {
                      $copy_bw += $valor;
                      break;
                    }
                  case 11: {
                      $copy_color += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                  case 12: {
                      $copy_bw += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                  case 13: {
                      $copy_color += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                  case 14: {
                      $copy_bw += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                  case 16: {
                      $print_color += $valor;
                      break;
                    }
                  case 17: {
                      $print_bw += $valor;
                      break;
                    }
                  case 18: {
                      $print_color += $valor;
                      break;
                    }
                  case 20: {
                      $print_color += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                  case 21: {
                      $print_bw += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                  case 22: {
                      $print_color += ($largePagePay2 ? $valor * 2 : $valor);
                      break;
                    }
                }
              }

              echo "</tr>";

              $queryPrint = "<br/>" . "('{$rowData[0]}','{$printerName}','{$dateExport}',{$copy_bw},{$copy_color},{$print_bw},{$print_color},'N')";
              $queryPrint .= ($i < count($lines) - 2 ? ',' : ';');
              $queryInsert .= $queryPrint;
            }
          }


          echo "</tbody></table>";
        } else {
          echo "Falha ao ler o arquivo.";
        }
      }
          ?>
  </div>

  <br />
  <br />
  <br />
  <?= $queryInsert; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>