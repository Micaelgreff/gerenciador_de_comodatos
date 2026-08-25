<?php

define ('HOSTCOMODATO', '127.0.0.1');
define ('USUARIOCOMODATO', 'host_api');
define ('SENHACOMODATO', 'teste123!@#');
define ('DBCOMODATO', 'comodatos');

$concomodato = mysqli_connect(HOSTCOMODATO, USUARIOCOMODATO, SENHACOMODATO, DBCOMODATO) or die('Não foi possível conectar');

?>