Setup:\
1.clone the repository on your local\
2.run composer install in the root of your project\
3.change .env database credentials as per your setup and run php artisan optimize:clear\
4.run php artisan migrate\
5.open 2 terminals in the root of your project and run php artisan serve and php artisan queue:work\
\
Routes:\
api/posts (GET)\
api/posts/{id} (GET)\
api/posts/create (POST)\
api/posts/update (POST)\
api/posts/delete/{id} (DELETE)\
\
these routes will throw the required validation errors respectively.\
\
Testing:\
1.stop the running commands (if any)\
2.run php artisan migrate:fresh to clear out old data in the db\
3.open 2 terminals in the root of your project and run php artisan serve and php artisan queue:work\
4.run php artisan test\
