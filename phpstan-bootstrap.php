<?php

$path = __DIR__.'/';

for($i=1; $i < 10; $i++){
    if(file_exists($path.'/vendor/autoload.php')){
        define('PIMCORE_PROJECT_ROOT', $path);
        break;
    }else{
        $path = $path.'../';
    }
}

include PIMCORE_PROJECT_ROOT . '/vendor/autoload.php';
\Pimcore\Bootstrap::setProjectRoot();
\Pimcore\Bootstrap::bootstrap();

if (!defined('PIMCORE_TEST')) {
    define('PIMCORE_TEST', true);
}
