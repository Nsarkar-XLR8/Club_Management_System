# ==========================================
# Stage 1: Build the React Frontend
# ==========================================
FROM node:20 AS frontend-build
WORKDIR /app/frontend

# Copy package.json and install dependencies
COPY frontend/package.json frontend/package-lock.json* ./
RUN npm install

# Copy the rest of the frontend source code and build
COPY frontend/ ./
RUN npm run build

# ==========================================
# Stage 2: Serve with PHP + Apache
# ==========================================
FROM php:8.2-apache

# Install necessary PHP extensions (like MySQLi for database connection)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache Mod Rewrite (needed for React Router)
RUN a2enmod rewrite

# Set the working directory to Apache's document root
WORKDIR /var/www/html

# Copy the built React frontend from Stage 1
COPY --from=frontend-build /app/frontend/dist/ ./

# Copy the modern PHP Backend and Database scripts
COPY api/ ./api/
COPY database/ ./database/
COPY images/ ./images/

# Copy the Apache configuration for React Router
COPY .htaccess ./

# Adjust permissions for Apache to read/write (important for image uploads)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/images

# Expose port 80
EXPOSE 80
