<?php

// skip when one of them is `->not`
expect($user)->not->toHaveKey('is_following');
expect($user)->toHaveKey('is_follower');

// skip chain with on has `->not`
expect($user)->not->toHaveKey('is_following')->toHaveKey('is_follower');
