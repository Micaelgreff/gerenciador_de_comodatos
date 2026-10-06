# Organização do legado

Os arquivos desta pasta são cópias preservadas dos pontos de entrada substituídos. Não são carregados pela aplicação atual e o `.htaccess` impede execução e leitura pelo Apache. Podem conter SQL desatualizado, conexões antigas e dados fixos: servem somente para consulta local durante a refatoração.

As versões atuais das rotas antigas são redirecionamentos para `app.php`. O antigo `old/` e o protótipo `doc-comodatos/` continuam em suas pastas, também bloqueados para web. Os documentos e as bibliotecas PHP foram preservados. Recursos de frontend sem uso na aplicação atual foram removidos; estas cópias servem como referências de código, sem garantia de execução ou aparência original.
