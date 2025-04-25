<?php
require_once ('common.php');
echo "<link rel='stylesheet' type='text/css' href='../css/ag.css' />";
const SECRET_KEY = 'tenta_adivinhar';
//Verificação do utilizador, caso ele esteja logado e possua a capability de 'manage-records'
if (!is_user_logged_in() || !current_user_can('manage_items') )
{
	echo "<p>Não tem autorização para aceder a esta página.</p>";
	return;
}
// Verificação se nos encontramos na página correta
elseif (is_page('gestao-de-itens'))
{
	exibir_conteudo_gestao_de_itens();
}
//Caso contrário
else
{
	echo "<br>Esta não é a página de Gestão de Itens!";
}

function exibir_conteudo_gestao_de_itens():void
{
	// Se o parâmetro estado estiver presente na URL, o valor de $_GET['estado'] será sanitize (para evitar a injeção de código malicioso) e atribuído à variável $estado.
	$estado_itens = $_REQUEST['estado'] ?? '';
	if ($estado_itens ==='inserir')
	{
		inserir_itens_na_bd(); // Chama a função para exibir inserir dados na Base de Dados
		echo "<br><br>";
		get_footer();
	}
	else
	{
		tabela_de_itens(); // Chama a função para exibir os registos
		formulario_de_itens(); // Chama a função para exibir o formulário de Inserção de Itens
		echo "<br><br>";
		get_footer();
	}
}

function inserir_itens_na_bd(): void
{
		$conexao = conexao_base_de_dados();

		$tipo_de_item_id = $_REQUEST['tipo'] ?? null;
		$nome = htmlspecialchars($_REQUEST['nome']);
		$estado_item = $_REQUEST['estado_item'] ?? null;
		// Headline
		echo '<div class="titulos_da_gestão_de_items_registos" >
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Itens - Inserção</h3>
			  </div>';
		// Valores Inseridos na Base de Dados
		echo "<pre id='pre_gestao_de_itens'>";
		echo "Tipo: " . $tipo_de_item_id . "\n";
		echo "Nome: " . htmlspecialchars($nome) . "\n";
		echo "Estado: " . $estado_item . "\n";
		echo "</pre>";

		// Query para inserir o item na base de dados
		$query_inserir_item = "
	        INSERT INTO item (name, item_type_id, state)
	        VALUES ('$nome', $tipo_de_item_id, '$estado_item')
	    ";

		$resultado_inserir_item = mysqli_query($conexao, $query_inserir_item);
		// Verificação do resultado
		if ( $resultado_inserir_item ) {
			echo '<p id="sucesso_inserir_item">Dados inseridos com sucesso!</p>';
			echo "<form action='' method='POST'>";
			echo "<input type='hidden' name='estado' value=''> ";
			// Botão para voltar para a página gestao de Iten
			echo "<input type='submit' value='Ver Itens'>";
			echo "</form>";
		} else {
			echo '<div class="erro_ao_inserir_items_registos">
					  <p>Erro ao inserir registo do novo item!
				      <br>';
		}
		if ( empty( $nome ) || empty( $tipo_de_item_id ) || empty( $estado_item ) ) {
			echo '	  Por favor, preencha todos os campos do formulário!
					  <br>
					  Para voltar para trás, clique no botão a abaixo.
					  <br>
					  </p>';
			echo "</div>";
			echo "<br>
				  <br>";
			echo "<form action='' method='POST'>";
			echo "<input type='hidden' name='estado' value=''> ";
			// Botão para voltar para a página gestao de Iten
			echo "<input type='submit' value='Voltar Atrás'>";
			echo "</form>";
		}
}

function formulario_de_itens():void
{
	$conexao = conexao_base_de_dados();
	echo '
		  <div class="titulos_da_gestão_de_items_registos">
				<h3 class="h3_titulos_gestão_de_items_registos">Gestão de Itens - Introdução</h3>
		  </div>';

	echo"<p class='introdução_itens_registos_formulario'>
			Introduza no formulário abaixo os dados necessários para a criação de um novo item:
		 </p>";

	echo "<form method='post' action=''>";

	echo "<label for='nome'>Nome do Novo Item:</label>";
	echo "<input type='text' name='nome' id='nome' placeholder='obrigatório'>
			<br>";

	echo "
		  <label for='tipo'>Tipo de Item:</label>
		  <br>";
	$query_item_type = "SELECT id,
       						   name 
						FROM item_type
						ORDER BY name";
	$resultado_item_type = mysqli_query($conexao, $query_item_type);

	if (mysqli_num_rows($resultado_item_type) > 0)
	{
		while ($tipo = mysqli_fetch_assoc($resultado_item_type))
		{
			$tipo_item_id = $tipo['id'];
			$tipo_item_name = htmlspecialchars($tipo['name']); // Evitar problemas com HTML
			echo "<input type='radio'  id='tipo_item_id' name='tipo' value='$tipo_item_id' >";
			echo "<label class='label_item' for='tipo_item_id'>$tipo_item_name</label><br>";
		}
	}
	else
	{
		echo "Não existem tipos de itens disponíveis!";
	}

	echo "<label>Estado:</label>
		  <br>";
	$estados = ['active', 'inactive'];
	foreach ($estados as $estado)
	{
		echo "<input type='radio' id='estado_item' name='estado_item' value='$estado' >";
		echo "<label  class='label_item' for='estado_item'>$estado</label><br>";
	}
	echo "<br>";
	echo "<input type='hidden' name='estado' value='inserir'>";
	echo "<button type='submit' name='inserir'>Inserir Item</button>";
	echo "</form>";

}

function tabela_de_itens():void
{
	$conexao =conexao_base_de_dados();
	echo '<div class="titulos_da_gestão_de_items_registos">
	              <strong class="bem_vindo_titulo_gestao_de_itens_registos"> Bem-Vindo à Página de Gestão de Itens </strong>,
	              aqui encontrará todos os itens e respetivos tipos, organizados na tabela abaixo.
	          <br>
	               Também, caso queira inserir itens na base de dados,poderá fazê-lo através do formulário, que se encontra logo após a tabela.
	      </div>';

	// Cabeçalho da tabela
	echo '<table >';
	echo '<thead>';
	echo '<tr class="tabela_de_gestao_de_itens_registos">';
	echo '<th class="tabela_de_gestao_de_itens_registos_1_linha">Tipo de Item</th>';
	echo '<th class="tabela_de_gestao_de_itens_registos_1_linha">ID</th>';
	echo '<th class="tabela_de_gestao_de_itens_registos_1_linha">Nome do Item</th>';
	echo '<th class="tabela_de_gestao_de_itens_registos_1_linha">Estado</th>';
	echo '<th class="tabela_de_gestao_de_itens_registos_1_linha">Ação</th>';
	echo '</tr>';
	echo '</thead>';

	//Aqui Inicia o body da tabela e tudo o que é necessário para preenche-lo
	echo '<tbody>';

	// Query para obter os tipos de itens
	$query_tipos_itens = "
	    SELECT DISTINCT item_type.id AS type_id, item_type.name AS type_name
	    FROM item_type 
	    INNER JOIN item ON item.item_type_id = item_type.id
	    ORDER BY item_type.name 
	";
	$resultado_types = mysqli_query(conexao_base_de_dados(), $query_tipos_itens);

	if (mysqli_num_rows($resultado_types) > 0)
	{
		while ($type = mysqli_fetch_assoc($resultado_types)) {
			$tipo_id = $type['type_id'];
			$tipo_name = $type['type_name'];

			// Query para obter itens do tipo atual
			$query_itens = "
	            SELECT id, name, state
	            FROM item
	            WHERE item_type_id = $tipo_id
	            ORDER BY name 
	        ";
			$resultado_itens = mysqli_query($conexao, $query_itens);
			$num_itens = mysqli_num_rows($resultado_itens);

			if ($num_itens > 0)
	        {
				$primeira_linha = true;

				while ($item = mysqli_fetch_assoc($resultado_itens))
	            {
					echo '<tr class = "tabela_de_gestao_de_itens_registos">';

					// Imprime o nome do tipo de item apenas na primeira linha
					if ($primeira_linha)
	                {
						echo "<td rowspan='$num_itens'>$tipo_name</td>";
						$primeira_linha = false;
					}
					// Dados de cada item
					$item_id = $item['id'];
					$item_name = $item['name'];
					$item_state = $item['state'];
					if($item_id > 0) {
						$token_editar = hash_hmac('sha256', $item_id . 'editar', SECRET_KEY);
						$url_editar = '/sgbd/edicao-de-dados/?' . http_build_query([
								'estado' => 'editar_itens',
								'id' => $item_id,
								'token' => $token_editar,
							]);
						$url_apagar = '/sgbd/edicao-de-dados/?' . http_build_query([
								'estado' => 'apagar',
								'id' => $item_id,
								'token' => $token_editar,
							]);
						$url_mudar_estado = '/sgbd/edicao-de-dados/?' . http_build_query([
								'estado' => 'mudar_estado',
								'id' => $item_id,
								'token' => $token_editar,
							]);

						$estado_cor = ( $item_state === 'active' ) ? 'green' : 'red';
						// Ações (editar, desativar/ativar, apagar)
						$acao_estado = ( $item_state === 'active' ) ? "<a href='" . htmlspecialchars($url_mudar_estado,ENT_QUOTES, 'UTF-8') . "' onclick='return confirm(\"Tem certeza que deseja alterar o estado deste item?\");' >[desativar]</a>"
							: "<a href='" . htmlspecialchars($url_mudar_estado, ENT_QUOTES, 'UTF-8') . "' onclick='return confirm(\"Tem certeza que deseja alterar o estado deste item?\");' >[ativar]</a>";
						$acoes       = "<a href='" . htmlspecialchars($url_editar, ENT_QUOTES, 'UTF-8') . "'>[editar]</a> 
		                                $acao_estado 
		                          <a href='" . htmlspecialchars($url_apagar, ENT_QUOTES, 'UTF-8') . "' onclick='return confirm(\"Tem certeza que deseja apagar este item?\");'>[apagar]</a>";

						echo "<td class='tabela_de_gestao_de_itens_registos' >$item_id</td>";
						echo "<td class='tabela_de_gestao_de_itens_registos' >$item_name</td>";
						echo "<td class='tabela_de_gestao_de_itens_registos' style='color:$estado_cor'>$item_state</td>";
						echo "<td class='tabela_de_gestao_de_itens_registos' >$acoes</td>";
						echo '</tr>';
					}
					else
					{
						echo "ID inválido!";
					}
				}
			}
	        else
	        {
				// Caso não haja itens para este tipo de item
				echo '<tr>';
				echo "<td colspan='5'>Não existem itens para o tipo <strong>$tipo_name</strong>.</td>";
				echo '</tr>';
			}
		}
	}
	else
	{
		// Caso não haja tipos de itens na tabela
		echo '<tr>';
		echo '<td colspan=5>Não há itens</td>';
	    echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
}
?>