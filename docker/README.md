# SymfoShop Docker Deployment

This directory contains all the necessary files to deploy the SymfoShop application using Docker with MySQL database.

## 🐳 Services Included

- **PHP 8.2-FPM** - Application server
- **Nginx** - Web server with optimized configuration
- **MySQL 8.0** - Database server
- **Redis 7** - Cache and session storage (optional)

## 📋 Prerequisites

- Docker Desktop installed and running
- Docker Compose installed
- At least 4GB of available RAM
- Ports 80, 3306, and 6379 available

## 🚀 Quick Start

### Option 1: Using the deployment script (Recommended)

**Linux/Mac:**
```bash
chmod +x docker/deploy.sh
./docker/deploy.sh
```

**Windows:**
```cmd
docker\deploy.bat
```

### Option 2: Manual deployment

1. **Build and start containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Wait for MySQL to be ready (30 seconds)**

3. **Run database migrations:**
   ```bash
   docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
   ```

4. **Clear cache:**
   ```bash
   docker-compose exec php php bin/console cache:clear
   ```

5. **Create sample data (optional):**
   ```bash
   docker-compose exec php php bin/console app:create-sample-data
   ```

## 🌐 Access Points

- **Application**: http://localhost
- **MySQL**: localhost:3306
  - Database: `symfoshop`
  - Username: `symfoshop`
  - Password: `symfoshop123`
- **Redis**: localhost:6379

## 📁 File Structure

```
docker/
├── docker-compose.yml          # Main Docker Compose configuration
├── deploy.sh                   # Linux/Mac deployment script
├── deploy.bat                  # Windows deployment script
├── docker.env                  # Environment variables
├── README.md                   # This file
├── nginx/
│   ├── Dockerfile              # Nginx container definition
│   ├── nginx.conf              # Main Nginx configuration
│   └── sites-available/
│       └── symfoshop.conf      # Symfony virtual host
├── php/
│   ├── Dockerfile              # PHP container definition
│   └── php.ini                 # PHP configuration
└── mysql/
    └── init/                   # MySQL initialization scripts
```

## 🔧 Configuration

### Environment Variables

Edit `docker/docker.env` to customize:
- Database credentials
- Application environment
- Cache settings
- Mailer configuration

### PHP Configuration

The PHP configuration in `docker/php/php.ini` is optimized for production:
- Memory limit: 256M
- Upload max filesize: 20M
- OPcache enabled
- Error reporting disabled

### Nginx Configuration

The Nginx configuration includes:
- Security headers
- Gzip compression
- Static file caching
- Symfony routing support
- Security restrictions

## 🛠️ Useful Commands

### Container Management
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Application Commands
```bash
# Access PHP container
docker-compose exec php bash

# Run Symfony commands
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console app:create-sample-data

# Access MySQL
docker-compose exec mysql mysql -u symfoshop -p symfoshop

# Access Redis
docker-compose exec redis redis-cli
```

### Database Management
```bash
# Create database backup
docker-compose exec mysql mysqldump -u symfoshop -p symfoshop > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u symfoshop -p symfoshop < backup.sql
```

## 🔒 Security Considerations

1. **Change default passwords** in production
2. **Update APP_SECRET** in environment variables
3. **Use HTTPS** in production (configure SSL certificates)
4. **Restrict database access** to application container only
5. **Regular security updates** for base images

## 📊 Performance Optimization

### Production Recommendations

1. **Enable OPcache** (already configured)
2. **Use Redis for sessions** (already configured)
3. **Configure Nginx caching** (already configured)
4. **Optimize MySQL settings** for your workload
5. **Use CDN** for static assets
6. **Enable HTTP/2** in Nginx

### Monitoring

```bash
# Check container resource usage
docker stats

# Monitor application logs
docker-compose logs -f php

# Check database performance
docker-compose exec mysql mysql -u symfoshop -p -e "SHOW PROCESSLIST;"
```

## 🐛 Troubleshooting

### Common Issues

1. **Port already in use**
   - Check if ports 80, 3306, 6379 are available
   - Stop conflicting services

2. **Permission denied**
   - Run: `chmod -R 755 var/ public/uploads/`
   - Ensure Docker has proper permissions

3. **Database connection failed**
   - Wait for MySQL to fully start (30 seconds)
   - Check database credentials in docker.env

4. **Application not accessible**
   - Check if containers are running: `docker-compose ps`
   - Check Nginx logs: `docker-compose logs nginx`

### Debug Mode

To enable debug mode, edit `docker/docker.env`:
```
APP_ENV=dev
APP_DEBUG=true
```

Then restart the PHP container:
```bash
docker-compose restart php
```

## 🔄 Updates

To update the application:

1. **Pull latest changes**
2. **Rebuild containers:**
   ```bash
   docker-compose down
   docker-compose up -d --build
   ```
3. **Run migrations:**
   ```bash
   docker-compose exec php php bin/console doctrine:migrations:migrate
   ```
4. **Clear cache:**
   ```bash
   docker-compose exec php php bin/console cache:clear
   ```

## 📝 License

This Docker setup is part of the SymfoShop project. 