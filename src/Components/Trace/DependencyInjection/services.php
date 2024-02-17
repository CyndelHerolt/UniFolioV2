<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */

namespace App\Components\Trace\DependencyInjection;

use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $services->defaults()
        ->private()
        ->autowire()
        ->autoconfigure(false);

    $services->set(TracePdf::class)->tag(TraceRegistry::TAG_TYPE_TRACE);
    $services->set(TraceVideo::class)->tag(TraceRegistry::TAG_TYPE_TRACE);
    $services->set(TraceImage::class)->tag(TraceRegistry::TAG_TYPE_TRACE);
    $services->set(TraceLien::class)->tag(TraceRegistry::TAG_TYPE_TRACE);
};
