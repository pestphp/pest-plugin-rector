<?php

$this->expectException(RuntimeException::class);
$this->expectExceptionMessage('error');
doSomething();

?>
-----
<?php

expect(fn() => doSomething())->toThrow(RuntimeException::class, 'error');
