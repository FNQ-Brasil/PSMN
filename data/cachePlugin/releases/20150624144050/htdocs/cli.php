<?php
/**
 * ZEND FRAMEWORK client
 * script php cli que recebe parametro de ambiente e nome do modulo/controler/action e a executa.
 *
 * Para executar:
 *
 * > php cli.php -e ambiente -a modulo.controller.action
 *
 * -e é opcao que define o ambiente (dev, homolog, producao) que o script vai rodar
 * -a é opcao que define a action que sera executada, no formato: modulo.controller.action
 * -l Limita quantidade de mensagens disparadas por execucao
 *
 * Exemplo:
 * 
 * sudo php cli.php -e development -a cli.queue.disparaemailemespera -l 10
 * 
 */

$c = count($argv);

if ($c <= 1) {

    echo "

   $ php cli.php -e ambiente -a modulo.controller.action
 
   -e é opcao que define o ambiente (dev, homolog, producao) que o script vai rodar
   -a é opcao que define a action que sera executada, no formato: modulo.controller.action
   -l Limita quantidade de mensagens disparadas por execucao

   Exemplo: 
   php cli.php -e development -a cli.queue.disparaemailemespera -l 10
        
";
    
    exit;
}


set_time_limit(0);

error_reporting(E_ERROR | E_WARNING | E_PARSE );

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_PATH_CACHE')
    || define('APPLICATION_PATH_CACHE', (getenv('APPLICATION_PATH_CACHE') ?
        getenv('APPLICATION_PATH_CACHE') : APPLICATION_PATH . '/../data'));

defined('APPLICATION_PATH_LIBS')
    || define('APPLICATION_PATH_LIBS', (getenv('APPLICATION_PATH_LIBS') ?
        getenv('APPLICATION_PATH_LIBS') : APPLICATION_PATH . '/../premium-libs'));

/*
 * parametro para ambiente sera variavel APPLICATION_ENV e tambem é utlizado pelo bootstrap (em uma funcao legada, porem ainda utilizada).
 */
$parametro_ambiente = $argv[2];

define('APPLICATION_ENV', $parametro_ambiente);

// Define application environment
$opts = getopt('e:');
$env = $opts['e'];

/**
 * getenv nao funciona no php cli por isso, abaixo, APPLICATION_ENV é 'development'
 */

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH_LIBS), get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';


/**
 * Em Vtx_Plugin_Permission
 * se CLI_APP esta definido, nao carrega ACL
 */
define('CLI_APP', true);

//echo "APPLICATION_PATH:" .APPLICATION_PATH."\n";
//echo "\nCLI_APP: " .CLI_APP."\n";

define('AMBIENTE_CLI',$env);

//echo "\nAMBIENTE_CLI: ".AMBIENTE_CLI."\n";

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

try {
    $opts = new Zend_Console_Getopt(
        array(
            'action|a=s' => 'Action to perform in format of module.controller.action', // parametro que define a action que sera executada, ex. abaixo
            'environment|e=s' => 'Parametro que define o ambiente da aplicação', //ex: php cli.php -e homologacao_cli -a financeiro.script-debito-dois.index
            'execucao-forcada|f=s' => 'Caso tb execucao_script tenha bloqueado a execucao do script, este para parametro permite executar (bypass)',
            'limit|l=s' => 'Limita quantidade de mensagens disparadas por execucao',
        )
    );
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    exit($e->getMessage() ."\n\n". $e->getUsageMessage());
}


    $reqRoute = array_reverse(explode('.', $opts->a));
    @list($action, $controller, $module) = $reqRoute;

    //echo  "\n\n module: " . $module . " controller: " . $controller . " action: " . $action ;

    $forca_execucao = $opts->f;
    //var_dump('forca_execucao: ',$forca_execucao);
    if ($forca_execucao == 'sim') {
        //echo "definiu com true!";
        define ('EXECUCAO_FORCADA', true);
    }

    //echo "\nparametro 'e'".$opts->e;
    
    $front = Zend_Controller_Front::getInstance();
    $module = $module ? $module : $front->getDefaultModule();

    $request = new Zend_Controller_Request_Simple($action, $controller, $module);
    $front->setRequest($request);
    $front->setRouter(new Vtx_RouterCli());
    $front->setResponse(new Zend_Controller_Response_Cli());

    $application->bootstrap()->run();

/**
 * erro
 * nao chamar este script no PHP via Cron com as opcoes: -a ou -f
 * @example
 * > php -a
 * > php -f
 *
 * todos os require ou includes do php cli nao sao carregados.
 */

    