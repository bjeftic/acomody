<?php

namespace Deployer;

require 'recipe/laravel.php';

set('application', 'acomody');
set('repository', 'git@github.com:bjeftic/acomody.git');
set('git_tty', false);
set('keep_releases', 5);
set('shared_files', ['.env']);
set('shared_dirs', ['storage']);
set('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/framework',
    'storage/logs',
]);

host('staging')
    ->setHostname(getenv('SSH_HOST') ?: '138.68.94.120')
    ->setRemoteUser('deployer')
    ->setIdentityFile('~/.ssh/id_ed25519_acomody_staging')
    ->setDeployPath('/var/www/acomody')
    ->set('branch', 'main')
    ->set('git_ssh_command', 'ssh -o StrictHostKeyChecking=no')
    ->set('forward_agent', true);

// ── Tasks ────────────────────────────────────────────

desc('Upload built assets');
task('deploy:upload_assets', function () {
    upload('public/build/', '{{release_path}}/public/build/');
});

desc('Restart PHP-FPM');
task('php-fpm:restart', function () {
    run('sudo systemctl reload php8.3-fpm');
});

desc('Restart Horizon');
task('horizon:restart', function () {
    run('sudo systemctl restart horizon');
});

// ── Deploy flow ───────────────────────────────────────

desc('Deploy Acomody');
task('deploy', [
    'deploy:info',
    'deploy:setup',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:upload_assets',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:migrate',
    'deploy:publish',
    'php-fpm:restart',
    'horizon:restart',
]);

after('deploy:failed', 'deploy:unlock');