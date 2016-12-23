@servers(['web' => 'root@cms.ege-repetitor.ru'])

@task('deploy')
    cd /home/egerep-web
    git pull github master
    php artisan config:cache
    php artisan route:cache
@endtask

@task('gulp')
    cd /home/egerep-web
    gulp --production
@endtask

@task('stash_and_pull')
    cd /home/egerep-web
    git stash
    git pull github master
@endtask

@task('cache')
    cd /home/egerep-web
    php artisan config:cache
    php artisan route:cache
@endtask

@task('migrate')
    cd /home/egerep-web
    php artisan migrate --force
@endtask

@task('stash')
    cd /home/egerep-web
    git stash
@endtask

@task('composer')
    cd /home/egerep-web
    composer update
@endtask
