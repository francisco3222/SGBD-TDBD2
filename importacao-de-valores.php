<?php
require_once("custom/php/common.php");
echo "<link rel='stylesheet' type='text/css' href='../css/ag.css'>";
echo '<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@200&display=swap" rel="stylesheet">';
echo '<link href="https://fonts.googleapis.com/css2?family=Comfortaa&display=swap" rel="stylesheet">';
echo "<div class=baseL>";
use PhpOffice\PhpSpreadsheet\IOFactory;
if (!is_user_logged_in() || !current_user_can("values_import"))
{
    echo "Não tem autorização para aceder a esta página.";
}
elseif(!isset($_REQUEST['estado']) || $_REQUEST['estado'] == "")
{
    echo '<h3 class="animatedL">Importação de Valores - escolher criança</h3>';
    echo '<table style="width:100%" class="outerTabelaL">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Nome</th>';
    echo '<th>Data de nascimento</th>';
    echo '<th>Encarregado de educação</th>';
    echo '<th>Telefone do Enc.</th>';
    echo '<th>e-mail</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    $queryChild = "SELECT *
                   FROM child";
    $resultChild = mysqli_query($conexao, $queryChild);
    foreach($resultChild as $rowChild)
    {
        echo '<tr class="tabelaL">';
        echo '<td class="tabelaL"><a href="importacao-de-valores?estado=escolheritem&crianca='.$rowChild['id'].'">'.$rowChild['name'].'</a></td>';
        echo '<td class="tabelaL">'.$rowChild['birth_date'].'</td>';
        echo '<td class="tabelaL">'.$rowChild['tutor_name'].'</td>';
        echo '<td class="tabelaL">'.$rowChild['tutor_phone'].'</td>';
        echo '<td class="tabelaL">'.$rowChild['tutor_email'].'</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
elseif($_REQUEST['estado'] == "escolheritem") {
    $_SESSION['child_id'] = $_REQUEST['crianca'];
    echo '<h3 class="titleL">Importação de valores - escolher item</h3>';
    $types = "SELECT id,
                    name
                FROM item_type;";
    $typeResult = mysqli_query($conexao, $types);
    echo "<div class='containerL'>";
    $NResultados = mysqli_num_rows($typeResult);
    $forEachColumn = 0;
    $breakColumns = False;
    echo "<div class='columnL'>";
    foreach ($typeResult as $type) {
        if ($forEachColumn >= ($NResultados / 2) && !$breakColumns) {
            echo "</div>";
            echo "<div class='columnL'>";
            $breakColumns = True;
        }
        $itens = "SELECT item_type_id,
                            name,
                            id
                        FROM item
                        WHERE item_type_id = " . $type['id'] . ";";
        $itemResult = mysqli_query($conexao, $itens);
        if (mysqli_num_rows($itemResult) >= 1) {
            echo '<ul>';
            echo '<li>' . $type['name'] . '</li>';
            foreach ($itemResult as $item) {

                $subs = "SELECT item_id
                            FROM subitem   
                            WHERE item_id = " . $item['id'] . ";";
                $subResult = mysqli_query($conexao, $subs);
                if (mysqli_num_rows($subResult) >= 1) {
                    echo '<ul>';
                    echo '<li>[<a href=importacao-de-valores?estado=introducao&crianca=' . $_SESSION['child_id'] . '&item=' . $item['id'] . '>' . $item['name'] . '</a>]</li>';
                    echo '</ul>';
                }
            }
            echo '</ul>';
        }
    }
}
elseif($_REQUEST['estado'] == "introducao")
{
    $childId = $_GET['crianca'];
    $itemId = $_GET['item'];
    $querySubs = "SELECT form_field_name,
                    value_type,
                    id
                  FROM subitem
                    WHERE subitem.item_id = $itemId";
    $subResult = mysqli_query($conexao, $querySubs);
    echo '<table style="width:100%" class="outerTabelaL">';
    echo '<tbody>';
    echo '<tr>';
    foreach ($subResult as $subitem)
    {
        echo '<td>'.$subitem['form_field_name'];
        if ($subitem['value_type'] == 'enum') {
            $valueCounter = "SELECT *
                                FROM subitem_allowed_value
                                WHERE subitem_id = " . $subitem['id'] . ";";
            $valueCounted = mysqli_query($conexao, $valueCounter);
            echo " -> " . mysqli_num_rows($valueCounted);
            echo '</td>';
        }
    }
    echo '</tr>';
    echo '<tr>';
    foreach ($subResult as $subitem)
    {
        echo '<td>'.$subitem['id'].'</td>';
    }
    echo '</tr>';
    echo '<tr>';
    foreach ($subResult as $subitem)
    {
        if ($subitem['value_type'] == 'enum') {
            $valuesQuery = "SELECT `value` as val
                                FROM subitem_allowed_value
                                WHERE subitem_id = " . $subitem['id'] . ";";
            $valuesResult = mysqli_query($conexao, $valuesQuery);
            echo '<td>';
            foreach ($valuesResult as $values)
            {
                echo $values['val'] . '<br>';
            }
            echo '</td>';
        }
        else
        {
            echo '<td></td>';
        }
    }
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo 'Deverá copiar estas linhas para um ficheiro excel e preencher com os dados pretendidos, sendo que nos casos de varios tipos de valores possíveis deve inserir um 0 quando o valor permitido n�o se aplicar e um 1 quando se aplicar';
    echo '<br>';
    echo '<br>';
    echo '<form action="" class="centerL"  method="post">';
    echo '<input type="file" accept=".xlsx" class="inputL" name="ficheiro">';
    echo '<input type="hidden" name="estado" value="validacao">';
    echo '<input type="hidden" name="crianca" value="'.$childId.'">';
    echo '<br>';
    echo '<br>';
    echo '<input type="submit" class="SubmitL" value="Enviar">';
    echo '</form>';
}
elseif($_REQUEST['estado'] == "validacao")
{
    $childId = $_GET['crianca'];
    if (isset($_FILES['ficheiro'])) {
        $file = $_FILES['ficheiro']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        $_SESSION['data'] = $data;
        echo '<h3>Importação de valores - validação</h3>';
        echo "Está prestes a inserir os dados seguintes na base de dados, confirma?";
        var_dump($data);
    } else {
        echo "Nenhum ficheiro enviado.";
    }
}