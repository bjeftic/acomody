<?php

namespace Deployer;

require 'recipe/laravel.php';

// ── Application ───────────────────────────────────────

set('application', getenv('APP_NAME') ?: 'acomody');
set('repository', getenv('GIT_REPO') ?: 'git@github.com:bjeftic/acomody.git');
set('git_tty', false);
set('keep_releases', 5);
set('shared_files', ['.env']);
set('shared_dirs', ['storage']);
set('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// ── Hosts ─────────────────────────────────────────────

// host('staging')
//     ->setHostname(getenv('SSH_HOST'))
//     ->setRemoteUser(getenv('SSH_USER') ?: 'deployer')
//     ->setIdentityFile('~/.ssh/id_ed25519_acomody_staging')
//     ->setDeployPath(getenv('DEPLOY_PATH') ?: '/var/www/acomody')
//     ->set('branch', 'main')
//     ->set('git_ssh_command', 'ssh -o StrictHostKeyChecking=no')
//     ->set('forward_agent', true);

host('production')
    ->setHostname(getenv('PROD_SSH_HOST'))
    ->setRemoteUser(getenv('PROD_SSH_USER') ?: 'deployer')
    ->setDeployPath(getenv('PROD_DEPLOY_PATH') ?: '/var/www/acomody.com')
    ->set('branch', 'main')
    ->set('forward_agent', false);

// ── Tasks ────────────────────────────────────────────

// Force git to use deployer's SSH key on the server
task('git:configure-ssh', function () {
    run('git config --global core.sshCommand "ssh -i /home/deployer/.ssh/id_ed25519_github -o StrictHostKeyChecking=no"');
});

before('deploy:update_code', 'git:configure-ssh');

desc('Upload built assets');
task('deploy:upload_assets', function () {
    upload('public/build/', '{{release_path}}/public/build/');
});

desc('Reload PHP-FPM');
task('php-fpm:reload', function () {
    run('sudo systemctl reload php8.4-fpm');
});

desc('Terminate Horizon (Supervisor će ga restartovati)');
task('horizon:restart', function () {
    run('php {{release_path}}/artisan horizon:terminate || true');
});

desc('Restart queue workers via Supervisor');
task('supervisor:restart', function () {
    run('sudo supervisorctl restart acomody-worker:*');
});

// ── Deploy flows ──────────────────────────────────────

// desc('Deploy to staging');
// task('deploy:staging', [
//     'deploy:info',
//     'deploy:setup',
//     'deploy:lock',
//     'deploy:release',
//     'deploy:update_code',
//     'deploy:shared',
//     'deploy:vendors',
//     'deploy:upload_assets',
//     'artisan:storage:link',
//     'artisan:config:cache',
//     'artisan:route:cache',
//     'artisan:view:cache',
//     'artisan:event:cache',
//     'artisan:migrate',
//     'deploy:publish',
//     'php-fpm:reload',
//     'horizon:restart',
// ]);

desc('Deploy to production');
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
    'artisan:event:cache',
    'artisan:migrate',
    'deploy:publish',
    'php-fpm:reload',
    'supervisor:restart',
    'horizon:restart',
]);

after('deploy:failed', 'deploy:unlock');