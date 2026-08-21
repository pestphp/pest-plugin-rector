<?php

expect($payload)->json()->toHaveKey('name');

expect($payload)->toBe($raw);

?>

-----
<?php

expect($payload)->json()->toHaveKey('name')
    ->and($payload)->toBe($raw);

?>
