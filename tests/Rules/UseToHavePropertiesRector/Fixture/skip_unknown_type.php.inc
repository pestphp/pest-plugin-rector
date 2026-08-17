<?php

// The subject has no resolvable type, so the rewrite is not proven safe and the
// rule must leave the code alone.
expect($user)->toHaveProperty('name')->toHaveProperty('email');
