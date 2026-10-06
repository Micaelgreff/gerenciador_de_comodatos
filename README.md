# GDC — Gestão de comodatos

Monólito PHP 8.2 + MariaDB, executado localmente no Apache do XAMPP. Sem integração obrigatória com Sênior, sem build de frontend e sem serviços externos. O login mantém o visual existente; a área autenticada usa CSS e JavaScript locais.

## Usar

Abra http://localhost/comodato/ e entre com a conta existente. Cadastre marcas e tipos, depois modelos, itens do inventário, comodantes e comodatários. Por último, registre os comodatos físicos existentes e selecione os equipamentos entregues.

Todos os módulos permitem listar, buscar, cadastrar e editar. Cadastros auxiliares e usuários podem ser inativados; registros com vínculos ativos têm proteção. Comodatos são encerrados pelo botão **Registrar devolução**. Não há exclusão definitiva pela interface.

Cada registro de inventário representa exatamente um item físico. Patrimônio é opcional e não pode se repetir quando preenchido; o ID identifica itens sem etiqueta. Não existe campo de quantidade. A lista mostra o tipo e o responsável atual; o formulário mostra o histórico de empréstimos. No cadastro, selecione a marca e depois o tipo para filtrar modelos, ou selecione o modelo diretamente para preencher seu tipo. Marca e tipo são derivados do modelo, sem duplicar esses vínculos no inventário.

Os seletores do comodato consultam somente os 10 primeiros resultados ao abrir. O campo de busca fica no topo do menu e consulta após 500 ms sem digitação; cada busca também retorna até 10 resultados. Equipamentos são selecionados em linhas que ocupam toda a largura, com **Adicionar +** e **Remover**. A primeira linha é obrigatória; remover a última linha limpa sua seleção.

Ao selecionar um equipamento, o servidor cria uma reserva exclusiva para aquele formulário e sessão. A reserva dura 3 minutos e o navegador a renova a cada 45 segundos enquanto o formulário está aberto. Remover ou trocar um equipamento libera sua reserva; sair do formulário tenta liberar todas as reservas, e a expiração cobre abandono ou perda de conexão. Outros formulários não listam equipamentos reservados, e uma tentativa feita com uma lista desatualizada é rejeitada no servidor. O salvamento exige reservas válidas, rejeita equipamentos repetidos e converte as reservas em vínculos do comodato na mesma transação. Reservas expiradas exigem nova seleção.

`data_fim` representa a previsão de devolução; `devolvido_em` representa a devolução efetiva de todos os itens. Atraso é calculado pela previsão vencida em comodatos abertos. Remover um item ao editar o comodato libera esse item e preserva o vínculo inativo no histórico. Comodatos encerrados ficam somente para consulta. Não há devolução parcial com data individual nesta versão.

## Estrutura

- `index.php`, `app.php`, `login.php` e `logout.php`: pontos de entrada públicos; apenas carregam seus controladores internos.
- `app/controllers/`: entrada autenticada, exibição do login, autenticação e encerramento da sessão.
- `app/bootstrap.php`: conexão PDO, sessão, escape HTML e proteção CSRF.
- `app/modules.php`: definição dos oito cadastros e seus campos.
- `app/services.php`: validação, persistência, disponibilidade, devolução e busca.
- `templates/`: login, layout, dashboard, listas, formulários e seletores compartilhados.
- `assets/`: CSS e JavaScript próprios do login e da área autenticada, com versões por conteúdo para atualizar o cache.
- `assets/vendor/`: CSS compilado do tema usado pelo login. Os ícones do login usam `vendor/fontawesome-free/`, com suas fontes e licença.
- `config/`: configuração local existente, preservada.
- `database/`: schema, migrações incrementais e execução por CLI.
- `tests/`: testes de integração e verificações HTTP locais.
- `doc-comodatos/`: protótipo de documentos bloqueado para acesso web, com seu carregador próprio; não foi integrado ao fluxo atual.

Os atalhos antigos da raiz são redirecionados pelo `.htaccess`, sem arquivos PHP individuais. Os redirecionamentos usam HTTP 303 e descartam parâmetros antigos; POSTs dos formulários antigos abrem o fluxo atual sem reaplicar dados. Atalhos nas pastas `view/` e `comodatos/` continuam como entradas de compatibilidade. Foram removidos os HTMLs de demonstração, `index.html`, imagens e fontes sem uso, fontes SCSS do painel antigo, scripts de demonstração e bibliotecas JavaScript sem referências na aplicação: jQuery, jQuery Easing, Bootstrap JS, Chart.js e DataTables. O login mantém o visual existente e usa JavaScript nativo para mostrar ou ocultar a senha.

`composer.json` e `composer.lock` permanecem na raiz, junto de `vendor/`, para gerenciamento das dependências PHP. `.htaccess`, arquivos do Git e este README também pertencem à raiz. A conexão antiga `conection.php` e os manifestos Node sem uso foram removidos; não há build de frontend nem aplicação Node ativa. A pasta local `node_modules/` permanece ignorada pelo Git e não participa da aplicação. O protótipo de documentos é uma referência de código, sem garantia de funcionamento com os recursos antigos removidos. Nenhuma dependência foi baixada.

O Apache precisa de `mod_rewrite` para os atalhos antigos. A instalação atual usa `RewriteBase /comodato/`; ajuste essa linha no `.htaccess` se publicar a aplicação em outro caminho. O acesso HTTP direto a `app/` permanece bloqueado.

## Banco e instalação

As credenciais continuam em `config/.env`. Nunca publique esse arquivo. O Apache bloqueia configuração, fontes internas, testes e legado.

Para um banco novo, execute `C:\xampp\php\php.exe database\setup.php` com uma conta de instalação autorizada e depois as migrações. Para um banco existente, execute somente `C:\xampp\php\php.exe database\migrate.php`. O migrador é idempotente: amplia o campo de senha, adiciona a data de devolução e cria o registro de trava transacional. Não remove registros.

A conta de execução configurada tem permissões limitadas. Para migrar, uma conta administrativa local pode ser fornecida ao processo pelas variáveis `GDC_MIGRATION_USER` e `GDC_MIGRATION_PASSWORD`, sem substituir as credenciais da aplicação. As migrações desta refatoração já foram aplicadas ao banco local `comodatos`.

A migração `003_inventory_reservations.sql` cria a tabela local de reservas. Para aplicar somente essa mudança sem carregar arquivos de configuração, execute `C:\xampp\php\php.exe database\migrate_reservations.php`. O comando usa a conta administrativa local padrão do XAMPP; host, banco e conta podem ser fornecidos por `GDC_MIGRATION_HOST`, `GDC_MIGRATION_DATABASE`, `GDC_MIGRATION_USER` e `GDC_MIGRATION_PASSWORD`. Contas com permissões por tabela que já gravam os vínculos de comodato recebem acesso à nova tabela; permissões por banco já a abrangem. Nenhum cadastro é removido ou alterado. A migração de reservas foi aplicada ao banco local `comodatos`.

Senhas novas usam `password_hash`. Hashes MD5 antigos são aceitos somente para compatibilidade e convertidos no próximo login bem-sucedido. A sessão armazena ID e token CSRF, sem senha. Contas inativas não acessam o sistema. Como o schema não define perfis, todos os usuários ativos têm acesso aos módulos administrativos, incluindo usuários.

A trava `app_write_lock` serializa as gravações da aplicação e impede atribuições concorrentes do mesmo item. Integrações futuras que escrevam diretamente no banco precisam respeitar a mesma regra. Datas usam America/Sao_Paulo no PHP; o MariaDB local deve manter o fuso compatível.

## Verificação local

`C:\xampp\php\php.exe tests\root_routes.php` confere no Apache local os redirecionamentos antigos por GET e POST e o bloqueio dos controladores internos. Não segue redirecionamentos, carrega configuração ou consulta o banco.

`C:\xampp\php\php.exe tests\frontend_assets.php` verifica os recursos do login e as fontes referenciadas pelos estilos, sem carregar configuração ou banco. Os testes de integração também conferem os caminhos dos recursos de todas as telas renderizadas.

`C:\xampp\php\php.exe tests\integration.php` cria um banco temporário com dados fictícios e o remove ao terminar. Usa uma conta local administrativa (`root` sem senha no XAMPP padrão); pode ser configurada por `GDC_TEST_DB_USER` e `GDC_TEST_DB_PASSWORD`. Não copia dados reais. Verifica cadastros, validações, filtros, renderização, bloqueio de empréstimo duplicado e devoluções. `GDC_TEST_PREVIEW=1` exporta opcionalmente um HTML fictício do dashboard para revisão visual; remova-o depois da revisão.

`C:\xampp\php\php.exe tests\http_smoke.php` verifica as páginas no Apache com uma sessão temporária de uma conta ativa, sem modificar cadastros. Precisa escrever na pasta de sessões do XAMPP. Verifica autenticação, CSRF, rotas e bloqueio dos arquivos internos.

Não há integração Sênior, geração de contratos, envio de e-mails, controle de permissões por perfil ou anexos implementados nesta etapa.

Os testes de integração também cobrem limite de resultados, busca além dos 10 iniciais, compatibilidade entre marca/tipo/modelo, expiração e liberação de reservas, tokens vinculados à sessão e ao formulário, e disputa simultânea de duas conexões pelo mesmo equipamento. Para conferir a interface com dados fictícios e sem carregar configuração, execute `php tests\browser_fixture.php setup`, depois `php -S 127.0.0.1:8765 tests\browser_fixture.php` e abra `http://127.0.0.1:8765/app.php`. Ao terminar, pare esse servidor e execute `php tests\browser_fixture.php cleanup` para remover somente o banco temporário. A fixture não está disponível pelo Apache.
