<?php

$draft = getDraft();
$riskAssessment = getRiskAssessment();

expect($draft)
    ->status->toBe('draft')
    ->approved_at->toBeNull()
    ->riskGroups->toHaveCount(2)
    ->and($riskAssessment->status)->toBe('approved');

?>
-----
<?php

$draft = getDraft();
$riskAssessment = getRiskAssessment();

expect($draft)
    ->status->toBe('draft')
    ->approved_at->toBeNull()
    ->riskGroups->toHaveCount(2);
expect($riskAssessment->status)->toBe('approved');

?>
