<?php

expect($items)->toHaveCount(3);

expect($items)->each->toBeString();

?>

-----
<?php

expect($items)->toHaveCount(3)->each->toBeString();

?>
