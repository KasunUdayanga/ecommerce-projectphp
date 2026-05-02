# eCommerce Project

This is a simple eCommerce web application built using PHP, SQL, HTML, and Tailwind CSS. The project is designed to provide a basic online shopping experience.

## Features

- User-friendly homepage displaying featured products.
- Product detail pages with descriptions and pricing.
- Shopping cart functionality to manage selected items.
- Checkout page with order summary and shipping form.
- Admin dashboard to add, edit, and delete products.
- Admin product image uploads with automatic 4:3 cropping.
- Responsive design using Tailwind CSS.

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

## Admin Login

Use the default credentials (update them in `includes/config.php` after first login):

- Username: `admin`
- Password: `admin123`

## Customer Login

Use the default customer account created automatically:

- Email: `customer@example.com`
- Password: `customer123`

## Technologies Used

- PHP for server-side scripting.
- MySQL for database management.
- HTML and Tailwind CSS for front-end design.

## License

This project is open-source and available for modification and distribution.
