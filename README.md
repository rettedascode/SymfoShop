# SymfoShop - E-commerce Platform

A modern e-commerce platform built with Symfony 7.3, featuring a complete online shopping experience with user management, product catalog, shopping cart, order processing, and admin panel.

## 🚀 Features

### Customer Features
- **Product Catalog**: Browse products with search, filtering, and sorting
- **Shopping Cart**: Add/remove items with real-time feedback
- **User Authentication**: Registration, login, and profile management
- **Checkout Process**: Complete order placement with address management
- **Order History**: View past orders with detailed order pages
- **Product Reviews**: Rate and review products with interactive rating system
- **Category Browsing**: Dedicated categories page with product counts

### Admin Features
- **EasyAdmin Panel**: Manage products, orders, users, and configuration
- **Product Management**: Add, edit, and manage products and categories
- **Order Management**: Process and track customer orders
- **Configuration System**: Database-driven configuration for easy customization
- **Sample Data**: Built-in commands for creating sample data

### Technical Features
- **Symfony 7.3**: Latest framework with best practices
- **Doctrine ORM**: Advanced database management
- **Webpack Encore**: Modern asset compilation
- **Docker Support**: Containerized development environment
- **Responsive Design**: Mobile-friendly interface with Bootstrap 5
- **Enhanced UX**: Loading states, tooltips, and smooth animations

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 16 or higher
- **MySQL**: 8.0 or higher
- **Docker & Docker Compose** (optional)

## 🛠️ Installation

### Local Development Setup

1. **Clone and setup**
   ```bash
   git clone <repository-url>
   cd SymfoShop
   composer install
   npm install
   ```

2. **Configure environment**
   ```bash
   cp .env .env.local
   # Edit .env.local with your database settings
   ```

3. **Setup database**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

4. **Build assets and initialize**
   ```bash
   npm run build
   php bin/console app:setup
   ```

5. **Start development server**
   ```bash
   symfony server:start
   ```

### Docker Setup

1. **Start containers**
   ```bash
   docker-compose up -d
   ```

2. **Run setup script**
   ```bash
   # Windows
   docker\deploy.bat
   
   # Linux/macOS
   docker/deploy.sh
   ```

3. **Access the application**
   - Frontend: http://localhost
   - Admin Panel: http://localhost/admin
   - Default admin: admin@example.com / password

## 🎯 Quick Start

### Application Setup Commands

```bash
# Initialize everything (config + sample data)
php bin/console app:setup

# Initialize only configuration
php bin/console app:setup --skip-sample-data

# Initialize only sample data
php bin/console app:setup --skip-config

# Clear all data
php bin/console app:clear-all-data --force
```

### Key URLs

- **Homepage**: `/`
- **Products**: `/products`
- **Categories**: `/categories`
- **Admin Panel**: `/admin`
- **User Profile**: `/profile`

## 🏗️ Project Structure

```
src/
├── Controller/          # Application controllers
├── Entity/             # Doctrine entities
├── Repository/         # Data access layer
├── Service/           # Business logic services
├── Command/           # Console commands
└── Twig/              # Twig extensions

templates/              # Twig templates
├── base.html.twig     # Base template
├── product/           # Product templates
├── order/             # Order templates
└── admin/             # Admin templates

config/                 # Configuration files
docker/                 # Docker configuration
tests/                  # PHPUnit tests
```

## ⚙️ Configuration

The application uses a database-driven configuration system accessible via the admin panel:

- **Shop Settings**: Name, description, contact information
- **Currency**: Currency code and symbol
- **System Settings**: Various application parameters

## 🧪 Testing

```bash
# Run all tests
php bin/phpunit

# Run specific test suites
php bin/phpunit --testsuite=Unit
php bin/phpunit --testsuite=Integration

# Run with coverage
php bin/phpunit --coverage-html var/coverage
```

## 🚀 Deployment

### Production Deployment

1. **Environment setup**
   ```bash
   APP_ENV=prod
   composer install --no-dev --optimize-autoloader
   npm run build
   ```

2. **Database setup**
   ```bash
   php bin/console doctrine:migrations:migrate --env=prod
   php bin/console app:setup --env=prod
   ```

3. **Cache warmup**
   ```bash
   php bin/console cache:warmup --env=prod
   ```

### Docker Production

```bash
docker-compose -f docker-compose.prod.yml up -d
```

## 📝 License

This project is licensed under the MIT License.

## 🤝 Support

For support and questions:
- Create an issue in the repository
- Check the documentation in the `docs/` folder
- Review the configuration options in the admin panel

---

**SymfoShop** - Modern e-commerce made simple with Symfony 7.3
