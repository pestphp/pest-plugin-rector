<?php

/**
 * @var string $password
 * @var string $string
 * @var string $text
 */
expect(strlen($string))->toBe(10);
expect(mb_strlen($text))->toBe(5);
expect(strlen($password))->toEqual(8);

?>
-----
<?php

/**
 * @var string $password
 * @var string $string
 * @var string $text
 */
expect($string)->toHaveLength(10);
expect($text)->toHaveLength(5);
expect($password)->toHaveLength(8);

?>
