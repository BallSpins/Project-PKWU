FROM node:20-alpine AS tailwind
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install --omit=dev

COPY src ./src
RUN mkdir -p dist \
    && npm run build


FROM php:8.3-apache-alpine AS web
WORKDIR /var/www/html

# Install PHP extensions jika diperlukan
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . .

# hapus folder src kalau sudah tak perlu (opsional)
# RUN rm -rf src

# copy hasil build tailwind
COPY --from=tailwind /app/dist ./dist

# Set document root ke folder view dan enable mod_rewrite
RUN sed -i 's#/var/www/html#/var/www/html/view#g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's#/var/www/html#/var/www/html/view#g' /etc/apache2/conf.d/default.conf \
    && a2enmod rewrite

EXPOSE 80