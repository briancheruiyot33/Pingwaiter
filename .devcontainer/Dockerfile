FROM mcr.microsoft.com/devcontainers/php:8.2

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip sqlite3 libsqlite3-dev \
    php8.2-mysql php8.2-sqlite3 php8.2-zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js + NPM (for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Set working directory
WORKDIR /workspaces/${LOCAL_WORKSPACE_FOLDER}