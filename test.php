<?php
// Instead of using something bloated like PHPUnit, let's just keep it simple.

// don't run file if accessed from web
if (php_sapi_name() != 'cli') {
    header('Location: index.php');
    exit;
}

// expose some functions to the test files
function describe($name, $fn) {
    echo "Running $name\n";
    $fn();
}

function assertEqual($a, $b) {
    if ($a !== $b) {
        $a_export = var_export($a, true);
        $b_export = var_export($b, true);
        throw new Exception("Assertion failed: $a_export !== $b_export");
    }
}

function assertNotEqual($a, $b) {
    if ($a === $b) {
        $a_export = var_export($a, true);
        $b_export = var_export($b, true);
        throw new Exception("Assertion failed: $a_export === $b_export");
    }
}

function assertThrows($fn) {
    try {
        $fn();
    } catch (Exception $e) {
        return;
    }
    throw new Exception("Assertion failed: expected exception");
}

// only run this file if it's the main file
if (get_included_files()[0] != __FILE__) {
    return;
}

// run all .test.php files
$files = glob(__DIR__ . '/*.test.php');
foreach ($files as $file) {
    echo "Running $file\n";
    $output = [];
    $return = 0;
    exec("php $file 2> /dev/null", $output, $return);
    
    if ($return != 0) {
        echo "Failed $file\n";
        echo implode("\n", $output);
        echo "\n";
        exit(1);
    }
    echo "Passed\n";
}