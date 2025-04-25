<?php
$conexao = conexao_base_de_dados();

echo "<script type='text/javascript'>
document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atrás'>Voltar atrás</a>\");
</script>";

if (isset($_SERVER['HTTP_REFERER'])) {
    echo "<noscript>
    <a href='" . $_SERVER['HTTP_REFERER'] . "' class='backLink' title='Voltar atrás'>Voltar atrás</a>
    </noscript>";
} else {
    echo "<noscript>
    <a href='#' class='backLink' title='Voltar atrás'>Voltar atrás</a>
    </noscript>";
}

function get_enum_values($connection, $table, $column) {
    $query = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    #extract the values
    #the values are enclosed in single quotes
    #and separated by commas
    $regex = "/'(.*?)'/";
    preg_match_all($regex, $row[1], $enum_array);
    $enum_fields = $enum_array[1];
    return $enum_fields;
}

// Verifique se a conexão falhou
function conexao_base_de_dados() {
    $conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conexao->connect_error) {
        die("Falha na conexão: " . $conexao->connect_error);
    } else {
        return $conexao;
    }
}