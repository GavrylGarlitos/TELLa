# TELLa Online Store System

TELLa Online Store System is a PHP-based e-commerce website that allows customers to browse products, manage a shopping cart, place orders, and view delivery information. It also includes an administrator area for managing products, customers, and orders.

## Features

### Customer Features
- User registration and login
- Password reset
- Customer profile viewing and editing, including profile image upload
- Browse products and view product details
- Add products to a shopping cart and remove cart items
- Checkout individual items or the entire cart
- Select shipping and payment options during checkout
- View order and delivery information

### Administrator Features
- Admin-only access to management pages
- Add, view, and update products
- Upload product images
- View and manage customer accounts
- View and update customer orders
- Update shipping status, estimated delivery days, and order notes

## Built With

- **PHP** – server-side application logic
- **MySQL / MariaDB** – database
- **HTML** – page structure
- **CSS** – website styling
- **JavaScript** – client-side interactions
- **Font Awesome** – icons
- **Philippine Address Selector data** – region, province, city, and barangay selection

## Requirements

- A local PHP development environment such as **XAMPP**
- PHP
- MySQL or MariaDB
- A web browser
- phpMyAdmin (recommended for importing the database)

## Installation and Setup

1. **Download or clone the repository.**

   ```bash
   git clone <repository-url>
   ```

2. **Move the project folder** into your local server's web root. For XAMPP, this is usually:

   ```text
   C:/xampp/htdocs/
   ```

3. **Start Apache and MySQL** from the XAMPP Control Panel.

4. **Create and import the database.**
   - Open phpMyAdmin at `http://localhost/phpmyadmin`.
   - Create a database named `onlinestore`.
   - Select the database and import `onlinestore (Updated).sql`.

5. **Check the database connection settings.**

   The PHP files currently connect using the following local settings:

   ```php
   $mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
   ```

   If your MySQL username, password, host, or database name is different, update the connection settings in the PHP files accordingly.

6. **Open the website** in your browser. If the folder is named `TELLa Online Store System`, for example, visit:

   ```text
   http://localhost/TELLa%20Online%20Store%20System/Final%20Version/login.php
   ```

   Adjust the URL to match the folder name and location you use inside `htdocs`.

## Database

The included SQL file creates the `onlinestore` database structure. Its main tables are:

| Table | Purpose |
|---|---|
| `users` | Stores customer account and profile information |
| `admin_users` | Stores administrator account information |
| `products` | Stores product details, prices, quantities, and image paths |
| `orders` | Stores customer orders, payment details, and shipping information |

The SQL dump also contains sample records.

## Important Notes

- This project is configured for local development. Review and secure the database credentials, account handling, file uploads, and other settings before deploying it to a public server.
- The SQL dump contains sample account data. Change or remove default/sample credentials before using the project beyond a local demonstration.
- Keep the `uploads/` and `image/` folders in their expected locations because the website uses them to display uploaded images and site assets.
- The project does not document a live payment-gateway integration. The checkout page records the selected payment method in the order data.

## Contributors

This project was developed as a group project. 

- **Boticario, Chriz Jovennel**
- **Garlitos, Gavryl**
- **Gorospe, Paolo**
- **Isip, Franco Gerard**
- **Peñaranda, Philip Sebastian**


