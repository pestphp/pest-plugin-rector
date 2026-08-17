<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect($user)->toHaveProperty('name', 'Nuno');
expect($user)->toHaveProperty('email', 'nuno@example.com');
