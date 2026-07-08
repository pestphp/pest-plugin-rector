<?php

expect($value)->not->toBeFalse();
expect($value)->not->toBe(10);

?>
-----
<?php

expect($value)->not->toBeFalse()->not->toBe(10);

?>
