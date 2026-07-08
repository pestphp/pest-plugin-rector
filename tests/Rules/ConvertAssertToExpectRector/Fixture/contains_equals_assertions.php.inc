<?php

$this->assertContainsEquals(['id' => 1], $items);
$this->assertNotContainsEquals(['id' => 2], $items);

?>
-----
<?php

expect($items)->toContainEqual(['id' => 1]);
expect($items)->not->toContainEqual(['id' => 2]);

?>
