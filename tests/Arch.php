<?php

declare(strict_types=1);

use Pest\Rector\AbstractRector;
use Pest\Rector\Rules\AbstractStrCaseRector;

arch()->preset()->php();

arch('rules are final')
    ->expect('Pest\Rector\Rules')
    ->classes()
    ->toBeFinal()
    ->ignoring(AbstractStrCaseRector::class);

arch('rules extend the abstract rector')
    ->expect('Pest\Rector\Rules')
    ->classes()
    ->toExtend(AbstractRector::class);

arch('source uses strict types')
    ->expect('Pest\Rector')
    ->toUseStrictTypes();
