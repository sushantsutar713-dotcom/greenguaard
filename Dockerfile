# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install required system packages and PHP extensions (GD for image manipulation)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite and headers modules for clean routing and security
RUN a2enmod rewrite headers

# Allow .htaccess overrides in Apache webroot
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Render binds to dynamic port specified by $PORT (defaults to 10000)
ENV PORT=10000
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Set working directory
WORKDIR /var/www/html

# Copy all repository files into container web root
COPY . /var/www/html/

# Support both repository structures:
# If files were uploaded inside a "project" subfolder, promote them to root /var/www/html/
RUN if [ -d "/var/www/html/project" ] && [ -f "/var/www/html/project/index.php" ]; then \
      cp -r /var/www/html/project/. /var/www/html/ && rm -rf /var/www/html/project; \
    fi

# Ensure essential directories exist
RUN mkdir -p /var/www/html/config /var/www/html/data /var/www/html/uploads

# Configure config.php gracefully:
# 1. Use config.php if present
# 2. Else copy from config.example.php if present
# 3. Else generate a dynamic production config.php with environment variable support
RUN if [ ! -f /var/www/html/config/config.php ]; then \
      if [ -f /var/www/html/config/config.example.php ]; then \
        cp /var/www/html/config/config.example.php /var/www/html/config/config.php; \
      else \
        printf '<?php\n\
if (!defined("APP_NAME")) define("APP_NAME", "GreenGuard");\n\
if (!defined("APP_TAGLINE")) define("APP_TAGLINE", "Community-Powered Environmental Threat Detection & Resolution");\n\
if (!defined("APP_VERSION")) define("APP_VERSION", "1.0.0");\n\
if (!defined("BASE_URL")) {\n\
    $envBase = getenv("BASE_URL");\n\
    if (!empty($envBase)) {\n\
        define("BASE_URL", rtrim($envBase, "/"));\n\
    } else {\n\
        $isHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || (!empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strpos($_SERVER["HTTP_X_FORWARDED_PROTO"], "https") !== false) || (isset($_SERVER["HTTP_HOST"]) && strpos($_SERVER["HTTP_HOST"], "onrender.com") !== false);\n\
        $protocol = $isHttps ? "https://" : "http://";\n\
        $host = $_SERVER["HTTP_HOST"] ?? "localhost";\n\
        $scriptDir = str_replace("\\\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? ""));\n\
        $baseDir = rtrim(explode("/admin", explode("/api", $scriptDir)[0])[0], "/");\n\
        define("BASE_URL", $protocol . $host . $baseDir);\n\
    }\n\
}\n\
if (!defined("GEMINI_API_KEY")) {\n\
    $envKey = getenv("GEMINI_API_KEY");\n\
    define("GEMINI_API_KEY", !empty($envKey) ? $envKey : "YOUR_GEMINI_API_KEY_HERE");\n\
}\n\
if (!defined("DATA_PATH")) define("DATA_PATH", __DIR__ . "/../data/");\n\
if (!defined("UPLOAD_PATH")) define("UPLOAD_PATH", __DIR__ . "/../uploads/");\n\
if (!defined("DEBUG_MODE")) {\n\
    $envDebug = getenv("DEBUG_MODE");\n\
    define("DEBUG_MODE", $envDebug !== false ? filter_var($envDebug, FILTER_VALIDATE_BOOLEAN) : false);\n\
}\n\
date_default_timezone_set("Asia/Kolkata");\n' > /var/www/html/config/config.php; \
      fi; \
    fi

# Ensure essential data store JSON files exist
RUN for f in users reports notifications; do \
      if [ ! -f "/var/www/html/data/${f}.json" ]; then \
        echo "[]" > "/var/www/html/data/${f}.json"; \
      fi; \
    done

# Set correct ownership and write permissions for JSON data and image uploads
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/data \
    && chmod -R 777 /var/www/html/uploads

# Expose Render port
EXPOSE ${PORT}

# Start Apache in foreground
CMD ["apache2-foreground"]
