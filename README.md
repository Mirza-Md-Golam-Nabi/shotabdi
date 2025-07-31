## About this Project



## ✅ Project Setup Instructions

1. Clone the repository

```sh
git clone https://github.com/Mirza-Md-Golam-Nabi/shotabdi.git
```

2. Goto project folder

```sh
cd shotabdi
```

3. Install dependencies using Composer

```sh
composer install
```

3. Create the **.env** file

Copy the example environment file:

```sh
cp .env.example .env
```

4. Run this command:

```sh
php artisan key:generate
```

4. Create the database

Create a database named:

```sh
shotabdi
```

5. Run migrations and seeders

Run the following command to migrate and seed the database:

```sh
php artisan migrate --seed
```

6. Run the application

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

