<?php

// Test case: non-identifier method name (dynamic method call)
// This should NOT be transformed since ->$method() can't be detected as 'only'
$method = 'only';
test('description', function () {
    expect(true)->toBeTrue();
})->$method();

?>
-----
<?php

// Test case: non-identifier method name (dynamic method call)
// This should NOT be transformed since ->$method() can't be detected as 'only'
$method = 'only';
test('description', function () {
    expect(true)->toBeTrue();
})->$method();

?>
