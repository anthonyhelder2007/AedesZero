<?php
declare(strict_types=1);

// Ponto de entrada na raiz do projeto (para hospedagens onde o site abre em htdocs/).
// Redireciona para a página inicial dentro de public/.
header('Location: public/index.php');
exit;
