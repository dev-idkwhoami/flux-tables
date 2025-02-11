<?php

use Idkwhoami\FluxTables\Concretes\Column\TextColumn;

test('example', function () {

    $column = (new TextColumn("test"))
        ->property('test_property');

    expect($column)->toBeInstanceOf(TextColumn::class)
        ->toHaveProperty('property');

});
