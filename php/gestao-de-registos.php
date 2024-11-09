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
//Caso contrário
else {
	echo "Esta não é a página de registos";
}

// Exibe o conteúdo principal da página de gestão de registos
function exibir_conteudo_gestao_de_registos(): void {
	// Se o parâmetro estado estiver presente na URL, o valor de $_GET['estado'] será sanitize (para evitar a injeção de código malicioso) e atribuído à variável $estado.
	$estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';
	if (empty($estado)) {
		tabela_de_registos(); // Chama a função para exibir os registos
	} else {
		echo "Outro estado"; // Caso um estado específico seja passado na URL
	}
}

// Função que gera a tabela de registos de crianças
function tabela_de_registos(): void {
	global $conexao;

	// Query para buscar todas as crianças
	$query_1 = "SELECT * 
				FROM child 
				ORDER BY name";
	$crianca = mysqli_query(conexao_base_de_dados(), $query_1);

	// Verificar se houve erros na consulta
	if (!$crianca)
	{
		echo "<p>Erro ao executar a consulta: " . mysqli_error($conexao) . "</p>";
	}
	elseif (mysqli_num_rows($crianca) === 0)
	{
		echo "<p>Não existem registos na tabela 'child' que possamos utilizar</p>";
	}
	else
	{
		echo "<p style='font-size: 30px; color: darkblue'>Bem-vindo à pagina de Registos da nossa Base de Dados SQL</p>";
		echo "<table style='border: 2px solid gray; border-collapse: collapse;'>
            <tr>
                <th style='border: 2px solid gray; padding: 5px;'>Nome</th>    
                <th style='border: 2px solid gray; padding: 5px;'>Data de Nascimento</th>
                <th style='border: 2px solid gray; padding: 5px;'>Encarregado de Educação</th>
                <th style='border: 2px solid gray; padding: 5px;'>Telefone do Encarregado</th>
                <th style='border: 2px solid gray; padding: 5px;'>E-mail</th>
                <th style='border: 2px solid gray; padding: 5px;'>Registos</th>
            </tr>";

		// Exibir as informações de cada criança
		while ($child = mysqli_fetch_assoc($crianca))
		{
			$child_id = $child['id'];

			// Query para ir buscar os registos para a  coluna 'registos' de cada criança
			$query_registos = "
               SELECT UPPER(item.name) AS Nome_do_Item,
                       DATE_FORMAT(value.date, '%Y-%m-%d') AS Data,
                       TIME_FORMAT(value.time, '%H:%i:%s') AS Horas,
                       value.producer AS User,
                       subitem.name AS Nome_do_Subitem,
                       value.value AS Valor_do_Subitem
                FROM value
                JOIN subitem ON value.subitem_id = subitem.id
                JOIN item ON subitem.item_id = item.id
                WHERE value.child_id = $child_id
                ORDER BY item.name, value.date, value.time;
            ";
			//A query será executada na base de dados
			$registos = mysqli_query(conexao_base_de_dados(), $query_registos);
            //O resultado que queremos que apareça na coluna registos irá ser guardado aqui
			$registos_resultado = "";

            //Caso a criança tenha 1 ou mais registos irá realizar o código dentro do 'if'
            if (mysqli_num_rows($registos) >= 1)
			{
                // Inicialização das variáveis para controlo do último item, data e hora
                $ultimo_item = ""; // Armazena o nome do último item processado
                $ultima_data_e_hora = ""; // Armazena a última combinação de data e hora processada
                $conjunto_dos_subitems = ""; // Variável para acumular os subitens que pertencem à mesma data/hora

                // While que itera por cada registo encontrado na consulta
                while ($registo = mysqli_fetch_assoc($registos))
				{
                    $nome_do_item = $registo['Nome_do_Item']; // Obtém o nome do item do registo atual
                    $data_e_hora_atual = "{$registo['Data']} {$registo['Horas']}"; // Combina data e hora para identificar o grupo de subitens

                    // Verifica se o item ou a data/hora atual são diferentes do último registado
                    if ($ultimo_item !== $nome_do_item || $ultima_data_e_hora !== $data_e_hora_atual)
					{
                        // Se existirem subitens acumulados para o grupo anterior, adiciona-os ao resultado
                        if ($conjunto_dos_subitems)
						{
                            $registos_resultado .= "{$conjunto_dos_subitems}<br>"; // Exibe os subitens acumulados até agora
                        }

                        // Se o nome do item atual for diferente do último item, exibe o novo item
                        if ($ultimo_item !== $nome_do_item)
						{
                            $registos_resultado .= "<b>{$nome_do_item}</b>:<br>"; // Exibe o nome do item em negrito
                        }

                        // Exibe o registo com a data, hora e o utilizador, juntamente com links para editar e apagar
                        $registos_resultado .= "[<a href=\"editar.php?id={$child_id}\">editar</a>] 
                                     [<a href=\"apagar.php?id={$child_id}\">apagar</a>] - 
                                     <b><strong>{$registo['Data']}</b> {$registo['Horas']}</strong> 
                                     ({$registo['User']}) - ";

                        // Reinicia a variável para acumular os subitens do novo grupo
                        $conjunto_dos_subitems = "";
                        // Atualiza o último item e data/hora com os valores atuais
                        $ultimo_item = $nome_do_item;
                        $ultima_data_e_hora = $data_e_hora_atual;
                    }

                    // Acumula os subitens e os seus valores para a mesma data e hora
                    $conjunto_dos_subitems .= "<strong>{$registo['Nome_do_Subitem']}</strong> ({$registo['Valor_do_Subitem']}); "; // Exibe o subitem com o seu valor
                }

                // Adiciona os últimos subitens acumulados ao resultado, se existirem
                if ($conjunto_dos_subitems)
				{
                    $registos_resultado .= "{$conjunto_dos_subitems}<br>"; // Exibe o último conjunto de subitens
                }
            }
            // Caso contrário (se não houver registos), o código não faz nada aqui
            else
			{
				$registos_resultado = "<p>Nenhum registo encontrado para esta criança.</p>";
			}
			// Exibir dos dados de cada criança nas respetivas colunas
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
	}
}
?>


