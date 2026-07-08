<?php

/** @noinspection ALL */

expect($post)->toBeInstanceOf(Post::class)
    ->and($user)->toBeInstanceOf(User::class)
    ->and($comment)->toBeInstanceOf(Comment::class);

expect($a)->toBeInt()->toBe(10);

expect($a)->not()->toBeNull();
expect($a)->not->toBeNull();

expect($a)->each()->toBeNull();
expect($a)->each->toBeNull();

expect($user->rooms)->toBeInstanceOf(Collection::class)->each->toBeInstanceOf(Room::class);
expect($user->rooms)->toBeInstanceOf(Collection::class)->each()->toBeInstanceOf(Room::class);

// Edge cases: expect without args or with multiple args
expect()->toBeTrue();
expect($a, $b)->toBe(true);

// Higher Order Expectations
expect(new \SplFileInfo(__FILE__))->getRealPath()->toBeString();
expect(new \SplFileInfo(__FILE__))->isDir()->toBeFalse();
