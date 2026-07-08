<?php

// Test: expect($x)->not->toBe()
expect($value)->not->toBe(5);
expect($value)->not->toEqual(10);

// Deep chain with ->not
expect($arr)->not->toContain('item');
