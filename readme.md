# hibrido subscribers

esse plugin tem como objetivo montar um jeito fácil para que possamos mostrar um formulário com um input (campo de email) de cadastre-se na nossa newsletter e que quando o formulário seja enviado, ele seja enviado por ajax

para isso, após ativarmos o plugin é só termos um form que tenha um atributo `data-hibrido-subscribers-form` que contenha um `input[name=email]` e termos um outro elemento com atributo `data-hibrido-subscribers-response` para as respostas

# requerimentos

* deve ter uma tabela separada para os emails no banco de dados
* deve adicionar emails na tabela
* deve listar os emails no admin
* deve poder exportar os emails em csv
* deve manter a tabela de emails quando o plugin for desinstalado
* não deve deixar que emails duplicados sejam cadastrados na tabela

# todo

* deve poder remover em bulk
* deve conter paginação na tabela do admin
* deve conter filtro na tabela do admin
* deve conter ordenação na tabela do admin