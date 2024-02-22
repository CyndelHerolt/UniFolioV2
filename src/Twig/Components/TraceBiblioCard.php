<?php

namespace App\Twig\Components;

use App\Repository\TraceRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TraceBiblioCard
{
    public int $id;

    public function __construct(
        protected TraceRepository $traceRepository,
    )
    {
    }

    public function getTrace()
    {
        $trace = $this->traceRepository->find($this->id);

        return $trace;
    }
}
