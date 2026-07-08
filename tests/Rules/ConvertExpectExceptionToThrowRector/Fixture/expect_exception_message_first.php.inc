<?php

$this->expectExceptionMessage('error');
$this->expectException(RuntimeException::class);
doSomething();

?>
-----
<?php

expect(fn() => doSomething())->toThrow(RuntimeException::class, 'error');
