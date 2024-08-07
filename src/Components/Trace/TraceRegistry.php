<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */
namespace App\Components\Trace;

use App\Components\Trace\TypeTrace\AbstractTrace;

class TraceRegistry
{
    public const TAG_TYPE_TRACE = 'trace.type';

    private array $typesTrace = [];
    private array $typesTracesForm = [];

    public function registerTypeTrace($name, AbstractTrace $typeTrace): void
    {
        $this->typesTrace[$name] = $typeTrace;
        $this->typesTracesForm['trace_'.$typeTrace::TYPE] = $typeTrace;
    }

    public function getTypeTrace($name): AbstractTrace
    {
        return $this->typesTrace[$name];
    }

    public function getTypeTraceFromForm($name): AbstractTrace
    {
        return $this->typesTracesForm[$name];
    }

    public function getTypeTraces(): array
    {
        return $this->typesTrace;
    }
    public function getTypeName()
    {
        return $this->typesTrace;
    }

    public function getTypeTypeTrace(?string $name): mixed
    {
        return $this->typesTrace[$name];
    }

    public function getForm(AbstractTrace $typeTrace): mixed
    {
        $formClass = $typeTrace::FORM;
        return new $formClass();
    }

//    public function getFormType($name): mixed
//    {
//        return $this->typesTrace[$name]::FORM;
//    }
}