<?php

namespace App\Twig;

use PhpParser\Node\Stmt\Echo_;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AmountExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('amount', [$this, 'amount'])
        ];
    }

    public function amount($value, string $symbol = '€', string $decSep = ',', string $thousandSep = ' ')
    {
        // 19229 => 192,29 €
        $finalValue = $value / 100;
        // 192.29
        $finalValue = number_format($finalValue, 2, $decSep, $thousandSep);
        // 192,29

        return sprintf('%s %s', $finalValue, $symbol);
    }
}