# Green Store Project

![Green Store Logo](shared-core/assets/titlelog.png)

Simple eCommerce web app using PHP, MySQL and Tailwind CSS.

## Key Features

- Featured products and curated storefront
- Product detail pages with full descriptions and images
- Shopping cart and checkout with order confirmation
- Admin dashboard: add/edit/delete products + image uploads
- Responsive UI (mobile-first, Tailwind CSS)

## Setup Instructions

1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Create a database and run the SQL schema located in `db/schema.sql` to set up the necessary tables.
4. Update the database connection settings in `includes/config.php` with your database credentials.
5. Open `index.php` in your web browser to access the application.

## Usage Guidelines

- Browse products on the homepage.
- Click on a product to view its details and add it to your cart.
- Access the shopping cart to review selected items and proceed to checkout.
- Open `/admin/login.php` to manage products.
- Log in as a customer to add items to the cart or checkout.

## Configuration (`shared-core/config.php`)

- Purpose: centralizes database credentials, OAuth settings, and payment/bank details used by both the user and admin sites.
- Security: do NOT commit live credentials. The file `shared-core/config.php` is already included in `.gitignore`.

Below is a safe example you can copy into `shared-core/config.php` and then fill in with your real values. Keep the real file out of source control.

```php
<?php


$db_host = 'DB_HOST_HERE';
$db_user = 'DB_USER_HERE';
$db_pass = 'DB_PASS_HERE';
$db_name = 'DB_NAME_HERE';

$db_config = [
	'db_host' => $db_host,
	'db_user' => $db_user,
	'db_pass' => $db_pass,
	'db_name' => $db_name,


	'admin_username' => 'admin',
	'admin_password' => 'admin123',
	'admin_password_hash' => null,


	'google_client_id' => 'GOOGLE_CLIENT_ID',
	'google_client_secret' => 'GOOGLE_CLIENT_SECRET',
	'google_redirect_uri' => 'https://yourdomain.example.com/user-site/pages/google_callback.php',


	'bank_name' => 'Your Bank',
	'bank_account_name' => 'Account Name',
	'bank_account_number' => '0000000000',
	'bank_branch' => 'Branch Name',

	'payhere_merchant_id' => 'PAYHERE_MERCHANT_ID',
	'payhere_merchant_secret' => 'PAYHERE_MERCHANT_SECRET',
];

return $db_config;
```

Follow-up notes:

- Replace placeholders with your actual database and OAuth credentials before deploying.
- Update the `google_redirect_uri` in Google Cloud Console to match your live domain callback.
- On InfinityFree, use the database credentials provided in the control panel and set `google_redirect_uri` to your InfinityFree domain.
- Make sure `shared-core/uploads/` exists and is writable for product images and receipts.

## Technologies Used

- PHP for server-side scripting.
- MySQL for database management.
- HTML and Tailwind CSS for front-end design.

## Live Demo

[🛒 Visit Store](https://greenstrore.ct.ws/user-site/) &nbsp; [🔐 Admin Login](https://greenstrore.ct.ws/admin-site/admin/login.php)


