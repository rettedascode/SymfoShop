#!/bin/bash

# SymfoShop Docker Deployment Script

echo "🚀 Starting SymfoShop Docker Deployment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Check if Docker Compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p var/cache var/log var/sessions
mkdir -p public/uploads

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 var/
chmod -R 755 public/uploads/

# Build and start containers
echo "🐳 Building and starting Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 30

# Run database migrations
echo "🗄️ Running database migrations..."
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
echo "🧹 Clearing application cache..."
docker-compose exec php php bin/console cache:clear

# Create sample data (optional)
read -p "🤔 Do you want to create sample data? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "📊 Creating sample data..."
    docker-compose exec php php bin/console app:create-sample-data
fi

# Set proper permissions again after container is running
echo "🔐 Setting final permissions..."
docker-compose exec php chown -R symfoshop:www-data var/
docker-compose exec php chown -R symfoshop:www-data public/uploads/

echo "✅ Deployment completed successfully!"
echo "🌐 Your application is available at: http://localhost"
echo "📊 MySQL is available at: localhost:3306"
echo "🔴 Redis is available at: localhost:6379"
echo ""
echo "📋 Useful commands:"
echo "  - View logs: docker-compose logs -f"
echo "  - Stop services: docker-compose down"
echo "  - Restart services: docker-compose restart"
echo "  - Access PHP container: docker-compose exec php bash"
echo "  - Access MySQL: docker-compose exec mysql mysql -u symfoshop -p symfoshop" 