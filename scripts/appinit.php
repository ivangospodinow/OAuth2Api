<?php
chdir(__DIR__ . '/../');

echo PHP_EOL;

echo 'Application is initting...' . PHP_EOL;

echo 'Checking .env.local file' . PHP_EOL;
if (!file_exists('.env.local')) {
    echo 'Creating .env.local file...' . PHP_EOL . PHP_EOL;
    copy('.env.local.example', '.env.local');
    echo '.env.local file created ' . (file_exists('.env') ? 'successfull' : 'failed') . '. Edit to enter database details.' . PHP_EOL;
} else {
    echo '.env.local file already created.' . PHP_EOL;
}

echo PHP_EOL;
