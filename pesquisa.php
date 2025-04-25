<?php

require_once('common.php');

$query_do_item = "SELECT * FROM item ORDER BY name ASC";
$result_do_item = mysqli_query($conexao, $query_do_item);

$query_do_tipo_de_item= "SELECT * FROM item_type ORDER BY name ASC";
$result_do_tipo_de_item= mysqli_query($conexao, $query_do_tipo_de_item);

$estado = $_REQUEST['estado'];

if (is_user_logged_in() && current_user_can('search')) // verifica se o utilizador está com sessão iniciada
{   
    echo
    " <head>
        <link rel='stylesheet'  type = 'text/css' href='../css/ag.css'>
    </head>";

    if ($estado == '') {
        echo "<h3 class = 'tituloPesquisa'>Pesquisa - escolher item</h3>";

        echo "<h5 class ='tituloOr'>Tipo de item</h5>";

        while($row_tipo_item = mysqli_fetch_assoc($result_do_tipo_de_item))
        {
            echo "<ul class='pontos'><li><a href='pesquisa?estado=escolha&tipo_item_id={$row_tipo_item['id']}'>{$row_tipo_item['name']}</a></li></ul>";
        }
        
        echo "<h5 class ='tituloOr'>Itens</h5>";
        
        while ($row_item = mysqli_fetch_assoc($result_do_item)) {
            echo "<ul class='pontos'><li><a href='pesquisa?estado=escolha&item_id={$row_item['id']}'>{$row_item['name']}</a></li></ul>";
        }
    }


    if ($estado == 'escolha') 
    {
        $item_id = $_REQUEST['item_id'];
        $_SESSION['item_id'] = $item_id;
    
        // Obter atributos da tabela child
        $query_atributos = "SELECT * FROM child";
        $result_atributos = mysqli_query($conexao, $query_atributos);
    
        // Obter subitens do item escolhido
        $query_subitens = "SELECT * FROM subitem WHERE item_id = $item_id ORDER BY name ASC";
        $result_subitens = mysqli_query($conexao, $query_subitens);
        
        echo "<form method='POST' action='pesquisa.php'>";
        echo "<input type='hidden' name='estado' value='escolher_filtros'>";
        echo "<table>";
        echo "<tr>
                <th>Atributo</th>
                <th>Obter</th>
                <th>Filtro</th>
             </tr>";
    
            if (mysqli_num_rows($result_atributos) == 0) 
            {
                echo 
                    "
                    <tr>
                        <td colspan='3'> Não existem atributos </td>
                    </tr>
                    ";
            } 
            else 
            {
                while ($row_atributo = mysqli_fetch_assoc($result_atributos)) {
                    echo "<tr>";
                    echo "<td> [ " . $row_atributo['name'] . " ] </td>";
                    echo "<td><input type='checkbox' name='atributos[obter][]' value='{$row_atributo['name']}'></td>";
                    echo "<td><input type='checkbox' name='atributos[filtro][]' value='{$row_atributo['name']}'></td>";
                    echo "</tr>";
                }
            }
    
        echo
         "<tr>
              <th>Subitem</th>
              <th>Obter</th>
              <th>Filtro</th>
         </tr>";
    
            if (mysqli_num_rows($result_subitens) == 0) 
            {
                 echo 
                    "
                    <tr>
                        <td colspan='3'> Não existem atributos </td>
                    </tr>
                    ";
            } 
            else 
            {
            {
                while ($row_subitem = mysqli_fetch_assoc($result_subitens))
                {
                    echo "<tr>";
                    echo "<td>{$row_subitem['name']}</td>";
                    echo "<td><input type='checkbox' name='subitens[obter][]' value='{$row_subitem['id']}'></td>";
                    echo "<td><input type='checkbox' name='subitens[filtro][]' value='{$row_subitem['id']}'></td>";
                    echo "</tr>";
                }
            }
        }
    
        echo "</table>";
        echo "<button type='submit' id = 'avançarID'>Avançar</button>";
        echo "</form>";

        var_dump($_SERVER['REQUEST_METHOD']);
        var_dump($_POST); 
        var_dump($_SESSION);
        
        var_dump($_POST['estado']);

        if ($_POST['estado'] == 'escolher_filtros') {
            $atributosObter = $_POST['atributos']['obter'] ?? [];
            $atributosFiltro = $_POST['atributos']['filtro'] ?? [];
            $subitensObter = $_POST['subitens']['obter'] ?? [];
            $subitensFiltro = $_POST['subitens']['filtro'] ?? [];
        
            if (empty($atributosObter) && empty($atributosFiltro) && empty($subitensObter) && empty($subitensFiltro)) {
                echo "<p>Por favor, selecione pelo menos um atributo ou subitem antes de avançar.</p>";
            } 
            else 
            {
                $_SESSION['atributos_obter'] = $atributosObter;
                $_SESSION['atributos_filtro'] = $atributosFiltro;
                $_SESSION['subitens_obter'] = $subitensObter;
                $_SESSION['subitens_filtro'] = $subitensFiltro;
            }
        }
    
    }
    if ($estado == 'escolher_filtros') 
    {
        error_log("Estado reconhecido: escolher_filtros");

        $_SESSION['atributos_obter'] = $_POST['atributos']['obter'];
        $_SESSION['atributos_filtro'] = $_POST['atributos']['filtro'];
        $_SESSION['subitens_obter'] = $_POST['subitens']['obter'];
        $_SESSION['subitens_filtro'] = $_POST['subitens']['filtro'];

        echo "<form action='pesquisa.php' method='POST'>";
        echo "<input type='hidden' name='estado' value='execucao'>";
        
        foreach ($_SESSION['atributos_filtro'] as $atributo) 
        {
            echo "<label>{$atributo}</label>";
            echo "<select name='operador[{$atributo}]'>";
            echo "<option value='>'>></option>";
            echo "<option value='>='>>=</option>";
            echo "<option value='='>=</option>";
            echo "<option value='<'><</option>";
            echo "<option value='<='><=</option>";
            echo "<option value='!='>!=</option>";
            echo "<option value='LIKE'>LIKE</option>";
            echo "</select>";
            echo "<input type='text' name='valor[{$atributo}]'>";
            echo "<br>";
        }


    }
    if($estado == 'execucao')
    {
        
    }

}
else
{
    echo "Não tem permissões para aceder a esta página";
}

?>
