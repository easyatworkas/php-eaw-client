<?php

use Eaw\Models\Model;
use Psy\Configuration;
use Psy\Shell;
use Symfony\Component\VarDumper\Caster\Caster;

require(__DIR__ . '/bootstrap/bootstrap.php');

// TODO: Move this to Psy commands?
(function () {
    if (method_exists($eaw = eaw(), $command = $_SERVER['argv'][1] ?? null)) {
        echo json_encode(call_user_func_array([ $eaw, $command ], array_slice($_SERVER['argv'], 2)), JSON_PRETTY_PRINT);
        exit;
    }
})();

$config = new Configuration();
$config->getPresenter()->addCasters([
    Model::class => function (Model $model) {
        $result = [];

        foreach ($model->getAttributes() as $key => $value) {
            $result[Caster::PREFIX_VIRTUAL . $key] = $value;
        }

        return $result;
    }
]);

$shell = new Shell($config);
$shell->setIncludes($_SERVER['argv']);
$shell->run();
