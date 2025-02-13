Copy the example environment file:

cp .env.example .env

1. Start Docker Containers

   docker-compose up -d --build

2. Install Dependencies

   docker-compose exec app composer install

3. Generate the Application Key

   docker-compose exec app php artisan key:generate

4. Composer install

   composer install

5. Run Migrations

   docker-compose exec app php artisan migrate

6. Access the Application

   Laravel App: http://localhost:8080

   phpMyAdmin: http://localhost:8081

   Username: laravel_user

   Password: secret

7. Run Seeders

   docker-compose exec app php artisan db:seed

8. Swagger generate

   php artisan l5-swagger:generate 
