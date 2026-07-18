<?php

test('rewrites loose comparisons only for pinned subject types', function () {
    $flag = true;
    $name = 'nuno';

    expect($flag)->toEqual(true);
    expect($name === 'nuno')->toEqual(false);
    expect(mb_substr($name, 0, 0))->toBe('');
});

?>
-----
<?php

test('rewrites loose comparisons only for pinned subject types', function () {
    $flag = true;
    $name = 'nuno';

    expect($flag)->toBeTrue();
    expect($name === 'nuno')->toBeFalse();
    expect(mb_substr($name, 0, 0))->toBeEmpty();
});

?>
