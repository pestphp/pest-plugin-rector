<?php

expect($guard->shouldDispatch())->toBeTrue();
expect($guard->shouldDispatch())->toBeFalse();
