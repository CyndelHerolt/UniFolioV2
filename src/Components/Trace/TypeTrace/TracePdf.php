<?php

namespace App\Components\Trace\TypeTrace;

use App\Components\Trace\Form\TracePdfType;
use App\Components\Trace\TypeTrace\AbstractTrace;
use App\Repository\TraceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TracePdf extends AbstractTrace
{
    public const TYPE = 'pdf';

    public const FORM = TracePdfType::class;

    public const FORM_TEMPLATE = 'trace/form_types/_form_pdf.html.twig';

    public const ICON = 'bi:file-pdf-fill';

    public const CONSTRAINT = 'Poids maximum : 8 Mo';

    public const CLASS_NAME = self::class;

    public ?string $name = 'pdf';

    public array $options = [];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('form', $this::FORM)
            ->setDefault('type_trace', $this->name);
    }

    public function display(): string
    {
        return self::TYPE;
    }

    public function sauvegarde(?array $contenu): array
    {
        $max_size = 8 * 1024 * 1024; // 8 Mo en octets

        if ($contenu) {
            foreach ($contenu as $pdf) {
                if ($pdf->getSize() > $max_size) {
                    return ['success' => false, 'error' => 'Le fichier doit faire 8mo maximum'];
                } elseif (!in_array($pdf->guessExtension(), ['pdf'])) {
                    return ['success' => false, 'error' => 'Le contenu n\'est pas un pdf valide'];
                }
                $fileName = uniqid() . '.' . $pdf->guessExtension();
                $pdf->move($_ENV['PATH_FILES'], $fileName);
                $content[] = $_ENV['SRC_FILES'] . '/' . $fileName;
            }
        } else {
            return ['success' => false, 'error' => 'Le contenu est vide'];
        }

        return ['success' => true, 'contenu' => $content];
    }
}