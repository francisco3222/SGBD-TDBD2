<?php
	require_once("custom/php/common.php");
	echo "<link rel='stylesheet' type='text/css' href='../css/ag.css'>";
    echo '<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@200&display=swap" rel="stylesheet">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Comfortaa&display=swap" rel="stylesheet">';
    echo "<div class=baseL>";
	if (!is_user_logged_in() || !current_user_can("manage_allowed_values"))
	{
		echo "Não tem autorização para aceder a esta página.";
	}
	elseif(!isset($_REQUEST['estado']) || $_REQUEST['estado'] == "")
	{

		$rowspanner = [];
		$lastID = null;
		$numIDs = 0;
		$itens = "SELECT id, item.name as nome
				FROM item
				ORDER BY id";
		$result_itens = mysqli_query($conexao, $itens);
		if (mysqli_num_rows($result_itens) === 0)
		{
			echo "Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.";
		}
		else
		{
			echo "<h3 class='animatedL'>Gestão de valores permitidos</h3>";
			echo "<table style='width:100%' class='outerTabelaL'>";
			echo "<thead>";
			echo "<tr class='tabelaL headerL'>
				<th>item</th>
				<th>id</th>
				<th>subitem</th>
				<th>id</th>
				<th>valores permitidos</th>
				<th>estado</th>
				<th>ação</thc
				</tr>";
			echo "</theads>";
			echo "<tbody>";
			foreach ($result_itens as $item_query)
			{
				$bigRow = 0;
				$smallRow = 0;
				//---------------Big Row Counter--------------
				$rowsCounter1 = "SELECT COUNT(*) as num
								FROM item
								JOIN subitem ON subitem.item_id = item.id
								LEFT JOIN subitem_allowed_value ON subitem_allowed_value.subitem_id = subitem.id
								WHERE item.id = ".$item_query['id']."
								";
				$rowResult1 = mysqli_query($conexao, $rowsCounter1);
				foreach($rowResult1 as $row1)
				{
					$bigRow = $row1['num'];
				}
				//--------------Small Row Counter-------------
				$rowsCounter2 = "SELECT COUNT(*) as num
								FROM item,
									subitem
								WHERE item.id = ".$item_query['id']." AND
									subitem.item_id = item.id";
				$rowResult2 = mysqli_query($conexao, $rowsCounter2);
				foreach($rowResult2 as $row2)
				{
					$smallRow = $row2['num'];
				}
				//--------------First Column----------------
				echo "<tr class='tabelaL'>";
				if($bigRow != 0)
				{
					echo '<td class="tabelaL" rowspan="' . $bigRow . '">' . $item_query['nome'] . '</td>';
				}
				elseif ($smallRow != 0 and $bigRow == 0)
				{
					echo '<td class="tabelaL" rowspan="' . $smallRow . '">' . $item_query['nome'] . '</td>';
				}
				elseif ($smallRow == 0 and $bigRow == 0)
				{
					echo '<td class="tabelaL">' . $item_query['nome'] . '</td>';
				}
				//--------------Continue Table----------------
				$subitens = "SELECT subitem.name as nome,
									subitem.id as id,
									subitem.item_id,
									item.name
							FROM subitem,
									item
							WHERE subitem.item_id = " . $item_query['id'] . "
							AND subitem.item_id = item.id
							ORDER BY item.id, subitem.id;";
				$results_subitems = mysqli_query($conexao, $subitens);
				if (mysqli_num_rows($results_subitems) >= 1)
				{
					foreach ($results_subitems as $subitem_query)
					{
						$tipoSubitens = "SELECT	subitem_allowed_value.id as id,
												subitem_allowed_value.value as value,
												subitem_allowed_value.state as state
										FROM subitem_allowed_value
										WHERE subitem_allowed_value.subitem_id = " . $subitem_query['id'] . "
										ORDER BY id;";
						$results_tipos = mysqli_query($conexao, $tipoSubitens);
						if (mysqli_num_rows($results_tipos) >= 1)
						{
							$firstSubitem = True;
							$subCounter = 0;
							$counter = 0;
							foreach ($results_tipos as $tipo_Query)
							{
								$subitemSpan = mysqli_num_rows($results_tipos);
								if ($counter == 0)
								{

									if ($subCounter == 0)
									{
										echo '<td class="tabelaL" rowspan="'.$subitemSpan.'">' . $subitem_query['id'] . '</td>';
										echo '<td class="tabelaL" rowspan="'.$subitemSpan.'"><a class="linkButtL" href="gestao-de-valores-permitidos?estado=introducao&subitem=' . $subitem_query['id'] . '">[' . $subitem_query['nome'] . ']</a></td>';
									}
									echo '<td class="tabelaL">' . $tipo_Query['id'] . '</td>';
									echo '<td class="tabelaL">' . $tipo_Query['value'] . '</td>';
									echo '<td class="tabelaL">' . $tipo_Query['state'] . '</td>';
									if ($tipo_Query['state'] == 'active')
									{
										echo '<td class="tabelaL"><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[editar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'&state=active">[desativar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[apagar]</a></td>';
									}
									else
									{
										echo '<td class="tabelaL"><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[editar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'&state=inactive">[desativar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[apagar]</a></td>';
									}
									echo '</tr>';
									$counter++;
									$subCounter++;
								}
								else
								{
									echo "<tr class='tabelaL'>";
									if ($subCounter == 0)
									{
										echo '<td class="tabelaL" rowspan="'.$subitemSpan.'">' . $subitem_query['id'] . '</td>';
										echo '<td class="tabelaL" rowspan="'.$subitemSpan.'"><a href="gestao-de-valores-permitidos?estado=introducao&subitem=' . $subitem_query['id'] . '">[' . $subitem_query['nome'] . ']</a></td>';
									}
									echo '<td class="tabelaL">' . $tipo_Query['id'] . '</td>';
									echo '<td class="tabelaL">' . $tipo_Query['value'] . '</td>';
									echo '<td class="tabelaL">' . $tipo_Query['state'] . '</td>';
									if ($tipo_Query['state'] == 'active')
									{
										echo '<td class="tabelaL"><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[editar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'&state=active">[desativar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[apagar]</a></td>';
									}
									else
									{
										echo '<td class="tabelaL"><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[editar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'&state=inactive">[desativar]</a><br><a class="linkButtL" href="edicao-de-dados?id='.$tipo_Query['id'].'">[apagar]</a></td>';
									}
									echo '</tr>';
									$counter++;
									$subCounter++;
								}
								if($subCounter == $subitemSpan)
								{

								}
								if($bigRow != 0)
								{
									if ($bigRow == $counter)
									{
										$counter = 0;
									}
								}
								else
								{
									if ($smallRow == $counter)
									{
										$counter = 0;
									}
								}
							}

						}
						else
						{
							echo '<td class="tabelaL">' . $subitem_query['id'] . '</td>';
							echo '<td class="tabelaL"><a class="linkButtL" href="gestao-de-valores-permitidos?estado=introducao&subitem=' . $subitem_query['id'] . '">[' . $subitem_query['nome'] . ']</a></td>';
							echo '<td class="tabelaL" colspan="4">Não há valores permitidos definidos</td>';
							echo '</tr>';
						}

					}
					}
				else
				{
					echo '<td class="tabelaL" colspan="6">Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.</td>';
					echo '</tr>';
				}
			}
			echo "</table>";
		}
		echo "</tbody>";
	}
	elseif($_REQUEST['estado'] == "introducao")
	{
		$_SESSION['subitem_id'] = $_REQUEST['subitem'];
		echo "<h3 class='titleL'>Gestão de valores permitidos - introdução</h3>";
        echo '<form class="centerL"  method = "post">';
		echo '<label class="formLabelL" for="name">Valor</label><br>';
        echo '<input class="inputL smallerL" type="text" name="valor">';
        echo '<input type="hidden" name="estado" value="inserir">';
		echo '<input type="hidden" name="subitem_id" value="'.$_SESSION['subitem_id'].'">';
        echo '<br>';
        echo '<br>';
        echo '<input type="submit" class="SubmitL" name="submitButton" value="Inserir valor permitido">';
		echo '</form>';
	}
	elseif($_REQUEST['estado'] == "inserir") {
		$errors = false;
		$valor = htmlspecialchars($_REQUEST['valor']);
		$subitemID = $_REQUEST['subitem_id'];
		if (empty($valor)) {
			echo "É necessário um valor.";
			$errors = True;
		}
		if (!$errors) {
			echo "<h3 class='titleL'>Gestão de valores permitidos - inserção</h3>";
			$query_insert = 'INSERT INTO subitem_allowed_value(subitem_id, value, state)
							VALUES (' . $subitemID . ', "' . $valor . '", "active");';
			$result_insert = mysqli_query($conexao, $query_insert);
			if ($result_insert) {
				echo '<div class="centerL">';
				echo "Inseriu os dados de novo valor permitido com sucesso.";
				echo '<form action="/sgbd/insercao-de-valores">';
				echo '<input class="SubmitL"  type="submit" value="Continuar">';
				echo '</form>';
				echo '</div>';
			} else {
				echo "Erro na query de inserção: " . mysqli_error($conexao);
			}
		}
	}
?>
