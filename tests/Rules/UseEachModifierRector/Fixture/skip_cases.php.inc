<?php

// Skip non-expect chains
$result = each($array, fn ($v) => dump($v));

// Skip non-each methods
expect($array)->toBeArray();

// Skip when not expect argument
$value = 10;

// Skip when the chain uses the foreach key - each has no equivalent
foreach ($users as $name => $user) {
    expect($user)->toHaveKey('name', $name);
}
