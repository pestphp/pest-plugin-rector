<?php

expect($guard->shouldDispatch())->toBeTrue();
expect($guard->shouldDispatch())->toBeFalse();

expect("{$guard->shouldDispatch()}")->toBe('1');
expect("{$guard->shouldDispatch()}")->toBe('');
