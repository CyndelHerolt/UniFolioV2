<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */
namespace App\Components\Trace\TypeTrace;

interface TraceInterface
{
    public function display(): string;
    public function save($form, $trace, $traceRepository, $traceRegistry, $existingContenu): array;
}
