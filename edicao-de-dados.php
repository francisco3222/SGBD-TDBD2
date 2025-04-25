<?php
require_once('common.php');
echo "<link rel='stylesheet' type='text/css' href='../css/ag.css' />";
const SECRET_KEY = 'tenta_adivinhar';

// Verificação do utilizador, caso ele esteja logado e possua a capability de 'manage-records'
if (!is_user_logged_in())
{
	echo "<p>Não tem autorização para aceder a esta página.</p>";
	return;
}

// Verificação se estamos na página correta
elseif (is_page('edicao-de-dados'))
{
	exibir_conteudo_gestao_de_dados();  // Passando os parâmetros corretos para a função
}
// Caso contrário
else
{
	echo "<br>Esta não é a página de Gestão de Itens!";
}

function exibir_conteudo_gestao_de_dados(): void
{
	$estado = $_REQUEST['estado'] ?? null;
	$token_recebido = $_REQUEST['token'] ?? null;
	$id = $_GET['id']?? null;
	$data = $_REQUEST['data'] ?? null;
	$hora = $_REQUEST['hora'] ?? null;
	$token_esperado = hash_hmac('sha256', $id . 'editar', SECRET_KEY) ?? null;
	if ($id === false || $id <= 0)
	{
		echo "<br>";
		die('ID inválido!');
	}
	elseif (!hash_equals($token_esperado, $token_recebido))
	{
		die('Token inválido! Alteração de URL detectada.');
	}
	elseif($estado === 'editar_registo')
	{
		editar_registo($id, $data,$hora);
		echo "<br><br>";
		get_footer();
	}
	elseif ( $estado === 'editar_itens' )
	{
		formulario_edicao_itens( $id );  // Edita os itens caso o estado seja 'editar_itens'
		echo "<br><br>";
		get_footer();
	} // Caso o estado 'atualizar' seja acionado via POST
	elseif ( $estado === 'atualizar' )
	{
		atualizar_itens( $id );  // Atualiza os itens se o estado for 'atualizar'
		echo "<br><br>";
		get_footer();
	}
	elseif ($estado=== 'apagar')
	{
		apagar_item($id);
		echo "<br><br>";
		get_footer();
	}
	elseif ($estado=== 'apagar_crianca')
	{
		apagar_registo_da_crianca($id);
		echo "<br><br>";
		get_footer();
	}
	elseif ($estado=== 'apagar_registo')
	{
		apagar_registo($id,$data,$hora);
		echo "<br><br>";
		get_footer();
	}
	elseif ($estado === 'mudar_estado')
	{
		mudarestado($id);
		echo "<br><br>";
		get_footer();
	}
	else
	{
		echo "<p class='erro_ao_inserir_items_registos'>
				Acesse esta página quando tiver a editar algum elemento de alguma página PHP!
			  </p>";
		echo "<br><br>";
		get_footer();
	}
}

function editar_registo($id,$data,$hora): void
{
	$conexao = conexao_base_de_dados();
	try
	{
		echo '
		  <div class="titulos_da_gestão_de_items_registos">
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Registos - Editar Registo</h3>
		  </div>';
		$editar_registo = "SELECT * 
						   FROM value
						   WHERE value.child_id = '$id'
								AND value.date = '$data'
								AND value.time = '$hora'
								";
		$resultado_editar_registo = mysqli_query($conexao, $editar_registo);


	}
	catch (Exception $e)
	{
		echo "<br>
				<p class='erro_ao_inserir_items_registos'>Erro: " . $e->getMessage() . "</p>
			  <br>";
	}

}


function apagar_registo($id,$data,$hora): void
{
	$conexao =  conexao_base_de_dados();
	try
	{
		echo '
		  <div class="titulos_da_gestão_de_items_registos">
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Registos - Apagar Registo</h3>
		  </div>';
		$apagar_registo = "DELETE FROM value
						   WHERE value.child_id = '$id'
								AND value.date = '$data'
								AND value.time = '$hora'
								";
		$resultado = mysqli_query($conexao, $apagar_registo);
		if ($resultado)
		{
			echo "<br><br>";
			echo "<div id='inserido_item_com_sucesso'>
					<p>
					  O Registo de ID <strong> $id </strong> foi Apagado com Sucesso! <br>
					  Caso queria voltar para a página de Gestão de Registos, clique no Botão Abaixo.
					</p>";

			echo "</div>
				  <br>";
			echo "<form action='/sgbd/gestao-de-registos'>";
			echo "<input type='submit' value='Gestão de Registos'>";
			echo "</form>";
		}
		else
		{
			throw new Exception("Erro ao apagar o Registo: " . mysqli_error($conexao));
		}
	}
	catch ( Exception $e)
	{
		echo "<br>
				<p class='erro_ao_inserir_items_registos'>Erro: " . $e->getMessage() . "</p>
			  <br>";
	}
}

function apagar_registo_da_crianca($id): void
{
	$conexao = conexao_base_de_dados();
	try
	{
		echo '
		  <div class="titulos_da_gestão_de_items_registos">
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Registos - Apagar Criança</h3>
		  </div>';
		$query_apagar_registo = "DELETE FROM child 
							  WHERE id = $id";
		// Executa a query na base de dados.
		$resultado = mysqli_query($conexao, $query_apagar_registo);
		// Verifica se a query foi executada com sucesso.
		if ($resultado)
		{
			echo "<br><br>";
			echo "<div id='inserido_item_com_sucesso'>
					<p>
					  A Criança de ID <strong> $id </strong> foi Apagado com Sucesso! <br>
					  Caso queria voltar para a página de Gestão de Registos, clique no Botão Abaixo.
					</p>";

			echo "</div>
				  <br>";
			echo "<form action='/sgbd/gestao-de-registos'>";
			echo "<input type='submit' value='Gestão de Registos'>";
			echo "</form>";
		}
		else
		{
			throw new Exception("Erro ao apagar o Registo: " . mysqli_error($conexao));
		}
	}
	catch ( Exception $e )
	{
		echo "<br>
				<p class='erro_ao_inserir_items_registos'>Erro: " . $e->getMessage() . "</p>
			  <br>";
	}
}

function mudarestado($id):void
{
	$conexao = conexao_base_de_dados();
	try
	{
		echo '
		  <div class="titulos_da_gestão_de_items_registos">
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Itens - Mudança de Estado</h3>
		  </div>
		  <br>';

		$query_estado_atual = "SELECT state
								FROM item
								WHERE id = $id";

		$resultado_estado_atual = mysqli_query( $conexao, $query_estado_atual );
		if ( mysqli_num_rows( $resultado_estado_atual ) <= 0 )
		{
			echo "<p class='erro_ao_inserir_items_registos'>ID não encontrado.</p>";
			echo "<br>
				  <br>";
			echo "<form action='' method='POST'>";
			echo "<input type='hidden' name='estado' value=''> ";
			// Botão para voltar para a página gestao de Iten
			echo "<input type='submit' value='Voltar Atrás'>";
			echo "</form>";
		}
		else
		{
			$state = mysqli_fetch_assoc( $resultado_estado_atual );
			$state_atual = $state['state'];
			if ( $state_atual == 'active' )
			{
				$novo_estado = 'inactive';
			}
			elseif ( $state_atual == 'inactive' )
			{
				$novo_estado = 'active';
			}
			else
			{
				throw new Exception("Erro ao alterar o estado do Item: " . mysqli_error($conexao));
			}

			$query_mudar_estado     = "UPDATE item 
								   		SET state = '$novo_estado' 
								   		WHERE id = $id";
			$resultado_mudar_estado = mysqli_query( $conexao, $query_mudar_estado );

			if ( $resultado_mudar_estado )
			{
				echo "<div id='inserido_item_com_sucesso'>
						<p>
						  O Estado do Item de ID <strong> $id </strong> foi Alterado com Sucesso! <br>
						  Caso queria voltar para a página de Gestão de Itens, clique no Botão Abaixo.
						</p>";
				echo "</div>
					  <br>";
				echo "<form action='/sgbd/gestao-de-itens'>";
				echo "<input type='submit' value='Voltar Atrás'>";
				echo "</form>";
			}
			else
			{
				throw new Exception("Erro ao alterar o estado do Item! " . mysqli_error($conexao));
			}
		}
	}
	catch ( Exception $e )
	{
		echo "<br>
				<p class='erro_ao_inserir_items_registos'>Erro: " . $e->getMessage() . "</p>
			  <br>";
	}
}
function apagar_item($id): void
{
	$conexao = conexao_base_de_dados();
	try
	{
		echo '
		  <div class="titulos_da_gestão_de_items_registos">
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Itens - Apagar Item</h3>
		  </div>';
		//ID recebido.
		//Query necessária para apagar o item.
		$query_apagar_item = "DELETE FROM item 
							  WHERE id = $id";
		// Executa a query na base de dados.
		$resultado = mysqli_query($conexao, $query_apagar_item);
		// Verifica se a query foi executada com sucesso.
		if ($resultado)
		{
			echo "<br><br>";
			echo "<div id='inserido_item_com_sucesso'>
					<p>
					  O Item de ID <strong> $id </strong> foi Apagado com Sucesso! <br>
					  Caso queria voltar para a página de Gestão de Itens, clique no Botão Abaixo.
					</p>";

			echo "</div>
				  <br>";
			echo "<form action='/sgbd/gestao-de-itens'>";
			echo "<input type='submit' value='Voltar Atrás'>";
			echo "</form>";
		}
		else
		{
			throw new Exception("Erro ao apagar o Item: " . mysqli_error($conexao));
		}
	}
	catch ( Exception $e )
	{
		echo "<br>
				<p class='erro_ao_inserir_items_registos'>Erro: " . $e->getMessage() . "</p>
			  <br>";
	}
}
function atualizar_itens($id): void
{
	$conexao = conexao_base_de_dados();

	// Verificar se o formulário foi submetido
	if (isset($_POST['atualizar'])) {
		// Escapando os dados para prevenir SQL Injection
		$nome = $_POST['nome'] ?? '';
		$estado = $_POST['estado_item'] ?? '';
		$item_type_id = $_POST['tipo'] ?? '';

		try {
			echo '
            <div class="titulos_da_gestão_de_items_registos">
                <h3 class="h3_titulos_gestão_de_items_registos">Edição de Dados - Atualização</h3>
            </div>';

			// Atualiza o item na base de dados
			$query_update = "UPDATE item SET name = '$nome', state = '$estado', item_type_id = '$item_type_id' WHERE id = $id";
			$resultado    = mysqli_query( $conexao, $query_update );

			if ($resultado) {
				echo "<br>
                    <div id='inserido_item_com_sucesso'>
                        <p>
                          O Estado do Item de ID <strong> $id </strong> foi Editado com Sucesso! <br>
                          Caso queira voltar para a página de Gestão de Itens, clique no Botão Abaixo.
                        </p>
                    </div>
                    <br>";
				echo "<form action='/sgbd/gestao-de-itens'>";
				echo "<input type='submit' value='Voltar Atrás'>";
				echo "</form>";
			}
			else
			{
				throw new Exception("Erro ao atualizar o Estado do Item de ID $id.");
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}
}

function formulario_edicao_itens($id): void
{
	$conexao = conexao_base_de_dados();

	// Recupera os dados do item
	$query_item = "SELECT * FROM item WHERE id=$id";
	$resultado_item = mysqli_query($conexao, $query_item);
	$item = mysqli_fetch_assoc($resultado_item);

	echo '
        <div class="titulos_da_gestão_de_items_registos">
            <h3 class="h3_titulos_gestão_de_items_registos">Edição de Dados - Atualização</h3>
        </div>';

	echo "<p class='introdução_itens_registos_formulario'>
            Introduza no formulário abaixo os dados necessários para a atualização do item de id <strong>" . $id . ":
         </p>";

	echo "<form method='post' action=''>";

	// Campo de texto preenchido com o nome do item
	echo "<label for='nome'>Nome do Novo Item:</label>";
	echo "<input type='text' name='nome' id='nome' placeholder='obrigatório' value='" . htmlspecialchars($item['name']) . "'>
          <br>";

	// Campo de rádio para selecionar o tipo do item
	echo "<label for='tipo'>Tipo de Item:</label><br>";
	$query_item_type = "SELECT id, name 
                        FROM item_type 
                        ORDER BY name";
	$resultado_item_type = mysqli_query($conexao, $query_item_type);

	if (mysqli_num_rows($resultado_item_type) > 0)
	{
		while ($tipo = mysqli_fetch_assoc($resultado_item_type))
		{
			$tipo_item_id = $tipo['id'];
			$tipo_item_name = htmlspecialchars($tipo['name']);
			// Comparação para verificar se o tipo atual deve estar selecionado
			$checked = ($item['item_type_id'] == $tipo_item_id) ? 'checked' : '';
			echo "<input type='radio' id='tipo_item_$tipo_item_id' name='tipo' value='$tipo_item_id' $checked>";
			echo "<label for='tipo_item_$tipo_item_id'>$tipo_item_name</label><br>";
		}
	}
	else
	{
		echo "Não existem tipos de itens disponíveis!";
	}

	// Campo de rádio para o estado do item
	echo "<label>Estado:</label><br>";
	$estados = ['active', 'inactive'];
	foreach ($estados as $estado)
	{
		// Comparação para verificar se o estado atual deve estar selecionado
		$checked = ($item['state'] === $estado) ? 'checked' : '';
		echo "<input type='radio' id='estado_$estado' name='estado_item' value='$estado' $checked>";
		echo "<label for='estado_$estado'>$estado</label><br>";
	}

	// Campo oculto para o estado do formulário e botão de submissão
	echo "<input type='hidden' name='estado' value='atualizar'>";
	echo "<button type='submit' name='atualizar'>Atualizar Item</button>";
	echo "</form>";
}
?>