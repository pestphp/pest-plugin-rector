<?php

expect($items)->each->toBeString();

expect($items)->toHaveCount(3);

?>

-----
<?php

expect($items)->each->toBeString()
    ->and($items)->toHaveCount(3);

?>
