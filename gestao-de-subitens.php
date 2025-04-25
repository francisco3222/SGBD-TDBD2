<?php

// verificar se colocando um subitem já existente ele dá o erro ou nn , fazer o mesmo no gestão de unidades
require_once('common.php');

$query_do_item = "SELECT * FROM item ORDER BY name ASC";
$result_do_item = mysqli_query($conexao, $query_do_item);

$show_table = true;

$query_de_subItemUnit = "SELECT * FROM subitem_unit_type ORDER BY name ASC";
$result_de_subItemUnit = mysqli_query($conexao, $query_de_subItemUnit);


if (is_user_logged_in() && current_user_can('manage_subitems')) // verifica se o utilizador está com sessão iniciada
{
    // Verifica se o formulário foi submetido
    if (isset($_REQUEST['estado_form']) && $_REQUEST['estado_form'] == 'inserir') 
    {
        $subItem = $_REQUEST['subItem'];
        $tipoValor = $_REQUEST['tipoValor'];
        $item = $_REQUEST['item'];
        $tipoCampo = $_REQUEST['tipoCampo'];
        $tipoUnidade = $_REQUEST['tipoUnidade'];                                                                                                                                                                                                                                                           
        $ordemCampo = $_REQUEST['ordemCampo'];
        $obrigatorio = $_REQUEST['obrigatorio'];

        // Validação dos campos
        if (empty($subItem))
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Nome do subitem' é obrigatório.";
        }
        elseif(is_numeric($subItem))
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Nome do subitem' não pode ser um número.";
        }
        elseif (empty($tipoValor)) 
        {
            // ver se nas seleções tds usar isset em vez de empty para ver se está vazio ou não
            $show_table = false;
            echo "<br>";
            echo "O campo 'Tipo do valor' é obrigatório.";
        }
        elseif (empty($item)) 
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Item' é obrigatório.";
        }
        elseif (empty($tipoCampo)) 
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Tipo do campo' é obrigatório.";
        }
        elseif (empty($ordemCampo) )
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Ordem do campo' é obrigatório";
        }
        elseif ($ordemCampo < 0) 
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Ordem do campo' deve ser um número inteiro maior que 0.";
        }
        elseif (!is_numeric($ordemCampo)) 
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Ordem do campo' deve ser um número inteiro.";
        }
        elseif (!isset($obrigatorio)) 
        {
            $show_table = false;
            echo "<br>";
            echo "O campo 'Obrigatório' é obrigatório.";
        }

        else
        {
            // Verifica se o subitem já existe
            $query_verifica_subItem = "SELECT COUNT(*) AS total FROM subitem WHERE name = '$subItem' AND item_id = '$item'";
            $result_verifica_subItem = mysqli_query($conexao, $query_verifica_subItem);
            $row_verifica_subItem = mysqli_fetch_assoc($result_verifica_subItem);

            // Se o subitem não existir, insere-o
            if ($row_verifica_subItem['total'] == 0) 
            {
                $query_insert_subItem = "INSERT INTO subitem (name, value_type, item_id, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state) 
                                         VALUES ('$subItem', '$tipoValor', '$item', '', '$tipoCampo', '$tipoUnidade', '$ordemCampo', '$obrigatorio', 'active')";
                $result_insert_subItem = mysqli_query($conexao, $query_insert_subItem);

                if ($result_insert_subItem) 
                {
                    $show_table = false;
                    // Recupera o ID do novo subitem inserido
                    
                    $new_subItem_id = mysqli_insert_id($conexao);

                    if (!$new_subItem_id) {
                        die("Erro: Falha ao recuperar o ID do novo subitem inserido.");
                    }

                    $query_do_item = "SELECT name FROM item WHERE id = $item";
                    $result_do_item = mysqli_query($conexao, $query_do_item);

                    if ($result_do_item && mysqli_num_rows($result_do_item) > 0) 
                    {
                        $row_item_name = mysqli_fetch_assoc($result_do_item);
                        $item_name = $row_item_name['name'];
                    } else 
                    {
                        die("Erro: Não foi possível recuperar o nome do item relacionado ao subitem.");
                    }
                    
                    // Gere o form_field_name
                    $form_field_name = substr($item_name, 0, 3) . '-' . $new_subItem_id . '-' . preg_replace('/[^a-z0-9_ ]/i', '', str_replace(' ', '_', $subItem));
                    
                    // Atualiza o form_field_name no banco de dados
                    $query_update_subItem = "UPDATE subitem SET form_field_name = '$form_field_name' WHERE id = $new_subItem_id";
                    mysqli_query($conexao, $query_update_subItem);

                    echo "<h3>Subitem inserido com sucesso</h3>";
                    echo "<br>";
                    echo "ID: " . $new_subItem_id . "<br>";
                    echo "Nome do subitem: " . $subItem . "<br>";
                    echo "Tipo do valor: " . $tipoValor . "<br>";
                    echo "Item: " . $item . "<br>";
                    echo "Tipo do campo: " . $tipoCampo . "<br>";
                    echo "Tipo de unidade: " . $tipoUnidade . "<br>";
                    echo "Ordem do campo: " . $ordemCampo . "<br>";
                    echo "Obrigatório: " . $obrigatorio . "<br>";
                    echo "Nome do campo no formulário: " . $form_field_name . "<br>";
                    echo "<br>";
                    echo "item name form -> ". $item . "<br>";
                    echo 
                    "<br>
                    <form method='post' action=''>
                        <button type='submit' name='continuar'>Continuar</button>
                    </form>";

                }
                else 
                {
                    $show_table = false;
                    echo "<br>";
                    echo "Erro ao inserir subitem: <br> " . mysqli_error($conexao) . "<br>";

                    $new_subItem_id = mysqli_insert_id($conexao);

                    echo "<br>";
                    echo "ID: " . $new_subItem_id . "<br>";
                    echo "Nome do subitem: " . $subItem . "<br>";
                    echo "Tipo do valor: " . $tipoValor . "<br>";
                    echo "Item: " . $item . "<br>";
                    echo "Tipo do campo: " . $tipoCampo . "<br>";
                    echo "Tipo de unidade: " . $tipoUnidade . "<br>";
                    echo "Ordem do campo: " . $ordemCampo . "<br>";
                    echo "Obrigatório: " . $obrigatorio . "<br>";
                    echo "Nome do campo no formulário: " . $form_field_name . "<br>";
                    echo "<br>";
                    echo "item name form -> ". $item_name . "<br>";
                    var_dump($form_field_name);

                }
            } 
            else 
            {
                $show_table = false;
                echo "Erro: O subitem já existe.";
            }
        }
    }

    if ($show_table)
    {
        echo 
        "
        <head>
            <link rel='stylesheet' type = 'text/css' href='../css/ag.css'>
        </head>
        <body>
        <h2 class = 'titulo' id ='topo'> Faça a gestão dos subitens aqui! </h2>
            <br>
            <button onclick = 'scrollToForm()'> Adicionar subitem </button>
            <br>

            <table class = 'bordas'>
                <tr class = 'identificacao' >
                    <td  class = 'bordas'>item</td>
                    <td  class = 'bordas'>id</td>
                    <td  class = 'bordas'>subitem</td>
                    <td  class = 'bordas'>tipo de valor</td>
                    <td  class = 'bordas'>nome do campo no formulário</td>
                    <td  class = 'bordas'>tipo do campo no formulário</td>
                    <td  class = 'bordas'>tipo de unidade</td>
                    <td  class = 'bordas'>ordem do campo no formulário</td> 
                    <td  class = 'bordas'>obrigatório</td> 
                    <td  class = 'bordas'>estado</td>
                    <td  class = 'bordas'>ação</td>
                </tr>
        ";

        while ($row_item = mysqli_fetch_assoc($result_do_item))
        {
            // Consulta para contar o número de subitens do item
            $query_count_subItems = "SELECT COUNT(*) AS subitem_count FROM subitem WHERE item_id = " . $row_item['id'];
            $result_count_subItems = mysqli_query($conexao, $query_count_subItems);
            $row_count_subItems = mysqli_fetch_assoc($result_count_subItems);

            $query_subItems_for_item = "SELECT * FROM subitem WHERE item_id = " . $row_item['id'] . " ORDER BY name ASC";
            $result_subItems_for_item = mysqli_query($conexao, $query_subItems_for_item);

            $subitem_count = $row_count_subItems['subitem_count'];

            echo "<tr>";
            //vai calcular o rowspan com o maior valor entre o subitem_count e 1
            echo "<td  class = 'bordas' id ='item' rowspan='" . max($subitem_count,1) . "'>" . $row_item['name'] . "</td>";

            if (mysqli_num_rows($result_subItems_for_item) > 0) 
            {
                $first_subitem = true;

                while ($row_subItem = mysqli_fetch_assoc($result_subItems_for_item)) 
                {
                    // Primeira linha com o primeiro subitem
                    if ($first_subitem) 
                    {
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['id'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['name'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['value_type'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['form_field_name'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['form_field_type'] . "</td>";

                        if($row_subItem['unit_type_id'] == NULL)
                        {
                            echo "<td  class = 'bordas' id='item'> - </td>";
                        }
                        else
                        {
                            foreach ($result_de_subItemUnit as $row_de_subItemUnit) 
                            {
                                if ($row_de_subItemUnit['id'] == $row_subItem['unit_type_id']) 
                                {
                                    echo "<td  class = 'bordas' id='item'>" . $row_de_subItemUnit['name'] . "</td>";
                                }
                            }
                        }

                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['form_field_order'] . "</td>";

                        if($row_subItem['mandatory'] == 1)
                        {
                            echo "<td  class = 'bordas sim' >sim</td>";
                        }
                        else
                        {
                            echo "<td  class = 'bordas nao'>não</td>";
                        }

                       
                        if($row_subItem['state'] == 'active')
                        {
                            echo "<td class='bordas ativo'>active</td>";
                        }
                        else
                        {
                            echo "<td class='bordas inativo'>inactive</td>";
                        }

                        echo 
                        "<td  class = 'bordas' id='item'><a href='edicao-de-dados.php?id=" . $row_subItem['id'] . "'>Editar</a> 
                              <a href='edicao-de-dados.php?id=" . $row_subItem['id'] . "'>Desativar</a>
                              <a href='edicao-de-dados.php?id=" . $row_subItem['id'] . "'>Ativar</a></td>";
                        echo "</tr>";

                        $first_subitem = false;
                    } 
                    else 
                    {
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['id'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['name'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['value_type'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['form_field_name'] . "</td>";
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['form_field_type'] . "</td>";

                        if($row_subItem['unit_type_id'] == NULL)
                        {
                            echo "<td  class = 'bordas' id='item'> - </td>";
                        }
                        else
                        {
                            foreach ($result_de_subItemUnit as $row_de_subItemUnit)
                            {
                                if ($row_de_subItemUnit['id'] == $row_subItem['unit_type_id']) 
                                {
                                    echo "<td  class = 'bordas' id='item'>" . $row_de_subItemUnit['name'] . "</td>";

                                }
                            }
                        }
                        
                        echo "<td  class = 'bordas' id='item'>" . $row_subItem['form_field_order'] . "</td>";
                        
                        if($row_subItem['mandatory'] == 1)
                        {
                            echo "<td class='bordas sim'>sim</td>";
                        }
                        else
                        {
                            echo "<td class='bordas nao'>não</td>";
                        }

                        if($row_subItem['state'] == 'active')
                        {
                            echo "<td class='bordas ativo'>active</td>";
                        }
                        else
                        {
                            echo "<td class='bordas inativo'>inactive</td>";
                        }


                        echo "<td  class = 'bordas' id='item'><a href='edicao-de-dados.php?id=" . $row_subItem['id'] . "'>Editar</a> 
                              <a href='edicao-de-dados.php?id=" . $row_subItem['id'] . "'>Desativar</a>
                              <a href='edicao-de-dados.php?id=" . $row_subItem['id'] . "'>Ativar</a></td>";
                        echo "</tr>";
                    }
                }
            } 
            else 
            {
                echo "<td  class = 'bordas' id='item' colspan='10'>Não existem subitens</td>";
                echo "</tr>";
            }
        }

        echo 
        "
        </table>
        ";

        echo
        "
           <button onclick = 'scrollToTop()'> Voltar ao Topo </button>
           <br>
           
           <h3 id='meuFormulario'><i>Gestão de subitens - Introdução</i></h3>

           <form method='POST' action='' id='formulario'>
                <label for='subItem' class='required-field' >Nome do subitem:</label>
                <input type='text' id='subItem' name='subItem' class = 'bordasF'>
                <br>
                <br>
                <label for='tipoValor' class='required-field' >Tipo do valor:</label>
                <br>
        ";

        $enum_values = get_enum_values($conexao, 'subitem', 'value_type');
       
        foreach($enum_values as $valorTipo){
            echo "<input type='radio' id='tipoValor" . $valorTipo . "' name='tipoValor' value='" . $valorTipo . "' > " . $valorTipo . "<br>";
        }
    
        echo
        "
            <br>
            <label for='item' class='required-field' >Item:</label>
            <select id='item' name='item' class = 'bordasF' >
            <option>Selecione um item</option>
        ";

        foreach ($result_do_item as $row_do_item) 
        {
            echo "<option value='" . $row_do_item['id'] . "'>" . $row_do_item['name'] . "</option>";
        }

        echo
        "
            </select>
            <br>
            <br>
            <label for='tipoCampo' class='required-field' >Tipo do campo do formulário:</label>
            <br>
        ";
        
        $enum_values_Campo = get_enum_values($conexao, 'subitem', 'form_field_type');
       
        foreach($enum_values_Campo as $valorCampo){
            echo "<input type='radio' id='tipoCampo" . $valorCampo . "' name='tipoCampo' value='" . $valorCampo . "' > " . $valorCampo . "<br>";
        }
        echo
        "
            <br>
            <label for='tipoUnidade' class='required-field'>Tipo de unidade:</label>
            <select id='tipoUnidade' name='tipoUnidade' class = 'bordas'>
            <option>Selecione um tipo de unidade</option>
        ";

        foreach ($result_de_subItemUnit as $row_de_subItemUnit)
        {
            echo "<option value='" . $row_de_subItemUnit['id'] . "'>" . $row_de_subItemUnit['name'] . "</option>";
        }
    
        echo 
        "
            </select>
            <br>
            <br>

            <label for='ordemCampo' class='required-field '>Ordem do campo no formulário:</label>
            <input type='text' id='ordemCampo' name='ordemCampo' class = 'bordasF' >
            <br>
            <br>
        ";         

        echo 
        "
            <label for='obrigatorio' class='required-field'>Obrigatório:</label>
            <br>

            <input type='radio' id='obrigatorio' name='obrigatorio' value='1' > Sim
            <br>
            <input type='radio' id='obrigatorio' name='obrigatorio' value='0' > Não
            <br>
            <br>

            <input type='hidden' id='estado_form' name='estado_form' value='inserir'>
            
            <button type='submit' id='submit' name='submit' value='submit'>Submeter SubItem</button>

        </form>

        <script src = '../js/script.js'></script>

        </body>
        ";
    }
}
else
{
    echo "Não tem permissões para aceder a esta página";
}

?>