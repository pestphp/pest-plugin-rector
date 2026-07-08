<?php

$this->expectException(RuntimeException::class);
doSomething();

?>
-----
<?php

expect(fn() => doSomething())->toThrow(RuntimeException::class);
