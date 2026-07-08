<?php

// Regression: second ->not-> modifier after a type matcher must not be dropped
// expect($x)->not->toBeNull()->not->toBe($y) should remain unchanged
// because toBeNull is already the type check and the chain is already correct.
expect($storedSecret)->not->toBeNull()->not->toBe($setup->secret);
