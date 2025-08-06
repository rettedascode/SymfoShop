# SymfoShop - E-commerce Platform

A modern e-commerce platform built with Symfony 7.3, featuring a complete online shopping experience with user management, product catalog, shopping cart, order processing, and admin panel. Enhanced with intuitive UX design and powerful configuration management.

## 🚀 Features

### Customer Features
- **Enhanced Product Catalog**: Browse products by categories with advanced search, filtering, and sorting
- **Intuitive Product Cards**: Interactive product cards with hover effects, wishlist functionality, and quick actions
- **Smart Search & Filters**: Price range filtering, category filtering, and multiple sorting options
- **Product Details**: View detailed product information with images, reviews, and ratings
- **Shopping Cart**: Add/remove items, update quantities, and manage cart with real-time feedback
- **User Authentication**: Registration, login, and profile management
- **Checkout Process**: Complete order placement with address management
- **Order History**: View past orders and their status with detailed order pages
- **Product Reviews**: Rate and review products with interactive star rating slider
- **Category Browsing**: Dedicated categories page with product counts and visual navigation

### Admin Features
- **EasyAdmin Integration**: Powerful admin panel for managing all entities
- **Product Management**: Add, edit, and manage products and categories
- **Order Management**: Process and track customer orders
- **User Management**: Manage customer accounts and permissions
- **Inventory Control**: Track product stock and availability
- **Configuration Management**: Database-driven configuration system for easy customization
- **Sample Data Management**: Built-in commands for creating sample data and clearing database

### Technical Features
- **Modern Symfony 7.3**: Latest Symfony framework with best practices
- **Doctrine ORM**: Advanced database management with migrations
- **Webpack Encore**: Modern asset compilation and management
- **Security Bundle**: Robust authentication and authorization
- **Docker Support**: Complete containerized development environment
- **Responsive Design**: Mobile-friendly user interface with Bootstrap 5
- **Dark Mode Support**: Toggle between light and dark themes
- **Configuration System**: Database-driven configuration with caching
- **Enhanced UX**: Loading states, tooltips, auto-dismiss alerts, and smooth animations

### UX Enhancements
- **Interactive Elements**: Hover effects, smooth transitions, and visual feedback
- **Loading States**: Loading overlays for form submissions and page transitions
- **Smart Navigation**: Sticky sidebars, back-to-top buttons, and breadcrumb navigation
- **Accessibility**: ARIA labels, keyboard navigation, and screen reader support
- **Auto-dismiss Alerts**: Flash messages that automatically disappear
- **Enhanced Tooltips**: Contextual help and information throughout the interface
- **Grid/List View Toggle**: Multiple viewing options for product browsing
- **Wishlist Functionality**: Heart icons for saving favorite products
- **Stock Indicators**: Visual badges for low stock and out-of-stock items

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 16 or higher (for asset compilation)
- **MySQL**: 8.0 or higher
- **Docker & Docker Compose** (optional, for containerized setup)

## 🛠️ Installation

### Option 1: Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd SymfoShop
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env .env.local
   ```
   
   Edit `.env.local` and configure your database connection:
   ```env
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/symfoshop?serverVersion=8.0"
   ```

5. **Create database and run migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Initialize the application**
   ```bash
   php bin/console app:setup
   ```

8. **Start the development server**
   ```bash
   symfony server:start
   ```

### Option 2: Docker Setup (Recommended)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd SymfoShop
   ```

2. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

3. **Deploy the application**
   ```bash
   # Windows
   docker/deploy.bat
   
   # Linux/macOS
   docker/deploy.sh
   ```

4. **Access the application**
   - Frontend: http://localhost
   - Admin Panel: http://localhost/admin

## 🎯 Usage

### Application Setup Commands

```bash
# Initialize application with sample data and configuration
php bin/console app:setup

# Initialize configuration only
php bin/console app:setup --skip-sample-data

# Create sample data only
php bin/console app:setup --skip-config

# Clear all data from database
php bin/console app:clear-all-data

# Clear all data but keep admin users
php bin/console app:clear-all-data --keep-admin
```

### Development Commands

```bash
# Asset compilation
npm run dev          # Build for development
npm run build        # Build for production
npm run watch        # Watch for changes and rebuild
npm run dev-server   # Start development server

# Symfony commands
php bin/console cache:clear          # Clear cache
php bin/console doctrine:migrations:status  # Check migration status
php bin/console doctrine:migrations:migrate # Run migrations
php bin/console make:entity          # Create new entity
php bin/console make:controller      # Create new controller
```

### Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Execute commands in container
docker-compose exec php php bin/console cache:clear
docker-compose exec php composer install
```

## 📁 Project Structure

```
SymfoShop/
├── assets/                 # Frontend assets (JS, CSS, images)
├── bin/                   # Symfony console and other binaries
├── config/                # Application configuration
├── docker/                # Docker configuration and deployment scripts
│   ├── deploy.bat         # Windows deployment script
│   └── deploy.sh          # Linux/macOS deployment script
├── migrations/            # Database migrations
├── public/                # Web root directory
├── src/                   # Application source code
│   ├── Command/          # Console commands
│   │   ├── ClearAllDataCommand.php
│   │   ├── SetupCommand.php
│   │   └── CreateSampleDataCommand.php
│   ├── Controller/       # Controllers
│   │   ├── Admin/        # EasyAdmin controllers
│   │   ├── CategoryController.php
│   │   ├── ProductController.php
│   │   └── ...
│   ├── Entity/           # Doctrine entities
│   │   ├── Configuration.php
│   │   ├── Product.php
│   │   └── ...
│   ├── Repository/       # Custom repositories
│   ├── Service/          # Business logic services
│   │   └── ConfigurationService.php
│   └── Twig/             # Twig extensions
│       └── AppExtension.php
├── templates/             # Twig templates
│   ├── category/         # Category templates
│   ├── product/          # Product templates
│   ├── review/           # Review templates
│   └── ...
├── translations/          # Translation files
├── var/                   # Cache, logs, and temporary files
├── vendor/                # Composer dependencies
├── docker-compose.yml     # Docker services configuration
├── package.json           # Node.js dependencies
├── webpack.config.js      # Webpack Encore configuration
└── composer.json          # PHP dependencies
```

## 🔧 Configuration

### Database-Driven Configuration System
The application uses a database-driven configuration system that allows easy customization without code changes:

```bash
# Access configuration through admin panel
http://localhost/admin -> Configuration

# Or use the service in code
$configurationService->get('shop.name');
$configurationService->getShopName();
```

### Available Configuration Keys
- `shop.name` - Shop name
- `shop.description` - Shop description
- `shop.email` - Contact email
- `shop.phone` - Contact phone
- `shop.currency` - Currency code
- `shop.currency_symbol` - Currency symbol

### Database Configuration
The application uses MySQL 8.0. Configure your database connection in `.env.local`:

```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/symfoshop?serverVersion=8.0"
```

### Mailer Configuration
Configure email settings for order notifications:

```env
MAILER_DSN=smtp://localhost:1025
```

### Security Configuration
The application includes user authentication and authorization. Configure security settings in `config/packages/security.yaml`.

## 🎨 User Experience Features

### Enhanced Navigation
- **Search Bar**: Quick product search in navigation
- **Sticky Sidebar**: Filters that follow scroll
- **Breadcrumb Navigation**: Clear page hierarchy
- **Back to Top Button**: Easy navigation on long pages

### Interactive Product Browsing
- **Grid/List View Toggle**: Multiple viewing options
- **Advanced Filtering**: Price range, category, and sorting
- **Wishlist Functionality**: Save favorite products
- **Quick Actions**: Add to cart, view details, wishlist
- **Stock Indicators**: Visual feedback for availability

### Smart Feedback Systems
- **Loading Overlays**: Visual feedback during operations
- **Auto-dismiss Alerts**: Flash messages with timeout
- **Tooltips**: Contextual help throughout interface
- **Hover Effects**: Smooth animations and transitions

### Accessibility Features
- **Keyboard Navigation**: Full keyboard support
- **ARIA Labels**: Screen reader compatibility
- **Focus Management**: Proper focus handling
- **High Contrast**: Dark mode support

## 🧪 Testing

```bash
# Run PHPUnit tests
php bin/phpunit

# Run tests with coverage
php bin/phpunit --coverage-html var/coverage
```

## 📦 Deployment

### Automated Deployment (Docker)

The project includes automated deployment scripts:

```bash
# Windows
docker/deploy.bat

# Linux/macOS
docker/deploy.sh
```

These scripts handle:
- Container management
- Dependency installation
- Asset building
- Database migrations
- Cache clearing
- Application setup

### Manual Production Build

1. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

2. **Configure environment**
   ```bash
   cp .env .env.local
   # Edit .env.local with production settings
   ```

3. **Run migrations**
   ```bash
   php bin/console doctrine:migrations:migrate --env=prod
   ```

4. **Initialize application**
   ```bash
   php bin/console app:setup --env=prod
   ```

5. **Clear and warm cache**
   ```bash
   php bin/console cache:clear --env=prod
   php bin/console cache:warmup --env=prod
   ```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Symfony documentation](https://symfony.com/doc/current/)
2. Review the application logs in `var/log/`
3. Ensure all dependencies are properly installed
4. Verify your database configuration
5. Check the configuration system in the admin panel

## 🔄 Updates

To update the application:

```bash
# Update PHP dependencies
composer update

# Update Node.js dependencies
npm update

# Run migrations
php bin/console doctrine:migrations:migrate

# Clear cache
php bin/console cache:clear

# Rebuild assets
npm run build
```

## 🎯 Key Improvements in This Version

- ✅ **Enhanced UX**: Intuitive navigation, interactive elements, and smooth animations
- ✅ **Configuration System**: Database-driven configuration management
- ✅ **Category Management**: Dedicated categories page with product counts
- ✅ **Review System**: Interactive star rating slider and review management
- ✅ **Order Management**: Detailed order pages and status tracking
- ✅ **Deployment Automation**: Streamlined deployment scripts for Docker
- ✅ **Accessibility**: Full keyboard navigation and screen reader support
- ✅ **Performance**: Caching, loading states, and optimized asset building

---

**Built with ❤️ using Symfony 7.3 and modern web technologies**
