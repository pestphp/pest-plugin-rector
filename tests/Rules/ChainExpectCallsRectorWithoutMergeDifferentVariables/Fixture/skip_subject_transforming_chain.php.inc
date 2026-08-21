<?php

expect($array)->each(function ($element): void {
    $element->value->toBeString();
});

expect($array)->toMatchArray([
    //
]);
