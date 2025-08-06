#!/bin/bash

# SymfoShop Docker Deployment Script for Linux/macOS
# This script deploys the SymfoShop application using Docker Compose

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}$1${NC}"
}

print_success() {
    echo -e "${GREEN}$1${NC}"
}

print_warning() {
    echo -e "${YELLOW}$1${NC}"
}

print_error() {
    echo -e "${RED}$1${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to wait for MySQL to be ready
wait_for_mysql() {
    local max_attempts=30
    local attempt=1
    
    print_status "⏳ Waiting for MySQL to be ready..."
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose exec -T mysql mysqladmin ping -h localhost -u symfoshop -psymfoshop123 --silent >/dev/null 2>&1; then
            print_success "✅ MySQL is ready"
            return 0
        fi
        
        echo "   Attempt $attempt/$max_attempts - MySQL not ready yet..."
        sleep 2
        ((attempt++))
    done
    
    print_error "❌ Error: MySQL failed to start within 60 seconds."
    print_error "   Check the MySQL container logs: docker-compose logs mysql"
    return 1
}

# Function to perform health check
health_check() {
    print_status "🔍 Performing health check..."
    sleep 5
    
    if curl -f http://localhost >/dev/null 2>&1; then
        print_success "✅ Application is responding"
    else
        print_warning "⚠️ Warning: Application health check failed, but containers are running."
        print_warning "   Check the logs: docker-compose logs -f"
    fi
}

# Function to handle data management
handle_data_management() {
    echo
    print_status "📊 Data Management Options:"
    echo "    1. Keep existing data"
    echo "    2. Clear all data and start fresh"
    echo "    3. Clear all data except admin users"
    read -p "🤔 Choose an option (1-3): " -n 1 -r
    echo
    
    case $REPLY in
        2)
            print_status "🗑️ Clearing all data..."
            if ! docker-compose exec -T php php bin/console app:clear-all-data --force; then
                print_warning "⚠️ Warning: Failed to clear data, but deployment continues..."
            else
                print_success "✅ All data cleared"
            fi
            ;;
        3)
            print_status "🗑️ Clearing all data except admin users..."
            if ! docker-compose exec -T php php bin/console app:clear-all-data --force --keep-admin; then
                print_warning "⚠️ Warning: Failed to clear data, but deployment continues..."
            else
                print_success "✅ Data cleared (admin users preserved)"
            fi
            ;;
        *)
            print_status "ℹ️ Keeping existing data"
            ;;
    esac
}

# Main deployment script
main() {
    echo
    echo "========================================"
    echo "🚀 SymfoShop Docker Deployment Script"
    echo "========================================"
    echo

    # Check if Docker is running
    print_status "🔍 Checking Docker status..."
    if ! docker info >/dev/null 2>&1; then
        print_error "❌ Error: Docker is not running or not installed."
        print_error "   Please start Docker and try again."
        exit 1
    fi
    print_success "✅ Docker is running"

    # Check if Docker Compose is available
    print_status "🔍 Checking Docker Compose..."
    if ! command_exists docker-compose; then
        print_error "❌ Error: Docker Compose is not installed."
        print_error "   Please install Docker Compose and try again."
        exit 1
    fi
    print_success "✅ Docker Compose is available"

    # Check if .env file exists
    if [ ! -f ".env" ]; then
        print_error "❌ Error: .env file not found."
        print_error "   Please create a .env file based on .env.example"
        exit 1
    fi
    print_success "✅ Environment file found"

    # Create necessary directories
    echo
    print_status "📁 Creating necessary directories..."
    mkdir -p var/cache var/log var/sessions
    mkdir -p public/uploads public/build
    print_success "✅ Directories created"

    # Set proper permissions for directories
    print_status "🔐 Setting initial permissions..."
    chmod -R 755 var/ 2>/dev/null || true
    chmod -R 755 public/uploads/ 2>/dev/null || true
    chmod -R 755 public/build/ 2>/dev/null || true

    # Stop any existing containers
    echo
    print_status "🛑 Stopping existing containers..."
    docker-compose down --remove-orphans
    print_success "✅ Existing containers stopped"

    # Build and start containers
    echo
    print_status "🐳 Building and starting Docker containers..."
    if ! docker-compose up -d --build; then
        print_error "❌ Error: Failed to build and start containers."
        print_error "   Check the Docker logs for more information."
        exit 1
    fi
    print_success "✅ Containers started successfully"

    # Wait for MySQL to be ready
    if ! wait_for_mysql; then
        exit 1
    fi

    # Install Composer dependencies
    echo
    print_status "📦 Installing Composer dependencies..."
    if ! docker-compose exec -T php composer install --no-dev --optimize-autoloader; then
        print_error "❌ Error: Failed to install Composer dependencies."
        exit 1
    fi
    print_success "✅ Composer dependencies installed"

    # Install Node.js dependencies and build assets
    echo
    print_status "🎨 Installing Node.js dependencies and building assets..."
    if ! docker-compose exec -T php npm install; then
        print_error "❌ Error: Failed to install Node.js dependencies."
        exit 1
    fi

    if ! docker-compose exec -T php npm run build; then
        print_error "❌ Error: Failed to build assets."
        exit 1
    fi
    print_success "✅ Assets built successfully"

    # Run database migrations
    echo
    print_status "🗄️ Running database migrations..."
    if ! docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction; then
        print_error "❌ Error: Failed to run database migrations."
        exit 1
    fi
    print_success "✅ Database migrations completed"

    # Handle data management
    handle_data_management

    # Run application setup
    echo
    print_status "🚀 Running application setup..."
    if ! docker-compose exec -T php php bin/console app:setup --force; then
        print_warning "⚠️ Warning: Failed to run application setup, but deployment continues..."
    else
        print_success "✅ Application setup completed"
    fi

    # Clear and warm cache
    echo
    print_status "🧹 Clearing and warming application cache..."
    docker-compose exec -T php php bin/console cache:clear --env=prod
    docker-compose exec -T php php bin/console cache:warmup --env=prod
    print_success "✅ Cache cleared and warmed"

    # Set proper permissions
    echo
    print_status "🔐 Setting proper permissions..."
    docker-compose exec -T php chown -R www-data:www-data var/
    docker-compose exec -T php chown -R www-data:www-data public/uploads/
    docker-compose exec -T php chown -R www-data:www-data public/build/
    docker-compose exec -T php chmod -R 755 var/
    docker-compose exec -T php chmod -R 755 public/uploads/
    docker-compose exec -T php chmod -R 755 public/build/
    print_success "✅ Permissions set"

    # Perform health check
    health_check

    # Display final status
    echo
    echo "========================================"
    print_success "✅ Deployment completed successfully!"
    echo "========================================"
    echo
    echo "🌐 Application URLs:"
    echo "   - Frontend: http://localhost"
    echo "   - Admin Panel: http://localhost/admin"
    echo
    echo "📊 Database Information:"
    echo "   - MySQL Host: localhost:3306"
    echo "   - Database: symfoshop"
    echo "   - Username: symfoshop"
    echo "   - Password: symfoshop123"
    echo
    echo "📋 Useful Commands:"
    echo "   - View logs: docker-compose logs -f"
    echo "   - Stop services: docker-compose down"
    echo "   - Restart services: docker-compose restart"
    echo "   - Access PHP container: docker-compose exec php bash"
    echo "   - Access MySQL: docker-compose exec mysql mysql -u symfoshop -p symfoshop"
    echo "   - Clear cache: docker-compose exec php php bin/console cache:clear"
    echo "   - Run migrations: docker-compose exec php php bin/console doctrine:migrations:migrate"
    echo "   - Clear all data: docker-compose exec php php bin/console app:clear-all-data --force"
    echo "   - Run setup: docker-compose exec php php bin/console app:setup --force"
    echo "   - Manage configuration: http://localhost/admin?crudAction=index&crudControllerFqcn=App\Controller\Admin\ConfigurationCrudController"
    echo
    print_success "🚀 Your SymfoShop application is ready!"
    echo
}

# Run the main function
main "$@" 