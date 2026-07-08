<?php

it('keeps inline comments when another fixture was processed first', function (): void {
    expect('rector')
        // preserve this explanation
        ->toBeString()
        ->toStartWith('r');
});

?>
-----
<?php

it('keeps inline comments when another fixture was processed first', function (): void {
    expect('rector')
        // preserve this explanation
        ->toBeString()
        ->toStartWith('r');
});

?>