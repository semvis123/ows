<?php

include 'test.php';

describe('Assertions should work', function() {
    assertEqual('1', '1');
    assertNotEqual('1', '2');
    assertThrows(function() {
        throw new Exception('test');
    });
});