<?php
require_once ('custom/php/common.php');
echo "<link rel='stylesheet' type='text/css' href='../css/ag.css' />";
const SECRET_KEY = 'tenta_adivinhar';

//Verificação do utilizador, caso ele esteja logado e possua a capability de 'manage-records'
if (!is_user_logged_in() || !current_user_can('manage_records') )
{
	echo "<p>Não tem autorização para aceder a esta página.</p>";
	return;
}
// Verificação se nos encontramos na página correta
elseif (is_page('gestao-de-registos'))
{
	exibir_conteudo_gestao_de_registos();
}
//Caso contrário
else
{
	echo "Esta não é a página de registos";
}
// Exibe o conteúdo principal da página de gestão de registos
function exibir_conteudo_gestao_de_registos(): void
{
	// Se o parâmetro estado estiver presente na URL, o valor de $_GET['estado'] será sanitize (para evitar a injeção de código malicioso) e atribuído à variável $estado.
	$estado = $_REQUEST['estado'] ?? '';

	if($estado === 'validar')
	{
		validar_dados(); // Chama a função para Validar os dados recebidos pelo formulário
		echo "<br><br>";
		get_footer();
	}
	elseif ($estado ==='inserir')
	{
		inserir_dados_na_bd(); // Chama a função para exibir inserir dados na Base de Dados
		echo "<br><br>";
		get_footer();
	}
	else
	{
		tabela_de_registos(); // Chama a função para exibir os registos
		formulario(); // Chama a função para exibir o formulário de Inserção
		echo "<br><br>";
		get_footer();
	}
}

function inserir_dados_na_bd(): void
{
	// Obter os valores dos campos enviados no estado anterior via POST
	$conexao = conexao_base_de_dados();
	$nome                    = $_POST['nome'];
	$data_de_nascimento      = $_POST['data_nascimento'];
	$encarregado_de_educacao = $_POST['encarregado_de_educacao'];
	$telefone                = $_POST['telefone'];
	$email                   = $_POST['email'] ?? '';

	// Construção da query SQL de inserção, omitindo o campo `id` (auto-increment)
	$query_insercao = "INSERT INTO child 
    						(name, birth_date, tutor_name, tutor_phone, tutor_email)
                       VALUES 
                           	('$nome', '$data_de_nascimento', '$encarregado_de_educacao', '$telefone', '$email')";

	// Executar a query de inserção
	if (mysqli_query($conexao, $query_insercao))
	{
		// Obter o último ID inserido para a nova criança
		$novo_id = mysqli_insert_id($conexao);
		// Mensagens de confirmação de sucesso
		echo "<div class='titulos_da_gestão_de_items_registos'>
				  <h3 class='h3_titulos_gestão_de_items_registos'>
					Dados de registo - inserção
				  </h3>
		      </div>
		      <p class='bem_vindo_titulo_gestao_de_itens_registos'>
				ID da nova criança: <strong> $novo_id</strong>
			  <br>
		        Inseriu os Dados de Registo com sucesso na Base de Dados.
		      <br>
				Para continuar a navegar na página Registos.
				Clique no botão a baixo:
			  </p>
			  <br>";
		// Campo oculto "estado", para o valor do estado seguinte
		echo "<form action='' method='POST'>";
		echo "<input type='hidden' name='estado' value=''> ";
		// Botão para voltar para a página gestao de registos
		echo "<input type='submit' value='Voltar '>";
	}
	else
	{
		// Mensagem de erro se a inserção falhar
		echo "<p class='erro_ao_inserir_items_registos''>Erro ao inserir os dados: " . mysqli_error($conexao) . "</p>";
	}

	// Fechar a conexão
	mysqli_close($conexao);
}


function formulario(): void
{
	echo '<div class="titulos_da_gestão_de_items_registos">
			<h3 class="h3_titulos_gestão_de_items_registos">Dados de Registo - Introdução</h3>
		  </div>';
	echo"<p class='introdução_itens_registos_formulario'>Introduza os Dados Pessoais da criança:</p>";

	echo "<form action='' method='POST'>";
//Campo para o Nome Completo da Criança
	echo "<label for='nome'>Nome completo:</label><br>";
	echo "<input type='text' name='nome' id='nome' placeholder='(obrigatório)'>
			<br>";
// Campo para a Data de Nascimento da Criança
	echo "<label for='data_nascimento'>Data de nascimento:</label><br>";
	echo "<input type='text' placeholder='(obrigatório)' id='data_nascimento' name='data_nascimento' >
			<br>";

// Campo para o Nome do Encarregado de Educação
	echo "<label for='encarregado_de_educacao'>Nome completo do encarregado de educação:</label><br>";
	echo "<input type='text' placeholder='(obrigatório)' id='encarregado' name='encarregado_de_educacao' >
			<br>";

// Campo para o Telefone do Encarregado de Educação - apenas 9 dígitos
	echo "<label for='telefone'>Telefone do encarregado de educação:</label><br>";
	echo "<input type='text' placeholder='(obrigatório)' id='telefone' name='telefone'>
			<br>";

// Campo para o Endereço de E-mail do Tutor (opcional)
	echo "<label for='email'>Endereço de E-mail do tutor:</label><br>";
	echo "<input type='email' id='email' placeholder='(opcional)' name='email'>
			<br><br>";

// Campo oculto "estado" para o valor do estado seguinte
	echo "<input type='hidden' name='estado' value='validar'> ";

// Botão para Submeter o formulário
	echo "<input type='submit' value='Submeter'>";
	echo "</form>";
}

function validar_dados():void
{
	// Recupera os dados enviados pelo formulário anterior e armazena nas variáveis correspondentes
	$nome = $_POST['nome'];
	$data_nascimento = $_POST['data_nascimento'];
	$encarregado = $_POST['encarregado_de_educacao'];
	$telefone = $_POST['telefone'];
	$email = $_POST['email'] ?? '';

	// Inicializa um array para armazenar todas as possiveis mensagens de erro
	$erros = [];

	// Validação dos campos obrigatórios e formatos dos campos preenchidos pelo utilizador
	if (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/", $nome))
	{
		$erros[] .= "<strong>Nome Completo da Criança Inválido.</strong> 
						Por favor,certifique-se de que este campo contenha apenas letras e espaços.";
	}

	if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data_nascimento))
	{
		$erros[] .= "<strong>Data de Nascimento Inválida.</strong> 
						Por favor,certifique-se de que este campo esteja preenchido no de acordo com o seguinte formato AAAA-MM-DD.
						Certificando-se também que estes sejam digitos/números.";
	}

	if (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/", $encarregado))
	{
		$erros[] .= "<strong>Nome do Encarregado de Educação Inválido.</strong> 
						Por favor,certifique-se de que este campo contenha apenas letras e espaços.";
	}

	if (!preg_match("/^\d{9}$/", $telefone))
	{
		$erros[] .= "<strong>Telefone Inválido.</strong> 
						Por favor,certifique-se de que este campo esteja preenchido com exatamente 9 dígitos/números.";
	}

	// Valida o email apenas se estiver preenchido
	if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		$erros[] .= "<strong>Endereço de e-mail inválido.</strong> 
						Por favor, forneça um endereço de e-mail válido.";
	}

    if (!empty($erros))
	{
        echo "<h4 class='h3_titulos_gestão_de_items_registos' style='color:red'>Ocorreram os seguintes erros de validação:</h4>";
		echo "<ul>";
		foreach ($erros as $erro)
		{
			echo "<li>$erro</li>";
		}
		echo "</ul>";

		echo "<form action='' method='POST'>";
		echo "<input type='hidden' name='estado' value=''>"; // Estado 'vazio' para retornar à pagina anterior
		echo "<input type='submit' value='Voltar Atrás'>";
    }
	else
	{
		echo "<div class='titulos_da_gestão_de_items_registos'> 
				<h3 class='h3_titulos_gestão_de_items_registos'>Dados de Registo - Validação</h3>
			  </div>";
		// Se não houver erros, exibe a confirmação para o utilizador
        echo "<p>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p>";
		echo "<ul>";
		echo "<li><strong>Nome completo:</strong> $nome</li>";
		echo "<li><strong>Data de nascimento:</strong> $data_nascimento</li>";
		echo "<li><strong>Nome do encarregado de educação:</strong> $encarregado</li>";
		echo "<li><strong>Telefone do encarregado de educação:</strong> $telefone</li>";
		echo "<li><strong>E-mail do tutor:</strong> " . ($email ?: 'Não fornecido') . "</li>";
		echo "</ul>";

		// Formulário com os dados em campos escondidos para submissão
		echo "<form action='' method='POST'>";
		echo "<input type='hidden' name='nome' value='$nome'>";
		echo "<input type='hidden' name='data_nascimento' value='$data_nascimento'>";
		echo "<input type='hidden' name='encarregado_de_educacao' value='$encarregado'>";
		echo "<input type='hidden' name='telefone' value='$telefone'>";
		echo "<input type='hidden' name='email' value='$email'>";
		echo "<input type='hidden' name='estado' value='inserir'>";
		echo "<input type='submit' value='Submeter'>";
    }
    echo "</form>";
}



// Função que gera a tabela de registos de crianças
function tabela_de_registos(): void
{
	$conexao = conexao_base_de_dados();
	echo '<div class="titulos_da_gestão_de_items_registos">
                  <strong class="bem_vindo_titulo_gestao_de_itens_registos"> Bem-Vindo à Página de Gestão de Registos </strong>,
                  aqui encontrará todos os registos das crianças presentes na nossa base de dados.
              <br>
                   Também, caso queira registar uma criança na base de dados,poderá fazê-lo através do formulário, que se encontra logo após a tabela.
          </div>';
	// Query para buscar todas as crianças
	$query_childs = "
				SELECT * 
				FROM child 
				ORDER BY name
				";
	$crianca = mysqli_query($conexao, $query_childs);
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
		echo "<table>
            <tr class='tabela_de_gestao_de_itens_registos'>
                <th class='tabela_de_gestao_de_itens_registos_1_linha'>Nome</th>    
                <th class='tabela_de_gestao_de_itens_registos_1_linha'>Data de Nascimento</th>
                <th class='tabela_de_gestao_de_itens_registos_1_linha'>Encarregado de Educação</th>
                <th class='tabela_de_gestao_de_itens_registos_1_linha'>Telefone do Encarregado</th>
                <th class='tabela_de_gestao_de_itens_registos_1_linha'>E-mail</th>
                <th class='tabela_de_gestao_de_itens_registos_1_linha'>Registos</th>
            </tr>";

		// Exibir as informações de cada criança
		while ($child = mysqli_fetch_assoc($crianca))
		{
			$child_id = $child['id'];

			// Query para ir buscar os registos para a  coluna 'registos' de cada criança
			$query_registos = "
               SELECT 
                   UPPER(item.name) AS Nome_do_Item,
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
			$registos = mysqli_query($conexao, $query_registos);
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
                            $registos_resultado .= "$conjunto_dos_subitems<br>"; // Exibe os subitens acumulados até agora
                        }

                        // Se o nome do item atual for diferente do último item, exibe o novo item
                        if ($ultimo_item !== $nome_do_item)
						{
                            $registos_resultado .= "<b>$nome_do_item</b>:<br>"; // Exibe o nome do item em negrito
                        }
						$token_editar = hash_hmac('sha256', $child_id . 'editar', SECRET_KEY);
						$url_apagar_registo = '/sgbd/edicao-de-dados/?' . http_build_query([
								'estado' => 'apagar_registo',
								'id' => $child_id,
								'data' => $registo['Data'],
								'hora' => $registo['Horas'],
								'token' => $token_editar,
							]);
						$url_editar_registo = '/sgbd/edicao-de-dados/?' . http_build_query([
								'estado' => 'editar_registo',
								'id' => $child_id,
								'data' => $registo['Data'],
								'hora' => $registo['Horas'],
								'token' => $token_editar,
							]);
                        // Exibe o registo com a data, hora e o utilizador, juntamente com links para editar e apagar
                        $registos_resultado .= "
									 [<a href='". htmlspecialchars($url_editar_registo, ENT_QUOTES,'UTF-8' )."' onclick='return confirm(\"Tem certeza que deseja editar este Registo da Base de Dados?\");'>Editar</a>] 
                                     [<a href='" . htmlspecialchars($url_apagar_registo, ENT_QUOTES, 'UTF-8') . "' onclick='return confirm(\"Tem certeza que deseja apagar este Registo da Base de Dados?\");'>Apagar</a>] - 
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
                    $registos_resultado .= "$conjunto_dos_subitems<br>"; // Exibe o último conjunto de subitens
                }
            }
            // Caso contrário (se não houver registos), o código não faz nada aqui
            else
			{
				$token_editar = hash_hmac('sha256', $child_id . 'editar', SECRET_KEY);
				$url_apagar_crianca = '/sgbd/edicao-de-dados/?' . http_build_query([
						'estado' => 'apagar_crianca',
						'id' => $child_id,
						'token' => $token_editar,
					]);
				$registos_resultado = "<p>Nenhum registo encontrado para esta criança.
									 [<a href='/sgbd/insercao-de-valores'>Inserir Dados</a>] 
                                     [<a href='" . htmlspecialchars($url_apagar_crianca, ENT_QUOTES, 'UTF-8') . "' onclick='return confirm(\"Tem certeza que deseja apagar esta Criança da Base de Dados?\");'>Apagar</a>]</p>";
			}
			// Exibir dos dados de cada criança nas respetivas colunas
			echo "
              <tr>
                <td class='tabela_de_gestao_de_itens_registos'>{$child['name']}</td>
                <td class='tabela_de_gestao_de_itens_registos'>{$child['birth_date']}</td>
                <td class='tabela_de_gestao_de_itens_registos'>{$child['tutor_name']}</td>
                <td class='tabela_de_gestao_de_itens_registos'>{$child['tutor_phone']}</td>
                <td class='tabela_de_gestao_de_itens_registos'>{$child['tutor_email']}</td>
                <td class='tabela_de_gestao_de_itens_registos'> $registos_resultado </td>
              </tr>";
		}
		echo "</table>";
	}
}
?>


