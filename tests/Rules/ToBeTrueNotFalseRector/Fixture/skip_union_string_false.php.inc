<?php

test('skips a string|false union subject', function (string $haystack): void {
    expect(mb_strstr($haystack, 'needle'))->not->toBeFalse();
});

?>
