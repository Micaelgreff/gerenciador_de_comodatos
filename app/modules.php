<?php
// Only this allowlist can provide SQL identifiers.
return [
 'comodantes'=>['title'=>'Comodantes','singular'=>'comodante','label'=>'nome_fantasia','description'=>'Empresas responsáveis pelos equipamentos.','fields'=>[
 'nome_fantasia'=>['Nome fantasia','text',true,255], 'razao_social'=>['Razão social','text',true,255], 'cnpj'=>['CNPJ','text',true,14], 'endereco'=>['Endereço','textarea',true]]],
 'comodatarios'=>['title'=>'Comodatários','singular'=>'comodatário','label'=>'nome_completo','description'=>'Colaboradores que recebem os equipamentos.','fields'=>[
 'nome_completo'=>['Nome completo','text',true,255], 'cpf'=>['CPF','text',true,11], 'endereco'=>['Endereço','textarea',true]]],
 'marcas'=>['title'=>'Marcas','singular'=>'marca','label'=>'nome','description'=>'Fabricantes dos equipamentos do inventário.','fields'=>[
 'nome'=>['Nome','text',true,100], 'identificador_externo'=>['Identificador externo','text',false,100]]],
 'tipos_equipamentos'=>['title'=>'Tipos de equipamentos','singular'=>'tipo de equipamento','label'=>'nome','description'=>'Categorias para organizar seu parque de equipamentos.','fields'=>[
 'nome'=>['Nome','text',true,100], 'descricao'=>['Descrição','textarea',false]]],
 'modelos'=>['title'=>'Modelos','singular'=>'modelo','label'=>'nome','description'=>'Modelos vinculados a uma marca e um tipo.','fields'=>[
 'nome'=>['Nome','text',true,150], 'marca_id'=>['Marca','select',true,'marcas'], 'tipo_equipamento_id'=>['Tipo de equipamento','select',true,'tipos_equipamentos'], 'descricao'=>['Descrição','textarea',false]]],
 'inventario'=>['title'=>'Inventário','singular'=>'item','label'=>'patrimonio','description'=>'Uma linha, um equipamento físico. Acompanhe disponibilidade e responsável.','fields'=>[
 'modelo_id'=>['Modelo','select',true,'modelos'], 'patrimonio'=>['Patrimônio','text',false,100]]],
 'users'=>['title'=>'Usuários','singular'=>'usuário','label'=>'name','description'=>'Contas com acesso à gestão da aplicação.','fields'=>[
 'name'=>['Nome','text',true,255], 'email'=>['E-mail','email',true,255], 'password'=>['Senha','password',false,255]]],
 'comodatos'=>['title'=>'Comodatos','singular'=>'comodato','label'=>'id','description'=>'Equipamentos emprestados, responsáveis e devoluções.','fields'=>[
 'comodante_id'=>['Comodante','select',true,'comodantes'], 'comodatario_id'=>['Comodatário','select',true,'comodatarios'], 'data_inicio'=>['Data de início','date',true], 'data_fim'=>['Previsão de devolução','date',false], 'observacao'=>['Observações','textarea',false]]],
];
