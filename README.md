### CRM Paketur

Rest API CRM Application

### Teknologi yang Digunakan
- Laravel 11

### Persyaratan
- PHP 8.2 atau lebih tinggi
- Composer
- MySQL atau PostgreSQL

## Installation

Clone repositori project.

Jalankan composer install untuk menginstal dependensi project.
```bash
    composer install
```
Buat database dan jalankan php artisan migrate.
```bash
    php artisan migrate:fresh
```
Jalankan seeder
```bash
    php artisan db:seed
```
jalankan php artisan jwt:secret
```bash
    php artisan jwt:secret
```
Jalankan php artisan serve untuk memulai server web.
```bash
    php artisan serve
```
### Gunakan url berikut sebagai base url http://localhost:8000/api/

### API Documentation Postman

[API Docs](/crm-paketur.postman_collection.json)

[API Docs Env](/paketur%20-%20local.postman_environment.json)

## ERD
![alt landing-page](/erd.png)

## Test

Jalankan php artisan test untuk run test.
```bash
    php artisan test
```


