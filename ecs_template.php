<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $vendorDir = __DIR__ . '/vendor';

    if (!is_dir($vendorDir)) {
        $vendorDir = __DIR__ . '/../../vendor';
    }

    $containerConfigurator->import($vendorDir . '/contao/easy-coding-standard/config/template.php');
};
