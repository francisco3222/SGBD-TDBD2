#!/bin/bash
echo "common.php:"
cat common.php  |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "gestao-de-registos.php:"
cat gestao-de-registos.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "gestao-de-itens.php:"
cat gestao-de-itens.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "gestao-de-unidades.php:"
cat gestao-de-unidades.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "gestao-de-subitens:"
cat gestao-de-subitens.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "gestao-de-valores-permitidos.php:"
cat gestao-de-valores-permitidos.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "insercao-de-valores.php:"
cat insercao-de-valores.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "pesquisa.php:"
cat pesquisa.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "importacao-de-valores.php:"
cat importacao-de-valores.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l
echo "edicao-de-dados.php:"
cat edicao-de-dados.php |tr -d " \t\r"|grep .|grep -v ^\/\/|grep -v ^\/\\*|wc -l

