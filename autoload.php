<?php

spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'idealo\\MOAv2\\';
    $prefixLength = strlen($prefix);

    // base directory for the namespace prefix
    $baseDir = __DIR__.'/idealo/MOAv2/';

    // does the class use the namespace prefix?
    if (strncmp($prefix, $class, $prefixLength) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relativeClass = substr($class, $prefixLength);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});