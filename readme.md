
## Installation


Setting up your development environment on your local machine. Kindly follow the below instruction 

```bash

$ git clone https://github.com/rajeshwws/lumen-product-management-.git
$ cd product-management
$ cp .env.example .env
$ composer install

```
Modify the `.env` file and update database detail.

Setup your local environment with Nginx and Nginx configuration file is present in root directory. 

After setting up, you can access the application via [http://saloodo.local](http://saloodo.local).


## Before starting
You need to run the migrations with the seeds :

Seeding the database :
```bash
$  php artisan migrate --seed
```

This will create two new user (Customer and Admin) that you can use to interact with the api :
```
// Admin Login detail
email: admin.rajesh@gmail.com
password: admin@1234

// Customer Login detail
email: user.rajesh@gmail.com
password: password
```
## Running test and listing end points

Running tests :
```bash
$  ./vendor/bin/phpunit --cache-result --order-by=defects --stop-on-defect --debug --coverage-text
```

List all available endpoints
```bash
$  php artisan route:list
```

## Accessing the API

Clients can access to the REST API. API requests require authentication via token. You can create a new token in your user profile.

Then, you can use this token in Authorization header :

```bash

# Authorization Header
curl --header "Authorization: your_private_token_here" http://saloodo.local/api/posts
```
All API endpoints are prefixed by ```api```.

Here is also a link to the [Postman Collection](https://www.getpostman.com/collections/df4aa9c47a7522e4e25d) to access a lot of the endpoints

You need to call the login endpoint with the appropriate user to get a valid `access token` that can be used

The table below shows the available endpoints and the level of permission attached to each one of them


```
+------+--------------------+-----------------+----------------------+
|Method| APIs end point     | Permissions     | Action               |
+------+--------------------+-----------------+----------------------+
| GET  | /                  | Unauthenticated | Framework info       |
| POST | /api/register      | Unauthenticated | Register             |
| POST | /api/login         | Unauthenticated | Login                |
| GET  | /api/products      | Customer        | Get all Products     |
| POST | /api/products      | Admin           | Create New Product   |
| POST | /api/products      | Admin           | Create Bundle Product|
| GET  | /api/products/{id} | Unauthenticated | Get product Details  |
| PUT  | /api/products/{id} | Admin           | Update Product       |
| GET  | /api/orders        | Customer        | Get all Orders       |
| GET  | /api/orders/{id}   | Customer        | Get Order details    |
| POST | /api/cart          | Customer        | Add products to cart |
| GET  | /api/cart          | Customer        | Get cart content     |
| POST | /api/cart/checkout | Customer        | Checkout cart        |
+------+--------------------+-----------------+----------------------+
```
