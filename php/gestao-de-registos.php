<?php
require_once ('custom/php/common.php');

//Verificação do utilizador, caso ele esteja logado e possua a capability de 'manage-records'
if (!current_user_can('manage_records') || !is_user_logged_in() ) {
	echo "<p>Não tem autorização para aceder a esta página.</p>";
	return;
}
// Verificação se nos encontramos na página correta
elseif (is_page('gestao-de-registos')) {
	exibir_conteudo_gestao_de_registos();
}
else {
	echo "Esta não é a página de registos";
}

// Exibe o conteúdo principal da página de gestão
function exibir_conteudo_gestao_de_registos(): void {
	// Se o parâmetro estado estiver presente na URL, o valor de $_GET['estado'] será sanitize (para evitar a injeção de código malicioso) e atribuído à variável $estado.
	$estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
	if (empty($estado)) {
		tabela_de_registos(); // Chama a função para exibir os registros
	} else {
		echo "Outro estado"; // Caso um estado específico seja passado na URL
	}
}

// Função que gera a tabela de registros de crianças
function tabela_de_registos(): void {
	global $conexao;
	// Definição da query que busca todos os dados da tabela 'child'
	$query_1 = "SELECT * FROM child ORDER BY name";
	// Executa a consulta na base de dados
	$crianca = mysqli_query( conexao_base_de_dados(), $query_1 );
	// Verifica se houve algum erro na query
	if ( ! $crianca )
	{
		echo "<p>Erro ao executar a consulta: " . mysqli_error( $conexao ) . "</p>";
	}
	elseif(mysqli_num_rows($crianca) === 0){
		echo "<p>Não existem registros na tabela 'child' que possamos utilizar</p>";
	}
	else {
		echo "<p>A Tabela abaixo apresenta os registros da nossa base de dados</p>";
		echo "<table style='border: 2px solid gray; border-collapse: collapse;'>
            <tr>
                <th style='border: 2px solid gray; padding: 5px;'>Nome</th>    
                <th style='border: 2px solid gray; padding: 5px;'>Data de Nascimento</th>
                <th style='border: 2px solid gray; padding: 5px;'>Encarregado de Educação</th>
                <th style='border: 2px solid gray; padding: 5px;'>Telefone do Encarregado</th>
                <th style='border: 2px solid gray; padding: 5px;'>E-mail</th>
                <th style='border: 2px solid gray; padding: 5px;'>Registos</th>
            </tr>";

		// Exibe as informações encontradas de cada criança
			while ( $child = mysqli_fetch_assoc( $crianca ) ) {
				$child_id = $child['id'];

				$query_registos ="SELECT upper(item.name) AS Nome_do_Item,
	                DATE_FORMAT(value.date, '%Y-%m-%d') AS Data,
	                TIME_FORMAT(value.time,'%H:%i:%s') AS Horas,
	                value. producer AS User,
				    CONCAT(subitem.name,' (', value.value, ')' ) AS Conjunto_de_subitems 
					FROM value 
				    JOIN child ON value.child_id = child.id
				    JOIN subitem ON value.subitem_id = subitem.id 
				    JOIN item ON subitem.item_id = item.id
				    WHERE value.child_id = $child_id
				    GROUP BY item.name, value.date, value.child_id ORDER BY item.name;";

				$registos = mysqli_query(conexao_base_de_dados(), $query_registos );
				$registos_resultado="";
				if (mysqli_num_rows($registos) >= 1)
				{
					while ($registo = mysqli_fetch_assoc($registos))
					{
						// Concatenando os registros formatados
						$registos_resultado .= "{$registo['Nome_do_Item']}</b>:<br>[<a href=\"editar.php?id=', value.child_id, '\">editar</a>] 
				        [<a href=\"apagar.php?id=', value.child_id, '\">apagar</a>]\n {$registo['Data']}\n {$registo['Horas']}\n ({$registo['User']}) - {$registo['Conjunto_de_subitems']}\n <br>";
					}
				}
				else {
					$registos_resultado = "<p>Nenhum registo encontrado para esta criança.</p>";
				}
				echo "
				  <tr>
	                <td style='border: 2px solid gray; padding: 5px;'>{$child['name']}</td>
	                <td style='border: 2px solid gray; padding: 5px;'>{$child['birth_date']}</td>
	                <td style='border: 2px solid gray; padding: 5px;'>{$child['tutor_name']}</td>
	                <td style='border: 2px solid gray; padding: 5px;'>{$child['tutor_phone']}</td>
	                <td style='border: 2px solid gray; padding: 5px;'>{$child['tutor_email']}</td>
	                <td style='border: 2px solid gray; padding: 5px;'> {$registos_resultado} </td>
	              </tr>";
			}
		echo "</table>";
		get_footer();
	}
}
?>

