# TV

## Install

- Adjust root docker-compose `.env` file.
- Run docker-compose.

```sh
export UID # Used to run php-fpm with host user's file permissions
docker-compose up --build
```

- Copy `src/resources/ts/config.example.ts` to the `src/resources/ts/config.ts` and configure frontend.
- Install dependencies and build frontend.

```sh
cd src
cp resources/ts/config.example.ts resources/ts/config.ts
yarn install
yarn dev
```

- Log into the php-fpm container.
- Copy `.env.example` to the `.env`.
- Install PHP dependencies.
- Generate key and apply database migrations.

```sh
cd ..
export UID
docker-compose exec php-fpm sh
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```
