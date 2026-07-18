<?php

test('skips lookalike chains', function () {
    $users = [new User(), new User()];

    // "every item is not an X" cannot be expressed with toContainOnlyInstancesOf
    expect($users)->each->not->toBeInstanceOf(Admin::class);

    // no each modifier
    expect($users[0])->toBeInstanceOf(User::class);

    // wrong argument count
    expect($users)->each->toBeInstanceOf();
});
