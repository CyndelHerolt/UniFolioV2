<?php

namespace App\Twig\Components;

use App\Components\Trace\TraceRegistry;
use App\Repository\TraceRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TraceCard
{
    public int $id;

    public string $type = '';

    public int $page;

    public function __construct(
        protected TraceRepository $traceRepository,
        protected TraceRegistry $traceRegistry,
    )
    {
    }

    public function getTrace()
    {
        $trace = $this->traceRepository->find($this->id);
        $type = $trace->getType();
        $this->type = $this->traceRegistry->getTypeTrace($type)::TYPE;

        return $trace;
    }
}
