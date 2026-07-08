<?php

// Skip single toHaveProperty with value (need at least 2)
expect($user)->toHaveProperty('name', 'Nuno');

// Skip toHaveProperty without value (just name check)
expect($user)->toHaveProperty('name');
expect($user)->toHaveProperty('email');

// Skip different subjects
expect($user)->toHaveProperty('name', 'Nuno');
expect($post)->toHaveProperty('title', 'Hello');
