#!/bin/bash

count_lines() {
  file=$1
  echo "$file:"
  awk '
    BEGIN { inside_comment=0 }
    {
      # Remove spaces and tabs
      gsub(/[ \t\r]/, "", $0)

      # Detect the start of a multiline comment
      if ($0 ~ /\/\*/) {
        inside_comment = 1
      }

      # If not inside a multiline comment and not a single-line comment, count the line
      if (!inside_comment && $0 !~ /^\/\// && $0 != "") {
        valid_lines++
      }

      # Detect the end of a multiline comment
      if ($0 ~ /\*\//) {
        inside_comment = 0
      }
    }

    END { print valid_lines }
  ' "$file"
}

# List of files to process
files=(
  "common.php"
  "gestao-de-registos.php"
  "gestao-de-itens.php"
  "gestao-de-unidades.php"
  "gestao-de-subitens.php"
  "gestao-de-valores-permitidos.php"
  "insercao-de-valores.php"
  "pesquisa.php"
  "importacao-de-valores.php"
  "edicao-de-dados.php"
  
)

# Loop through files and count lines
for file in "${files[@]}"; do
  count_lines "$file"
done