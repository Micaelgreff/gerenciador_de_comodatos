# GDC — Gestão de comodatos

Monólito PHP 8.2 + MariaDB, executado localmente no Apache do XAMPP. Sem integração obrigatória com Sênior, sem build de frontend e sem serviços externos. O login mantém o visual existente; a área autenticada usa CSS e JavaScript locais.

## Usar

Abra http://localhost/comodato/ e entre com a conta existente. Cadastre marcas e tipos, depois modelos, itens do inventário, comodantes e comodatários. Por último, registre os comodatos físicos existentes e selecione os equipamentos entregues.

Todos os módulos permitem listar, buscar, cadastrar e editar. Cadastros auxiliares e usuários podem ser inativados; registros com vínculos ativos têm proteção. Comodatos são encerrados pelo botão **Registrar devolução**. Não há exclusão definitiva pela interface.

Cada registro de inventário representa exatamente um item físico. Patrimônio é opcional e não pode se repetir quando preenchido; o ID identifica itens sem etiqueta. Não existe campo de quantidade. A lista mostra o responsável atual e o formulário mostra o histórico de empréstimos.

`data_fim` representa a previsão de devolução; `devolvido_em` representa a devolução efetiva de todos os itens. Atraso é calculado pela previsão vencida em comodatos abertos. Remover um item ao editar o comodato libera esse item e preserva o vínculo inativo no histórico. Comodatos encerrados ficam somente para consulta. Não há devolução parcial com data individual nesta versão.

## Estrutura

- `app.php`: entrada autenticada e coordenação das requisições.
- `app/bootstrap.php`: conexão PDO, sessão, escape HTML e proteção CSRF.
- `app/modules.php`: definição dos oito cadastros e seus campos.
- `app/services.php`: validação, persistência, disponibilidade, devolução e busca.
- `templates/`: layout, dashboard, listas e formulários compartilhados.
- `assets/`: CSS responsivo e JavaScript da área autenticada.
- `config/`: configuração local existente, preservada.
- `database/`: schema, migrações incrementais e execução por CLI.
- `tests/`: testes de integração e verificações HTTP locais.
- `legacy/`: cópias dos fluxos substituídos, bloqueadas pelo Apache.
- `old/` e `doc-comodatos/`: referências anteriores preservadas e bloqueadas para acesso web; o gerador de documentos antigo contém dados fixos e não foi integrado ao novo fluxo.

Entradas PHP antigas redirecionam para os módulos atuais. POSTs dos formulários antigos não são reaplicados: o redirecionamento abre o fluxo atual. Os HTMLs de demonstração, o `index.html`, scripts antigos e dependências Node foram preservados como referências; não participam do novo fluxo. O sistema atual não depende de Node/Express/MSSQL. Nenhuma dependência foi baixada.

## Banco e instalação

As credenciais continuam em `config/.env`. Nunca publique esse arquivo. O Apache bloqueia configuração, fontes internas, testes e legado.

Para um banco novo, execute `C:\xampp\php\php.exe database\setup.php` com uma conta de instalação autorizada e depois as migrações. Para um banco existente, execute somente `C:\xampp\php\php.exe database\migrate.php`. O migrador é idempotente: amplia o campo de senha, adiciona a data de devolução e cria o registro de trava transacional. Não remove registros.

A conta de execução configurada tem permissões limitadas. Para migrar, uma conta administrativa local pode ser fornecida ao processo pelas variáveis `GDC_MIGRATION_USER` e `GDC_MIGRATION_PASSWORD`, sem substituir as credenciais da aplicação. As migrações desta refatoração já foram aplicadas ao banco local `comodatos`.

Senhas novas usam `password_hash`. Hashes MD5 antigos são aceitos somente para compatibilidade e convertidos no próximo login bem-sucedido. A sessão armazena ID e token CSRF, sem senha. Contas inativas não acessam o sistema. Como o schema não define perfis, todos os usuários ativos têm acesso aos módulos administrativos, incluindo usuários.

A trava `app_write_lock` serializa as gravações da aplicação e impede atribuições concorrentes do mesmo item. Integrações futuras que escrevam diretamente no banco precisam respeitar a mesma regra. Datas usam America/Sao_Paulo no PHP; o MariaDB local deve manter o fuso compatível.

## Verificação local

`C:\xampp\php\php.exe tests\integration.php` cria um banco temporário com dados fictícios e o remove ao terminar. Usa uma conta local administrativa (`root` sem senha no XAMPP padrão); pode ser configurada por `GDC_TEST_DB_USER` e `GDC_TEST_DB_PASSWORD`. Não copia dados reais. Verifica cadastros, validações, filtros, renderização, bloqueio de empréstimo duplicado e devoluções. `GDC_TEST_PREVIEW=1` exporta opcionalmente um HTML fictício do dashboard para revisão visual; remova-o depois da revisão.

`C:\xampp\php\php.exe tests\http_smoke.php` verifica as páginas no Apache com uma sessão temporária de uma conta ativa, sem modificar cadastros. Precisa escrever na pasta de sessões do XAMPP. Verifica autenticação, CSRF, rotas e bloqueio dos arquivos internos.

Não há integração Sênior, geração de contratos, envio de e-mails, controle de permissões por perfil ou anexos implementados nesta etapa.
