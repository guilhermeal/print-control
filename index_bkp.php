<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
// FUNCTIONS

function parseDate($string, $export = 'mysql', $from = 'layout')
{
  $parts = explode(" ", $string);
  $partdate = $parts[0];
  $parthour = $parts[1];
  $date = @(explode("\t", $partdate)[4]);

  $hourexplode = explode(':', $parthour);
  $hour_h = str_pad(str_pad(trim($hourexplode[0]), 2, 0, STR_PAD_LEFT), 2, 0, STR_PAD_RIGHT);
  $hour_m = str_pad(str_pad(trim($hourexplode[1]), 2, 0, STR_PAD_LEFT), 2, 0, STR_PAD_RIGHT);
  $hour = "$hour_h:$hour_m";

  $date = @explode("/", $date ? $date : $partdate);
  $day = @str_pad(trim($date[0]), 2, 0, STR_PAD_LEFT);
  $month = @str_pad(trim($date[1]), 2, 0, STR_PAD_LEFT);
  $year = @str_pad(trim($date[2]), 4, 0, STR_PAD_LEFT);

  if ($from == 'db') {
    $date = @(explode("-", $partdate));
    $year = @str_pad(trim($date[0]), 4, 0, STR_PAD_LEFT);
    $month = @str_pad(trim($date[1]), 2, 0, STR_PAD_LEFT);
    $day = @str_pad(trim($date[2]), 2, 0, STR_PAD_LEFT);
  }

  $normalDate = "$day/$month/$year $hour";
  $mysqlDate = "$year-$month-$day $hour";

  if ($export === 'mysql') {
    return $mysqlDate;
  }

  return $normalDate;
}

function connectDB()
{
  $db_host = '127.0.0.1';
  $db_user = 'root';
  $db_pass = 'toor';
  $db_collect = 'impressao';

  $con = mysqli_connect($db_host, $db_user, $db_pass, $db_collect);

  // Check connection
  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
  }

  if ($con) {
    echo "<script>console.log('✨ DB Connected!');</script>";
    return $con;
  } else {
    throw new Exception('Connect db error.');
  }
}

?>

<!DOCTYPE html>
<html>

<head>
  <title><?= (@$_GET['option'] == 'reports' && @$_GET['action'] == 'generate' && @$_POST ? "Relatório :: Contador de Impressões :: De: " . $_POST['report_from'] . " - Até: " . $_POST['report_to'] : 'Leitura de Arquivo TXT'); ?></title>
  <link href="style/css.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <?
  if (@$_GET['option'] == 'reports' && @$_GET['action'] == 'generate') {
  ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <?
  }
  ?>

</head>

<body>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container">
        <a class="navbar-brand" href="#">Print Control</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link <?= (@$_GET['option'] == '' ? ' fw-bold text-decoration-underline link-light' : ''); ?>" href="./">IMPORTAR</a></li>
            <li class="nav-item"><a class="nav-link <?= (@$_GET['option'] == 'reports' ? ' fw-bold text-decoration-underline link-light' : ''); ?>" href="./?option=reports">RELATÓRIOS</a></li>
            <li class="nav-item"><a class="nav-link <?= (@$_GET['option'] == 'printers' ? ' fw-bold text-decoration-underline link-light' : ''); ?>" href="./?option=printers">IMPRESSORAS</a></li>
            <li class="nav-item"><a class="nav-link <?= (@$_GET['option'] == 'users' ? ' fw-bold text-decoration-underline link-light' : ''); ?>" href="./?option=users">USUÁRIOS</a></li>
            <li class="nav-item"><a class="nav-link <?= (@$_GET['option'] == 'sectors' ? ' fw-bold text-decoration-underline link-light' : ''); ?>" href="./?option=sectors">SETORES</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
  <div class="container" style="max-width: 90vw !important;">
    <?php
    switch (@$_GET['option']) {
      case 'reports': {
    ?>
          <h3 class="mt-4">
            <span>
              <i class="bi bi-newspaper"></i> Relatório :: Contador de Impressões
            </span>
          </h3>
          <?
          $link = connectDB();

          if ($result = mysqli_query($link, "SELECT DISTINCT date_export AS DATES FROM `printdata`;")) {
            $qtdRows = mysqli_num_rows($result);
            if ($qtdRows > 0) {
              $options_reports = array();
              while ($row = mysqli_fetch_row($result)) {
                $option = $row[0];
                $option_value = parseDate($row[0], 'normal', 'db');
                array_push($options_reports, $row[0]);
              }

          ?>
              <form target="_top" action="<?= (@$_GET['option'] ? './?option=' . $_GET['option'] : '') . '&action=generate'; ?>" method="post">
                <div class="input-group">
                  <label for="report_from" class="input-group-text">DE: </label>
                  <select class="form-select" id="report_from" name="report_from" required>
                    <option value="" selected disabled>Selecione uma data inicial</option>
                    <?
                    foreach (array_keys($options_reports) as $option) {
                    ?>
                      <option value="<?= $options_reports[$option]; ?>" <?= (@$_POST['report_from'] == $options_reports[$option] ? "selected" : ""); ?>><?= parseDate($options_reports[$option], 'normal', 'db'); ?></option>
                    <?
                    }
                    ?>
                  </select>
                  <label for="report_to" class="input-group-text">ATE: </label>
                  <select class="form-select" id="report_to" name="report_to" required>
                    <option value="" selected disabled>Selecione uma data final</option>
                    <?
                    foreach (array_keys($options_reports) as $option) {
                    ?>
                      <option value="<?= $options_reports[$option]; ?>" <?= (@$_POST['report_to'] == $options_reports[$option] ? "selected" : ""); ?>><?= parseDate($options_reports[$option], 'normal', 'db'); ?></option>
                    <?
                    }
                    ?>
                  </select>
                  <button class="btn btn-outline-primary" type="submit" id="button-addon2">Gerar!</button>
                </div>
                <div class="container mt-2">
                  <div class="row align-items-start">
                    <div class="col-3 form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="showzero" name="showzero" <?= (@$_POST['showzero'] == 'on' ? 'checked' : ''); ?>>
                      <label class="form-check-label" for="showzero">Mostrar zerados</label>
                    </div>
                  </div>
                </div>
              </form>
            <?
            }
          } else {
            ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              Erro ao consultar dados!
            </div>
            <?
            throw new Exception('Connect db error.');
          }
          mysqli_close($link);

          switch (@$_GET['action']) {
            case 'generate': {

                $link = connectDB();
                $post_from = ($_POST['report_from']);
                $post_to = ($_POST['report_to']);
                $showzero = @$_POST['showzero'] == 'on' ? true : false;

                $queryReport = "SELECT
                  `user`,
                  (`copy_bw` - (SELECT `copy_bw` FROM `printdata` WHERE `user` = p1.user AND `status` = 'N' AND `date_export` = '" . ($post_from) . "')) AS `copy_bw_diff`,
                  (`copy_color` - (SELECT `copy_color` FROM `printdata` WHERE `user` = p1.user AND `status` = 'N' AND `date_export` = '" . ($post_from) . "')) AS `copy_color_diff`,
                  (`print_bw` - (SELECT `print_bw` FROM `printdata` WHERE `user` = p1.user AND `status` = 'N' AND `date_export` = '" . ($post_from) . "')) AS `print_bw_diff`,
                  (`print_color` - (SELECT `print_color` FROM `printdata` WHERE `user` = p1.user AND `status` = 'N' AND `date_export` = '" . ($post_from) . "')) AS `print_color_diff`
              FROM printdata p1
              WHERE `status` = 'N' AND `date_export` = '" . ($post_to) . "'
              ORDER BY `user` ASC
              ;";

              echo $queryReport ;

                if ($result = mysqli_query($link, $queryReport)) {
                  $qtdRows = mysqli_num_rows($result);
                  if ($qtdRows > 0) {
                    $array_users = array();

            ?>

                    <div class='printers-grpah'>
                      <canvas id="myChart"></canvas>
                    </div>

                    <table class="table table-striped mt-5">
                      <thead>
                        <tr>
                          <th>Usuário</th>
                          <th class="text-center">Cópia P&B</th>
                          <th class="text-center">Cópia Cor</th>
                          <th class="text-center">Impressão P&B</th>
                          <th class="text-center">Impressão Cor</th>
                          <th class="text-center">Total P&B</th>
                          <th class="text-center">Total Cor</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?

                        $total_copy_bw = 0;
                        $total_copy_color = 0;
                        $total_print_bw = 0;
                        $total_print_color = 0;



                        while ($rows = mysqli_fetch_row($result)) {
                          $total_line_bw = 0;
                          $total_line_color = 0;
                          $i = 0;
                          $printRow = "";
                          foreach ($rows as $cell) {
                            switch ($i) {
                              case 0: {
                                  $user = $cell;
                                  $array_users[$user] = array();
                                  break;
                                }
                              case 1: {
                                  $total_line_bw += $cell;
                                  $total_copy_bw += $cell;
                                  break;
                                }
                              case 2: {
                                  $total_line_color += $cell;
                                  $total_copy_color += $cell;
                                  break;
                                }
                              case 3: {
                                  $total_line_bw += $cell;
                                  $total_print_bw += $cell;
                                  break;
                                }
                              case 4: {
                                  $total_line_color += $cell;
                                  $total_print_color += $cell;
                                  break;
                                }
                            }

                            $printRow .= "<td " . ($i > 0 ? 'class="text-center"' : '') . ">" . $cell . "</td>";
                        ?>

                          <?
                            $i++;
                          }

                          $printRow .= "<td class='text-center'>" . $total_line_bw . "</td>";
                          $printRow .= "<td class='text-center'>" . $total_line_color . "</td>";
                          if ($showzero || ($total_line_bw + $total_line_color > 0)) {

                            array_push($array_users[$user], [$total_line_bw, $total_line_color]);

                          ?>
                            <tr>
                              <?= $printRow; ?>
                            </tr>
                        <?
                          }
                        }
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th scope="row">TOTAL</th>
                          <th class='text-center'><?= $total_copy_bw; ?></th>
                          <th class='text-center'><?= $total_copy_color; ?></th>
                          <th class='text-center'><?= $total_print_bw; ?></th>
                          <th class='text-center'><?= $total_print_color; ?></th>
                          <th class='text-center'><?= ($total_copy_bw + $total_print_bw); ?></th>
                          <th class='text-center'><?= ($total_copy_color + $total_print_color); ?></th>
                        </tr>
                      </tfoot>
                    </table>

                    <?
                    $lista = "";
                    $data1 = "";
                    $data2 = "";
                    $data = "";

                    foreach (array_keys($array_users) as $user) {
                      if ($array_users[$user]) {
                        $lista .= "'" . $user . "',";
                        $data1 .= $array_users[$user][0][0] . ",";
                        $data2 .= $array_users[$user][0][1] . ",";
                      }
                    }
                    $data .= "{
                        type: 'bar',
                        label: 'Total P&B',
                        data: [" . rtrim($data1, ',') . "],
                        backgroundColor: '#999',
                        order: 1
                      }, {
                        type: 'bar',
                        label: 'Total Cor',
                        data: [" . rtrim($data2, ',') . "],
                        backgroundColor: '#FF6384',
                        order: 2
                      }";


                    ?>
                    <script>
                      function beforePrintHandler() {
                        for (let id in Chart.instances) {
                          Chart.instances[id].resize(200, 200);
                        }
                      }

                      const ctx = document.getElementById('myChart');

                      new Chart(ctx, {
                        type: 'bar',
                        data: {
                          labels: [<?= rtrim($lista, ','); ?>],
                          datasets: [<?= $data; ?>]
                        },
                        options: {
                          scales: {
                            y: {
                              beginAtZero: true
                            }
                          }
                        }
                      });

                      window.addEventListener('beforeprint', () => {
                        console.log('### BEFORE PRINT ##');
                        ctx.style.width = "450px";
                        ctx.style.height = "300px";
                        ctx.resize();
                      });
                      window.addEventListener('afterprint', () => {
                        console.log('### AFTER PRINT ##');
                        ctx.style.width = "100%";
                        ctx.style.height = "550px";
                        ctx.resize();
                      });
                    </script>
                  <?
                  }
                } else {
                  ?>
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Erro ao consultar dados!
                  </div>
          <?
                  throw new Exception('Connect db error.');
                }
                mysqli_close($link);
                break;
              }
          }
          break;
        }
      case 'printers': {
          $modal = array(
            "ref" => "new_printer_modal",
            "title" => "Adicionar novo usuário",
            "action" => "add",
            "fields" => array(
              "login" => array(
                "id" => "user",
                "label" => "Usuário",
                "icon" => "person-fill",
                "type" => "text",
                "default" => "",
                "title" => "Informe o login do usuario",
                "required" => true,
              ),
              "pass" => array(
                "id" => "pass",
                "label" => "ID (senha)",
                "icon" => "123",
                "type" => "number",
                "default" => "",
                "title" => "Informe o ID do usuario",
                "required" => true,
              ),
              "sector" => array(
                "id" => "sector",
                "label" => "Setor",
                "icon" => "person-vcard-fill",
                "type" => "select",
                "options" => array(
                  12 => "ti",
                  32 => "financeiro",
                  563 => "contabilidade",
                  124 => "comercial",
                ),
                "default" => "",
                "title" => "Selecione o setor do usuário",
                "required" => true,
              ),
              "status" => array(
                "id" => "status",
                "label" => "Ativar usuário",
                "type" => "switch",
                "default" => true,
                "title" => "Marque ativar o usuário",
              ),
            ),
          );
          ?>
          <h3 class="mt-4">
            <span>
              <i class="bi bi-printer-fill"></i> Impressoras
            </span>
            <?
            if (@$modal) {
            ?>
              <button type="button" class="btn btn-outline-primary btn-md" data-bs-toggle="modal" data-bs-target="#<?= $modal['ref']; ?>" title="Adicionar novo Usuário">
                <i class="bi bi-plus-circle-fill"></i>
              </button>
            <?
            }
            ?>
          </h3>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Alias</th>
                <th>Fabricante</th>
                <th>Modelo</th>
                <th>IP</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        <?
          break;
        }
      case 'users': {
          $modal = array(
            "ref" => "new_user_modal",
            "title" => "Adicionar novo usuário",
            "action" => "add",
            "fields" => array(
              "login" => array(
                "id" => "user",
                "label" => "Usuário",
                "icon" => "person-fill",
                "type" => "text",
                "default" => "",
                "title" => "Informe o login do usuario",
                "required" => true,
              ),
              "pass" => array(
                "id" => "pass",
                "label" => "ID (senha)",
                "icon" => "123",
                "type" => "number",
                "default" => "",
                "title" => "Informe o ID do usuario",
                "required" => true,
              ),
              "sector" => array(
                "id" => "sector",
                "label" => "Setor",
                "icon" => "person-vcard-fill",
                "type" => "select",
                "options" => array(
                  12 => "ti",
                  32 => "financeiro",
                  563 => "contabilidade",
                  124 => "comercial",
                ),
                "default" => "",
                "title" => "Selecione o setor do usuário",
                "required" => true,
              ),
              "status" => array(
                "id" => "status",
                "label" => "Ativar usuário",
                "type" => "switch",
                "default" => true,
                "title" => "Ativar usuário",
              ),
            ),
          );
        ?>
          <h3 class="mt-4">
            <span>
              <i class="bi bi-person-fill"></i> Usuários
            </span>
            <?
            if (@$modal) {
            ?>
              <button type="button" class="btn btn-outline-primary btn-md" data-bs-toggle="modal" data-bs-target="#<?= $modal['ref']; ?>" title="Adicionar novo Usuário">
                <i class="bi bi-plus-circle-fill"></i>
              </button>
            <?
            }
            ?>
          </h3>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Sector</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
          <?
          switch (@$_GET['action']) {
            case 'add': {
                print_r($_POST);
                if (!empty($_POST)) {
                  // Array ( [user] => zigbee [pass] => 333311 [sector] => 12 [status] => on )

                  $insertQuery = "INSERT INTO users (login, pass, idsector, status) VALUES ('" . $_POST['user'] . "','" . $_POST['pass'] . "','" . $_POST['sector'] . "','" . ($_POST['status'] == 'on' ? 'N' : 'X') . "')";

                  echo "<pre>";
                  var_dump($insertQuery);
                  echo "</pre>";
                  echo "<code>$insertQuery</code>";
                  die;
                }

                break;
              }
          }
          break;
        }
      case 'sectors': {
          ?>
          <h3 class="mt-4">
            <span>
              <i class="bi bi-person-vcard-fill"></i> Setores
            </span>
            <?
            if (@$modal) {
            ?>
              <button type="button" class="btn btn-outline-primary btn-md" data-bs-toggle="modal" data-bs-target="#<?= $modal['ref']; ?>" title="Adicionar novo Usuário">
                <i class="bi bi-plus-circle-fill"></i>
              </button>
            <?
            }
            ?>
          </h3>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        <?
          break;
        }
      default: {
        ?>
          <h3 class="mt-4">Leitura de Arquivo TXT</h3>

          <form method="post" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
              <input type="file" name="file" accept=".txt" class="form-control-file">
              <button type="submit" class="btn btn-primary">Enviar Arquivo</button>
            </div>
          </form>
          <?php

          $queryInsert = "INSERT INTO printdata (user, printer, date_export, copy_bw, copy_color, print_bw, print_color, status) VALUES ";
          $largePagePay2 = true;

          if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['file']['tmp_name'];
            $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $line0 = preg_replace('/\t/', '', $lines[0]);
            $printer = $line0 === 'Machine NameC280' ? 'C280' : 'KYO';

            switch ($printer) {
              case 'C280': {
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
                    <div class="table-responsive">
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

                            $counter = array();

                            for ($i = 7; $i < (count($lines)); $i++) {
                              $rowData = explode("\t", $lines[$i]);
                              $accoutnName = trim($rowData[0]);

                              if (count($rowData) > 1 && $accoutnName != '' && $accoutnName != 'Public' && $accoutnName != 'BoxAdmin') {
                                echo "<tr>";
                                echo "<td>{$accoutnName}</td>";
                                "<td>{$accoutnName}</td>";

                                $copy_bw = 0;
                                $copy_color = 0;
                                $print_bw = 0;
                                $print_color = 0;


                                for ($j = 1; $j < (count($rowData) - 1); $j++) {

                                  $valueRow = $rowData[$j];
                                  $valor = (int)$valueRow;

                                  echo "<td>" . $valor . "</td>";
                                  @$counter[$j] += $valor;

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
                                  }
                                }

                                echo "</tr>";

                                $queryPrint = "('{$rowData[0]}','{$printer}','{$dateExport}',{$copy_bw},{$copy_color},{$print_bw},{$print_color},'N')";
                                $queryPrint .= ($i < count($lines) - 3 ? ', ' : ';');
                                $queryInsert .= $queryPrint;
                              }
                            }


                            echo "</tbody>";
                            ?>
                        <tfoot>
                          <tr>
                            <th scope="row">TOTAL</th>
                            <?
                            for ($x = 1; $x < ($i + 3); $x++) {
                              echo "<td>" . @$counter[$x] . "</td>";
                            }
                            ?>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  <?
                  } else {
                    echo "Falha ao ler o arquivo.";
                  }
                  break;
                }
              case 'KYO': {

                  $headers = $lines[0];

                  if ($lines !== false) {
                    $printerName =  (explode('";"', substr($lines[1], 1)))[0];
                    $serialNumber = (explode('";"', substr($lines[1], 1)))[2];
                    $fileDate = (explode('";"', substr($lines[1], 1)))[37];
                    $tableHeaders = explode(";", $headers);
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
                    <div class="table-responsive">
                      <table class="table table-striped table-bordered table-responsive mt-2 w-100">
                        <thead style='backgroud-color: gray;'>
                          <tr>

                            <?

                            foreach ($tableHeaders as $header) {
                              echo "<th>$header</th>";
                            }
                            echo "</tr></thead><tbody>";

                            $counter = array();


                            for ($i = 1; $i < (count($lines)); $i++) {
                              $rowData = explode(";", $lines[$i]);
                              $accoutnName = str_replace('"', '', trim($rowData[4]));

                              if (count($rowData) > 1 && $accoutnName != '' && $accoutnName != 'Public' && $accoutnName != 'BoxAdmin' && $accoutnName != 'Outros') {
                                echo "<tr>";

                                $copy_bw = 0;
                                $copy_color = 0;
                                $print_bw = 0;
                                $print_color = 0;

                                for ($j = 0; $j < (count($rowData)); $j++) {

                                  $valueRow = str_replace('"', '', $rowData[$j]);
                                  $valor = (int)$valueRow;

                                  echo "<td>" . $valueRow . "</td>";
                                  @$counter[$j] += $valor;

                                  switch ($j) {
                                    case 5: {
                                        $print_bw += $valor;
                                        break;
                                      }
                                    case 6: {
                                        $copy_bw += $valor;
                                        break;
                                      }
                                  }
                                }

                                echo "</tr>";

                                $queryPrint = "('{$accoutnName}','{$printer}','{$dateExport}',{$copy_bw},{$copy_color},{$print_bw},{$print_color},'N')";
                                $queryPrint .= ($i < count($lines) - 2 ? ', ' : ';');
                                $queryInsert .= $queryPrint;
                              }
                            }


                            echo "</tbody>";
                            ?>
                        <tfoot>
                          <tr>
                            <th scope="row" colspan="5">TOTAL</th>
                            <?
                            for ($x = 5; $x < (count($rowData) - 1); $x++) {
                              echo "<td style='font-weight: bold;'>" . @$counter[$x] . "</td>";
                            }
                            ?>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
            <?
                  } else {
                    echo "Falha ao ler o arquivo.";
                  }
                  break;
                }
            }

            ?>
            <hr />
            <code>
              <?= $queryInsert; ?>
            </code>
            <?
            $con = connectDB();
            if ($res = mysqli_query($con, $queryInsert)) {

            ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                Dados armazenados com sucesso!
              </div>
            <?
            } else {

            ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Erro ao armazenar os dados!
              </div>
          <?
              throw new Exception('Connect db error.');
            }
            mysqli_close($con);
          }
          ?>
    <?php
          break;
        }
    }
    ?>

    <?php
    if (@$modal) {
    ?>
      <div class="modal fade" id="<?= $modal['ref']; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="<?= $modal['ref']; ?>Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <form target="_top" action="<?= (@$_GET['option'] ? './?option=' . $_GET['option'] : '') . (@$modal['action'] ? '&action=' . $modal['action'] : ''); ?>" method="post">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="<?= $modal['ref']; ?>Label"><?= $modal['title']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <?
                foreach ($modal['fields'] as $field) {
                ?>
                  <div class="mb-3">
                    <label for="<?= $field['id']; ?>" class="col-form-label fw-bold"><?= $field['label'] ?>:</label>
                    <?
                    switch ($field['type']) {
                      default: {
                    ?>
                          <div class="input-group mb-3">
                            <?
                            if (!empty($field['icon'])) {
                            ?>
                              <span class="input-group-text" id="<?= $field['id'] . "_aria"; ?>">
                                <i class="bi bi-<?= $field['icon']; ?>"></i>
                              </span>
                            <?
                            }
                            ?>
                            <input type="<?= ($field['type']); ?>" id="<?= $field['id']; ?>" aria-label="<?= $field['label']; ?>" aria-describedby="<?= ($field['id'] . "_aria"); ?>" name="<?= $field['id']; ?>" class="form-control" value="<?= $field['default']; ?>" placeholder="<?= $field['title']; ?>" title="<?= $field['title']; ?>" <?= (!empty($field['required']) && $field['required'] === true ? 'required' : ''); ?> />
                          </div>
                        <?
                          break;
                        }
                      case 'textarea': {
                        ?>
                          <div class="input-group mb-3">
                            <span class="input-group-text" id="<?= $field['id'] . "_aria"; ?>"><i class="bi bi-<?= $field['icon']; ?>"></i></span>
                            <textarea id="<?= $field['id']; ?>" aria-label="<?= $field['label']; ?>" name="<?= $field['id']; ?>" class="form-control" value="<?= $field['default'] ?>" placeholder="<?= $field['title'] ?>" title="<?= $field['title']; ?>" <?= (!empty($field['required']) && $field['required'] === true ? 'required' : ''); ?>></textarea>
                          </div>
                        <?
                          break;
                        }
                      case 'select': {
                        ?>
                          <div class="input-group mb-3">
                            <label for="<?= $field['id']; ?>" class="input-group-text">
                              <?= (!empty($field['icon']) ? '<i class="bi bi-' . $field['icon'] . '"></i>' : '#'); ?>
                            </label>
                            <select class="form-select" id="<?= $field['id']; ?>" name="<?= $field['id']; ?>" aria-label="<?= $field['label']; ?>" <?= (!empty($field['required']) && $field['required'] === true ? 'required' : ''); ?>>
                              <option value="" selected disabled><?= (!empty($field['title']) ? $field['title'] : 'Selecione uma opção'); ?></option>
                              <?
                              foreach (array_keys($field['options']) as $option_key) {
                              ?>
                                <option value="<?= $option_key; ?>"><?= $field['options'][$option_key]; ?></option>
                              <?
                              }
                              ?>
                            </select>

                          </div>
                        <?
                          break;
                        }
                      case 'switch': {
                        ?>
                          <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="<?= $field['id']; ?>" <?= ($field['default'] === true ? 'checked' : ''); ?> name="<?= $field['id']; ?>" title="<?= $field['title']; ?>" />
                            <label class="form-check-label" for="<?= $field['id']; ?>"><?= $field['title']; ?></label>
                          </div>
                    <?
                          break;
                        }
                    }
                    ?>
                  </div>
                <?
                }
                ?>
              </div>
              <div class="modal-footer">
                <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="reset" class="btn btn-warning">Resetar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    <?
    }
    ?>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>