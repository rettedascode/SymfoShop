@echo off
echo 🚀 Starting SymfoShop Docker Deployment...

REM Create necessary directories
echo 📁 Creating necessary directories...
if not exist "var\cache" mkdir var\cache
if not exist "var\log" mkdir var\log
if not exist "var\sessions" mkdir var\sessions
if not exist "public\uploads" mkdir public\uploads

REM Build and start containers
echo 🐳 Building and starting Docker containers...
docker-compose up -d --build

REM Wait for MySQL to be ready
echo ⏳ Waiting for MySQL to be ready...
timeout /t 30 /nobreak >nul

REM Run database migrations
echo 🗄️ Running database migrations...
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

REM Clear cache
echo 🧹 Clearing application cache...
docker-compose exec php php bin/console cache:clear

REM Ask for sample data
set /p create_sample="🤔 Do you want to create sample data? (y/n): "
if /i "%create_sample%"=="y" (
    echo 📊 Creating sample data...
    docker-compose exec php php bin/console app:create-sample-data
)

REM Set proper permissions
echo 🔐 Setting final permissions...
docker-compose exec php chown -R symfoshop:www-data var/
docker-compose exec php chown -R symfoshop:www-data public/uploads/

echo ✅ Deployment completed successfully!
echo 🌐 Your application is available at: http://localhost
echo 📊 MySQL is available at: localhost:3306
echo 🔴 Redis is available at: localhost:6379
echo.
echo 📋 Useful commands:
echo   - View logs: docker-compose logs -f
echo   - Stop services: docker-compose down
echo   - Restart services: docker-compose restart
echo   - Access PHP container: docker-compose exec php bash
echo   - Access MySQL: docker-compose exec mysql mysql -u symfoshop -p symfoshop
pause 