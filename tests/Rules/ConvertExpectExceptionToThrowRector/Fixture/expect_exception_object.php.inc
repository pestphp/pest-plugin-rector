<?php

$this->expectExceptionObject($exception);
doSomething();

?>
-----
<?php

expect(fn() => doSomething())->toThrow($exception);
