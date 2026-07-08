<?php

$this->expectException(RuntimeException::class);
$this->expectExceptionCode(500);
doSomething();

$this->expectExceptionMessage('error');
doSomething();

$this->expectException(RuntimeException::class);
$this->expectExceptionMessageMatches('/error/');
doSomething();

$this->expectExceptionObject($exception);
$this->expectExceptionMessage('error');
doSomething();

?>
