## About this Project



## ✅ Project Setup Instructions

1. Clone the repository

For Local Machine:

```sh
git clone https://github.com/Mirza-Md-Golam-Nabi/shotabdi.git
```

For Live Server or cPanel:

```sh
git clone https://github.com/Mirza-Md-Golam-Nabi/shotabdi.git .
```

2. Goto project folder

```sh
cd shotabdi
```

3. Install dependencies using Composer

```sh
composer install
```

If you want to install it in cPanel, first check **composer** is install or not. For checking:

```sh
composer -v
```

If you see "**Composer Not Found**", you need to install Composer on your system.
Follow this guide [Composer install in cPanel](https://github.com/Mirza-Md-Golam-Nabi/tips/blob/master/laravel/composer/README.md#composer-install-in-cpanel-%EF%B8%8F)

4. Create the **.env** file

Copy the example environment file:

```sh
cp .env.example .env
```

5. Run this command:

```sh
php artisan key:generate
```

6. Create the database

Create a database named:

```sh
shotabdi
```

7. Run migrations and seeders

Run the following command to migrate and seed the database:

```sh
php artisan migrate --seed
```

8. Run the application

```sh
npm install
```

and

```sh
npm run build
```

and

```sh
php artisan serve
```

✅ When the seeder file is executed, a default user is created in the users table.
The login credentials are:

-   Mobile: 01825712671

-   Password: 12345

By default, these credentials will be pre-filled in the login form.

