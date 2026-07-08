<?php

// skip when one of them is `->not`
expect($user)->not->toHaveProperty('is_following');
expect($user)->toHaveProperty('is_follower');

// skip chain with on has `->not`
expect($user)->not->toHaveProperty('is_following')->toHaveProperty('is_follower');
