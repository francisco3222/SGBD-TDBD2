<?php
    require_once("custom/php/common.php");
	echo "<link rel='stylesheet' type='text/css' href='../css/ag.css'>";
    echo '<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@200&display=swap" rel="stylesheet">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Comfortaa&display=swap" rel="stylesheet">';
    echo "<div class=baseL>";
    if (!is_user_logged_in() || !current_user_can("insert_values"))
    {
        echo "Não tem autorização para aceder a esta página.";
    }
    elseif(!isset($_REQUEST['estado']) || $_REQUEST['estado'] == "")
    {
        echo '<h3 class="animatedL">Inserção de valores - criança - procurar</h3>';
        echo '<form class="centerL" method = "post">';
        echo '<label for="childName" class="formLabelL">Nome:</label>';
        echo '<input type="text" class="inputL" name="childName">';
        echo '<label for="dateBirth" class="formLabelL">Data de Nascimento:</label>';
        echo '<input type="text" class="inputL" name="dateBirth">';
        echo '<input type="hidden" name="estado" value="escolher_crianca">';
        echo '<br>';
        echo '<br>';
        echo '<input type="submit" class="SubmitL" name="submitButton" value="Submeter">';
        echo '</form>';
    }
    elseif($_REQUEST['estado'] == "escolher_crianca")
    {
        echo '<h3 class="titleL">Inserção de valores - criança - escolher</h3>';
		$errors = false;
		$name = htmlspecialchars($_REQUEST['childName']);
		$birth = htmlspecialchars($_REQUEST['dateBirth']);

		if(!empty($name) && !preg_match("/^[\p{L}\s]*$/u", $name))
		{
            echo "O nome contém caracteres inválidos<br>";
			$errors = true;
		}
		if (!empty($birth) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $birth))
		{
			echo "A data deve ser do formato AAAA-MM-DD.<br>";
			$errors = true;
		}
        if(!$errors) {
            echo "<div class='ChildQueryL'>";
            if (empty($name) && empty($birth))
            {
                $children = "SELECT child.name as nome,
                                child.birth_date as nascimento,
                                child.id as id  
                            FROM child;";
                $resultChildren = mysqli_query($conexao, $children);
                if (mysqli_num_rows($resultChildren) >= 1)
                {
                    echo "<div class='containerL'>";
                    $NResultados = mysqli_num_rows($resultChildren);
                    $forEachColumn = 0;
                    $breakColumns = False;
                    echo "<div class='columnL centerL'>";
                    foreach ($resultChildren as $child)
                    {
                        if ($forEachColumn >= ($NResultados/2) && !$breakColumns)
                        {
                            echo "</div>";
                            echo "<div class='columnL centerL'>";
                            $breakColumns = True;
                        }
                        $id = $child['id'];
                        echo '[<a href=insercao-de-valores?estado=escolher_item&crianca=' . $id . '>' . $child['nome'] . '</a>]';
                        echo ' (' . $child['nascimento'] . ')';
                        echo '<br>';

                        $forEachColumn++;
                    }
                    echo "</div>";
                    echo "</div>";
                }
                else
                {
                    echo "Nenhuma criança encontrada.";
                }
            }
            elseif (empty($birth))
            {
                $children = "SELECT child.name as nome,
                                child.birth_date as nascimento,
                                child.id as id
                            FROM child
                            WHERE child.name LIKE '%" . $name . "%'";
                $resultChildren = mysqli_query($conexao, $children);
                if (mysqli_num_rows($resultChildren) >= 1)
                {
                    foreach ($resultChildren as $child)
                    {
                        $id = $child['id'];
                        echo '[<a href=insercao-de-valores?estado=escolher_item&crianca=' . $id . '>' . $child['nome'] . '</a>]';
                        echo ' (' . $child['nascimento'] . ')';
                        echo '<br>';
                    }
                }
                else
                {
                    echo "Nenhuma criança encontrada.";
                }
            }
            elseif (empty($name))
            {
                $children = "SELECT child.name as nome,
                                child.birth_date as nascimento,
                                child.id as id
                            FROM child
                            WHERE child.birth_date = '" . $birth . "';";
                $resultChildren = mysqli_query($conexao, $children);
                if (mysqli_num_rows($resultChildren) >= 1)
                {
                    foreach ($resultChildren as $child)
                    {
                        $id = $child['id'];
                        echo '[<a href=insercao-de-valores?estado=escolher_item&crianca=' . $id . '>' . $child['nome'] . '</a>]';
                        echo ' (' . $birth . ')';
                        echo '<br>';
                    }
                }
                else
                {
                    echo "Nenhuma criança encontrada.";
                }
            }
            else
            {
                $children = "SELECT child.name as nome,
                                child.birth_date as nascimento,
                                child.id as id
                            FROM child
                            WHERE child.name = '" . $name . "' AND
                            child.birth_date = '" . $birth . "';";
                $resultChildren = mysqli_query($conexao, $children);
                if (mysqli_num_rows($resultChildren) >= 1)
                {
                    foreach ($resultChildren as $child)
                    {
                        $id = $child['id'];
                        echo '[<a href=insercao-de-valores?estado=escolher_item&crianca=' . $id . '>' . $child['nome'] . '</a>]';
                        echo ' (' . $birth . ')';
                        echo '<br>';
                    }
                }
                else
                {
                    echo "Nenhuma criança encontrada.";
                }
            }
            echo "</div>";
        }
    }
    elseif($_REQUEST['estado'] == "escolher_item")
    {
        $_SESSION['child_id'] = $_REQUEST['crianca'];
        echo '<h3 class="titleL">Inserção de valores - escolher item</h3>';
        $types = "SELECT id,
                        name
                    FROM item_type;";
        $typeResult = mysqli_query($conexao, $types);
        echo "<div class='containerL'>";
        $NResultados = mysqli_num_rows($typeResult);
        $forEachColumn = 0;
        $breakColumns = False;
        echo "<div class='columnL'>";
        foreach($typeResult as $type)
        {
            if ($forEachColumn >= ($NResultados/2) && !$breakColumns)
            {
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
            if(mysqli_num_rows($itemResult) >= 1)
            {
                echo '<ul>';
                echo '<li>' . $type['name'] . '</li>';
                foreach ($itemResult as $item)
                {
                    
                    $subs = "SELECT item_id
                            FROM subitem   
                            WHERE item_id = ". $item['id']. ";";
                    $subResult = mysqli_query($conexao, $subs);
                    if(mysqli_num_rows($subResult) >= 1)
                    {
                        echo '<ul>';
                        echo '<li>[<a href=insercao-de-valores?estado=introducao&item='. $item['id']. '&childid='.$_SESSION['child_id'].'>'. $item['name']. '</a>]</li>';
                        echo '</ul>';
                    }
                }
                echo '</ul>';
            }
        }
        echo "</div>";
        echo "</div>";
    }
    elseif($_REQUEST['estado'] == "introducao")
    {
        $child_id = $_REQUEST['childid'];
        $_SESSION['item_id'] = $_REQUEST['item'];
        $buscaNome = "SELECT name as nome,
                        item_type_id as tid
                    FROM item
                    WHERE id = " . $_SESSION['item_id'].";";
        $resultNome = mysqli_query($conexao, $buscaNome);
        foreach($resultNome as $itemNome)
        {
            $_SESSION['item_name'] = $itemNome['nome'];
            $_SESSION['item_type_id'] = $itemNome['tid'];
        }
        echo "<h3 class='titleL'>Inserção de valores - " . $_SESSION['item_name'] . "</h3>";
        $subitens = "SELECT subitem.name as subName,
                        subitem.value_type as subValue,
                        subitem.form_field_name as formValue,
                        subitem.form_field_type as formType,
                        subitem.id as id,
                        subitem.form_field_name as formName,
                        subitem.unit_type_id as unitId
                    FROM subitem
                    WHERE subitem.item_id = " . $_SESSION['item_id'] . " AND
                    subitem.state = 'active';";
        $subitemResult = mysqli_query($conexao, $subitens);
        if(mysqli_num_rows($subitemResult) >= 1)
        {
            echo '<form class="centerL" method = "post" name=item_type_' . $_SESSION['item_id'] . '_item_' . $_SESSION['item_id'] . '>';
            foreach($subitemResult as $subs)
            {
                $unit = "";
                if ($subs['unitId'] != NULL)
                {
                    $unitQuery = "SELECT subitem_unit_type.name as unidade
                                    FROM subitem_unit_type
                                    WHERE subitem_unit_type.id = " . $subs['unitId'] . ";";
                    $unitResult = mysqli_query($conexao, $unitQuery);
                    foreach($unitResult as $unitQ)
                    $unit = $unitQ['unidade'];
                }
                if($subs['formType'] == "checkbox" or $subs['formType'] == "radio" or $subs['formType'] == "selectbox")
                {
                    $subItensValues = "SELECT subitem_allowed_value.value as value
                                        FROM subitem_allowed_value
                                        WHERE subitem_allowed_value.subitem_id = ".$subs['id']."
                                        ORDER BY subitem_allowed_value.id;";
                    $subValueResult = mysqli_query($conexao, $subItensValues);
                    if(mysqli_num_rows($subValueResult) >= 1)
                    {
                        if ($subs['formType'] == "checkbox")
                        {
                            echo '<label class="formLabelL" for="'.$subs['subName'].'">'.$subs['subName'].'</label><br>';
                            echo '<div class="divShadow">';
                            foreach($subValueResult as $subVal)
                            {
                                echo '<input class="inputL" style="box-shadow: 0px 0px 0px black" type="' . $subs['formType'] . '" id="' . $subVal['value'] . '" name="' . $subs['formName'] . '[]" value="'.$subVal['value'].'">';
                                echo '<label class="formLabelL" for="' . $subVal['value'] . '">'.$subVal['value'].'</label>  ';
                                if ($unit != "")
                                {
                                    echo '<span class="formLabelL">' . $unit . '</span>';
                                }
                            }
                            echo '</div>';
                            echo "<br>";
                        }
                        elseif ($subs['formType'] == "radio")
                        {
                            echo '<label class="formLabelL" for="'.$subs['subName'].'">'.$subs['subName'].'</label><br>';
                            echo '<div class="divShadow">';
                            foreach($subValueResult as $subVal)
                            {
                                echo '<input class="inputL" style="box-shadow: 0px 0px 0px black" type="' . $subs['formType'] . '" id="' . $subVal['value'] . '" name="' . $subs['formName'] . '" value="'.$subVal['value'].'">';
                                echo '<label class="formLabelL" for="' . $subVal['value'] . '">'.$subVal['value'].'</label>  ';
                                if ($unit != "")
                                {
                                    echo '<span class="formLabelL">' . $unit . '</span>';
                                }
                            }
                            echo '</div>';
                            echo "<br>";
                        }
                        elseif ($subs['formType'] == "selectbox")
                        {
                            echo '<label class="formLabelL" for="'.$subs['subName'].'">'.$subs['subName']."</label>";
                            echo "<br>";
                            if ($unit != "")
                            {
                                echo '<span class="formLabelL">' . $unit . '</span>';
                            }
                            echo '<select class="selectL" name="'.$subs['formName'].'" id="'.$subs['subName'].'">';
                            foreach($subValueResult as $subVal)
                            {
                                echo '<option  class="selectL" value="'.$subVal['value'].'">'.$subVal['value'].'</option>';
                            }
                            echo '</select>';
                            echo '<br>';
                        }
                    }
                    else
                    {
                        echo "Erro na query de valores: " . $subs['id'];
                    }
                }
                elseif ($subs['formType'] == "textbox")
                {
                    echo '<label class="formLabelL" for="'.$subs['subName'].'">'.$subs['subName'].'</label>';
                    echo "<br>";
                    echo '<textarea class="textboxL" name="'.$subs['formName'].'" rows="4" cols="50">';
                    echo '</textarea>';
                    if ($unit != "")
                    {
                        echo '<span class="formLabelL">' . $unit . '</span>';
                    }
                    echo '<br>';
                }
                else
                {
                    echo '<label class="formLabelL" for="'.$subs['subName'].'">'.$subs['subName']."</label>";
                    echo "<br>";
                    echo '<input class="inputL smallerL" type="' . $subs['formType'] . '" name="' . $subs['formName'] . '" placeholder="inserir dados...">';
                    if ($unit != "")
                    {
                        echo '<span class="formLabelL">' . $unit . '</span>';
                    }
                    echo '<br>';
                }
            }
            echo '<br>';
            echo '<input type="submit" class="SubmitL" name="submitButton" value="Submeter">';
            echo '<input type="hidden" name="estado" value= "validar">';
            echo '<input type="hidden" name="item_id" value= "'.$_SESSION['item_id'].'">';
            echo '<input type="hidden" name="child_id" value= "'.$child_id.'">';
            echo '<input type="hidden" name="item_name" value= "'.$_SESSION['item_name'].'">';
            echo '</form>';
        }
        else
        {
            echo "Erro na query de obtenção de subitens";
        }
    }

    elseif($_REQUEST['estado'] == "validar")
    {
        $id = $_POST['item_id'];
        $childId = $_POST['child_id'];

        $itemName = $_POST['item_name'];
        $subsForm = "SELECT subitem.form_field_name as formName
                    FROM subitem
                    WHERE subitem.item_id = " . $id . " AND
                    subitem.state = 'active';";
        $resultForm = mysqli_query($conexao, $subsForm);
        $formData = [];
        $errors = False;
        if (!$errors)
		{
            echo "<h3 class='titleL'>Inserção de valores - $itemName - validar</h3>";
            foreach ($resultForm as $subs)
            {
                if (is_array($_REQUEST[$subs['formName']]))
                {
                    foreach ($_REQUEST[$subs['formName']] as $checkboxVal)
                    {
                        $formData[] = [[$subs['formName']], $checkboxVal];
                    }
                }
                else
                {
                    $formData[] = [[$subs['formName']],$_REQUEST[$subs['formName']]];
                }
            }
            echo '<ul>';
        foreach ($formData as $forms)
        {
            foreach ($resultForm as $subs)
            {
                $formName = $forms[0][0];
                $value = $forms[1];
                if (empty($_REQUEST[$subs['formName']]))
                {
                    echo "É necessário um valor para " . $subs['formName'];
                    $errors = True;
                }
            }
            echo '<li>'. $formName . ': ' . $value . '</li>';
        }
            echo '</ul>';
            echo "<i>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</i>";
            echo '<form method="POST" class="centerL" action="?estado=inserir&item='.$id.'">';
            foreach ($formData as $form)
            {
                $formName = $form[0][0];   // This is $subs['formName']
                $value = $form[1];
                echo '<input type="hidden" name="'.$formName.'" value="'.$value.'">';
            }
            echo '<input type="hidden" name="item_id" value= "'.$id.'">';
            echo '<input type="hidden" name="child_id" value= "'.$childId.'">';
            echo '<input type="hidden" name="estado" value= "inserir">';
            echo '<input type="hidden" name="item_name" value="'.$itemName.'">';
            echo '<br>';
            echo '<input type="submit" class="SubmitL" name="submitButton" value="Submeter">';
            echo '</form>';
        //}
        }
        else {
            echo "Por favor corrija o formulário na página anterior antes de prosseguir.";
        }
    }
    elseif($_REQUEST['estado'] == "inserir")
    {
        $id = $_REQUEST['item_id'];
        $childId = $_POST['child_id'];
        $subsForm = "SELECT subitem.form_field_name as formName,
                            subitem.id as id
                    FROM subitem
                    WHERE subitem.item_id = " . $id . " AND
                    subitem.state = 'active';";
        $resultForm = mysqli_query($conexao, $subsForm);
        $formData = [];
        foreach ($resultForm as $subs)
        {
            if (is_array($_REQUEST[$subs['formName']]))
            {
                foreach ($_REQUEST[$subs['formName']] as $checkboxVal)
                {
                    $formData[] = [[$subs['formName']], $checkboxVal, $subs['id']];
                }
            }
            else
            {
                $formData[] = [[$subs['formName']],$_REQUEST[$subs['formName']], $subs['id']];
            }
        }
        $error = False;
        foreach ($formData as $form)
        {
            $formName = $form[0][0];
            $value = $form[1];
            $subId = $form[2];
            $insertQuery = "INSERT INTO `value`(child_id, subitem_id, value, date, time)
                            VALUE ($childId, $subId, '$value', '".date("Y-m-d")."', '".date("H:i:s")."')";
            $result_insert = mysqli_query($conexao,$insertQuery);
			if (!$result_insert)
			{
                $error = True;
            }
        }
        if (!$error)
        {
            echo "Inseriu o(s) valor(es) com sucesso.";
            echo "<br>";
            echo "Clique em <i>Voltar</i> para voltar ao início da inserção de valores ou em <i>Escolher item</i> se quiser continuar a inserir valores associados a esta criança";
            echo "<div class='dividedBoxL'>";
                echo "<div class='divideColL'>";
                    echo "<a class='linkButtL' href='insercao-de-valores'>Voltar</a>";
                echo "</div>";
                echo "<div class='divideColL'>";
                    echo '<a class="linkButtL" href=insercao-de-valores?estado=escolher_item&crianca=' . $childId . '>Escolher Item</a>';

                echo "</div>";
            echo "</div>";
        }
        else
        {
            echo "Erro na query de inserção: " . mysqli_error($conexao);
        }
    }
    echo "</div>";
?>
