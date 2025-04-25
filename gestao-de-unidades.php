<?php

require_once('common.php');

$query_de_unit_type = "SELECT * FROM subitem_unit_type ORDER BY name ASC";
$result_de_unit_type = mysqli_query($conexao, $query_de_unit_type);

$mostra_tabela = true;

if (is_user_logged_in() && current_user_can('manage_unit_types')) // verifica se o utilizador está com sessão iniciada
{
    // Verifica se o formulário foi submetido
    if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'inserir') 
    {
        $nome = $_REQUEST['Uni'];
        
        // Verifica se o nome não está vazio após a limpeza
        if (!empty($nome)) {

            // Verifica se o nome já existe na tabela
            $query_verifica_nome = "SELECT COUNT(*) AS total FROM subitem_unit_type WHERE name = '$nome'";
            $result_verifica_nome = mysqli_query($conexao, $query_verifica_nome);
            $row_verifica_nome = mysqli_fetch_assoc($result_verifica_nome); // obtem o numero de linhas que tem o nome inserido

            if ($row_verifica_nome['total'] == 0) 
            {
                // Insere o novo tipo de unidade
                $query = "INSERT INTO subitem_unit_type (name) VALUES ('$nome')";
                $result = mysqli_query($conexao, $query);
                // Verifica se a query foi executada com sucesso
                if ($result) 
                {
                    $mostra_tabela = false;
                    // Obtém o ID do novo tipo de unidade
                    $new_unit_type_id = mysqli_insert_id($conexao);
                    // Mostra uma mensagem de sucesso
                    echo "Tipo de unidade inserido com sucesso";
                    echo "<br>";
                    echo "ID : " . $new_unit_type_id . "<br>";
                    echo "Nome : " . $nome;
                    echo 
                    "<br>
                    <form method='post' action=''>
                        <button type='submit' name='continuar'>Continuar</button>
                    </form>";
                } 
                else 
                {
                    $mostra_tabela = false;
                    echo "<br>";
                    echo "Erro: Não foi possível inserir o tipo de unidade.";
                    echo "ID : " . $new_unit_type_id . "<br>";
                    echo "Nome : " . $nome;
                }
            } 
            else 
            {
                $mostra_tabela = false;
                echo "Erro: O nome do tipo de unidade já existe.";
            }
        } 
        elseif (empty($nome))
        {
            $mostra_tabela = false;
            echo "Erro: O nome do tipo de unidade não pode estar vazio.";
        }
        elseif (is_numeric($nome))
        {
            $mostra_tabela = false;
            echo "Erro: O nome do tipo de unidade não pode ser um número.";

        }
    }

    if($mostra_tabela)
    {
        echo 
        "
            <head>
                <link rel='stylesheet'  type = 'text/css' href='../css/ag.css'>
            </head>
            <body>
            <h2 class = 'titulo' > Faça a gestão das Unidades aqui! </h2>
                <table>
                    <tr class = 'identificacao'>                               
                        <td  class = 'bordas'> id </td>
                        <td  class = 'bordas'> unidade </td>
                        <td  class = 'bordas'> subitem </td>
                        <td  class = 'bordas'> ação </td>
                    </tr>
        ";

        foreach($result_de_unit_type as $row_UnitType)
        {
            echo "<tr>";
            echo "<td  class = 'bordas'>" . $row_UnitType['id'] . "</td>";
            echo "<td  class = 'bordas'>" . $row_UnitType['name'] . "</td>";

            $subitems = [];

            // Consulta para obter os subitens relacionados ao tipo de unidade atual
            $query_de_subitem = "SELECT subitem.name AS subitem_name, item.name AS item_name 
                                    FROM subitem 
                                    JOIN item ON subitem.item_id = item.id 
                                    WHERE subitem.unit_type_id = " . $row_UnitType['id'] . " 
                                    ORDER BY subitem.name ASC";
            $result_do_subitem = mysqli_query($conexao, $query_de_subitem);
        
            if ($result_do_subitem && mysqli_num_rows($result_do_subitem) > 0) 
            {
                while ($row_subitem = mysqli_fetch_assoc($result_do_subitem))
                {
                    $subitems[] = $row_subitem['subitem_name'] . " (" . $row_subitem['item_name'] . ")";
                }
            }
            else
            {
                $subitems[] = "Não existem subitens para este tipo de unidade";
            }

            // Mostra os subitens relacionados ao tipo de unidade atual
            // para que funciona a implode -> ver 
            echo "<td  class = 'bordas active'>" . implode(", ", $subitems) . "</td>";

            echo 
                "
                <td  class = 'bordas'> 
                    <a href='editar-subitem.php?id=" . $row_UnitType['id'] . "'> Editar </a>
                    <a href='edicao-de-dados.php?id=" . $row_UnitType['id'] . "'> Apagar </a>
                </td>";

            echo "</tr>";
        }
            echo
                "
                </table>
        
                <h3><strong><i> Gestão de unidades - Introdução</h3>

                <form method = 'POST' action =''>
                    <label for='Unidade' class='required-field' required>Nome: </label>
                    <input type='text' id='Uni' name='Uni' required>
                    <br>
                    <input type='hidden' name='estado' value='inserir'>
                    <br>
                    <button type='submit'>Inserir tipo de unidade</button>
                </form>

            </body>
            ";
    }

}
else 
{
    echo "Não tem permissões para aceder a esta página";
}

?>