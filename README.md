# Multi-Tenant E-Commerce API

## Description
This is a multi-tenant e-commerce API built using Laravel 10.

## Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/HebaKaddour/multi-tenant-api.git

 2.  Install dependencies:
   composer install
3. Configure the .env file with your database credentials.

4. Run migrations:
php artisan migrate

5. Start the development server:
php artisan serv


Testing:
To test the API, you can run the following commands:
php artisan test --filter=ProductTest
php artisan test --filter=AuthTest
php artisan test --filter=OrderTest
php artisan test --filter=MultiTenantWorkflowTest

API  documentation :
https://github.com/HebaKaddour/multi-tenant-api/blob/master/swagger.yaml




