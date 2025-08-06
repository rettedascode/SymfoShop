@echo off
setlocal enabledelayedexpansion

REM SymfoShop Docker Deployment Script for Windows
REM This script deploys the SymfoShop application using Docker Compose

echo.
echo ========================================
echo 🚀 SymfoShop Docker Deployment Script
echo ========================================
echo.

REM Check if Docker is running
echo 🔍 Checking Docker status...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Error: Docker is not running or not installed.
    echo    Please start Docker Desktop and try again.
    pause
    exit /b 1
)
echo ✅ Docker is running

REM Check if Docker Compose is available
echo 🔍 Checking Docker Compose...
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Error: Docker Compose is not available.
    echo    Please install Docker Compose and try again.
    pause
    exit /b 1
)
echo ✅ Docker Compose is available

REM Check if .env file exists
if not exist ".env" (
    echo ❌ Error: .env file not found.
    echo    Please create a .env file based on .env.example
    pause
    exit /b 1
)
echo ✅ Environment file found

REM Create necessary directories
echo.
echo 📁 Creating necessary directories...
if not exist "var\cache" mkdir var\cache
if not exist "var\log" mkdir var\log
if not exist "var\sessions" mkdir var\sessions
if not exist "public\uploads" mkdir public\uploads
if not exist "public\build" mkdir public\build
echo ✅ Directories created

REM Stop any existing containers
echo.
echo 🛑 Stopping existing containers...
docker-compose down --remove-orphans
echo ✅ Existing containers stopped

REM Build and start containers
echo.
echo 🐳 Building and starting Docker containers...
docker-compose up -d --build
if %errorlevel% neq 0 (
    echo ❌ Error: Failed to build and start containers.
    echo    Check the Docker logs for more information.
    pause
    exit /b 1
)
echo ✅ Containers started successfully

REM Wait for MySQL to be ready
echo.
echo ⏳ Waiting for MySQL to be ready...
set /a attempts=0
:wait_mysql
set /a attempts+=1
docker-compose exec -T mysql mysqladmin ping -h localhost -u symfoshop -psymfoshop123 --silent >nul 2>&1
if %errorlevel% neq 0 (
    if %attempts% lss 30 (
        echo    Attempt %attempts%/30 - MySQL not ready yet...
        timeout /t 2 /nobreak >nul
        goto wait_mysql
    ) else (
        echo ❌ Error: MySQL failed to start within 60 seconds.
        echo    Check the MySQL container logs: docker-compose logs mysql
        pause
        exit /b 1
    )
)
echo ✅ MySQL is ready

REM Install Composer dependencies
echo.
echo 📦 Installing Composer dependencies...
docker-compose exec -T php composer install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    echo ❌ Error: Failed to install Composer dependencies.
    pause
    exit /b 1
)
echo ✅ Composer dependencies installed

REM Install Node.js dependencies and build assets
echo.
echo 🎨 Installing Node.js dependencies and building assets...
docker-compose exec -T php npm install
if %errorlevel% neq 0 (
    echo ❌ Error: Failed to install Node.js dependencies.
    pause
    exit /b 1
)

docker-compose exec -T php npm run build
if %errorlevel% neq 0 (
    echo ❌ Error: Failed to build assets.
    pause
    exit /b 1
)
echo ✅ Assets built successfully

REM Run database migrations
echo.
echo 🗄️ Running database migrations...
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
if %errorlevel% neq 0 (
    echo ❌ Error: Failed to run database migrations.
    pause
    exit /b 1
)
echo ✅ Database migrations completed

REM Ask for data management options
echo.
echo 📊 Data Management Options:
echo    1. Keep existing data
echo    2. Clear all data and start fresh
echo    3. Clear all data except admin users
set /p data_option="🤔 Choose an option (1-3): "

if "%data_option%"=="2" (
    echo 🗑️ Clearing all data...
    docker-compose exec -T php php bin/console app:clear-all-data --force
    if %errorlevel% neq 0 (
        echo ⚠️ Warning: Failed to clear data, but deployment continues...
    ) else (
        echo ✅ All data cleared
    )
) else if "%data_option%"=="3" (
    echo 🗑️ Clearing all data except admin users...
    docker-compose exec -T php php bin/console app:clear-all-data --force --keep-admin
    if %errorlevel% neq 0 (
        echo ⚠️ Warning: Failed to clear data, but deployment continues...
    ) else (
        echo ✅ Data cleared (admin users preserved)
    )
) else (
    echo ℹ️ Keeping existing data
)

REM Run application setup
echo.
echo 🚀 Running application setup...
docker-compose exec -T php php bin/console app:setup --force
if %errorlevel% neq 0 (
    echo ⚠️ Warning: Failed to run application setup, but deployment continues...
) else (
    echo ✅ Application setup completed
)

REM Clear and warm cache
echo.
echo 🧹 Clearing and warming application cache...
docker-compose exec -T php php bin/console cache:clear --env=prod
docker-compose exec -T php php bin/console cache:warmup --env=prod
echo ✅ Cache cleared and warmed

REM Set proper permissions
echo.
echo 🔐 Setting proper permissions...
docker-compose exec -T php chown -R www-data:www-data var/
docker-compose exec -T php chown -R www-data:www-data public/uploads/
docker-compose exec -T php chown -R www-data:www-data public/build/
docker-compose exec -T php chmod -R 755 var/
docker-compose exec -T php chmod -R 755 public/uploads/
docker-compose exec -T php chmod -R 755 public/build/
echo ✅ Permissions set

REM Health check
echo.
echo 🔍 Performing health check...
timeout /t 5 /nobreak >nul
curl -f http://localhost >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️ Warning: Application health check failed, but containers are running.
    echo    Check the logs: docker-compose logs -f
) else (
    echo ✅ Application is responding
)

REM Display final status
echo.
echo ========================================
echo ✅ Deployment completed successfully!
echo ========================================
echo.
echo 🌐 Application URLs:
echo    - Frontend: http://localhost
echo    - Admin Panel: http://localhost/admin
echo.
echo 📊 Database Information:
echo    - MySQL Host: localhost:3306
echo    - Database: symfoshop
echo    - Username: symfoshop
echo    - Password: symfoshop123
echo.
echo 📋 Useful Commands:
echo    - View logs: docker-compose logs -f
echo    - Stop services: docker-compose down
echo    - Restart services: docker-compose restart
echo    - Access PHP container: docker-compose exec php bash
echo    - Access MySQL: docker-compose exec mysql mysql -u symfoshop -p symfoshop
echo    - Clear cache: docker-compose exec php php bin/console cache:clear
echo    - Run migrations: docker-compose exec php php bin/console doctrine:migrations:migrate
echo    - Clear all data: docker-compose exec php php bin/console app:clear-all-data --force
echo    - Run setup: docker-compose exec php php bin/console app:setup --force
echo    - Manage configuration: http://localhost/admin?crudAction=index&crudControllerFqcn=App\Controller\Admin\ConfigurationCrudController
echo.
echo 🚀 Your SymfoShop application is ready!
echo.
pause 