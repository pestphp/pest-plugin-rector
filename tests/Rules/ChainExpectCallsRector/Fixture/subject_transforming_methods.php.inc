<?php

expect($array)->each(function ($element): void {
    $element->value->toBeString();
});

expect($array)->toMatchArray([
    //
]);

?>

-----
<?php

expect($array)->each(function ($element): void {
    $element->value->toBeString();
})
    ->and($array)->toMatchArray([
    //
]);

?>
