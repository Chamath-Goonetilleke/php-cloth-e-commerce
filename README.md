# OneFit Clothing - E-Commerce Website

OneFit Clothing is a stylish e-commerce platform for a clothing brand, featuring a modern responsive frontend and a robust PHP/MySQL backend.

## Project Structure

The project follows a clean separation between frontend and backend components:

```
onefitclothing/
├── admin/                  # Admin dashboard and management
├── assets/                 # Frontend assets
│   ├── css/                # CSS styles
│   ├── js/                 # JavaScript files
│   └── images/             # Images (products, banners, etc.)
├── includes/               # PHP include files
│   ├── config.php          # Database connection and site constants
│   ├── auth.php            # Authentication functions
│   ├── header.php          # Common header template
│   └── footer.php          # Common footer template
├── index.php               # Homepage
├── product.php             # Individual product page
├── cart.php                # Shopping cart
├── tshirts.php             # T-shirts category page
├── hoodies.php             # Hoodies category page
├── cart-update.php         # Cart operations (add, update, remove)
├── install.php             # Installation script
└── README.md               # Project documentation
```

## Features

- Responsive frontend design that maintains the original styling
- Product catalog with categories
- Individual product pages with details and images
- Shopping cart functionality
- User authentication and account management
- Admin panel for product management
- Search functionality
- Sale and discount support
- Product filtering and sorting
- Product reviews

## Installation Instructions

1. **Prerequisites**:

   - Web server (Apache/Nginx)
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - MAMP, XAMPP, or similar local development environment

2. **Setup**:

   - Clone or extract the project files to your web server's document root
   - Create a MySQL database for the project
   - Update database connection details in `includes/config.php` if needed
   - Run the installation script by visiting `http://yoursite.com/install.php`

3. **Default Admin Login**:
   - Username: `admin`
   - Password: `admin123`

## Backend Structure

The backend is built with PHP and follows a structured approach:

- **Authentication**: User login, registration, and password management
- **Database**: MySQL database with tables for users, products, categories, orders, etc.
- **Product Management**: Add, edit, and delete products; manage categories
- **Order Processing**: Order tracking, status updates, and history
- **User Management**: User profiles, addresses, and order history

## Frontend Technologies

- HTML5
- CSS3
- JavaScript
- Responsive design that works on mobile, tablet, and desktop
- Uses CSS Grid and Flexbox for layouts
- Font Awesome for icons

## Customization

You can customize the website by:

1. Modifying the CSS in `assets/css/style.css`
2. Updating site constants in `includes/config.php`
3. Editing template files in the `includes` directory
4. Adding new product categories in the admin panel

## License

This project is intended for educational and demonstration purposes.

## Author

OneFit Clothing E-commerce Platform
