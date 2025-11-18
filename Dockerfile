FROM node:20-alpine AS tailwind
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install --omit=dev

COPY src ./src
RUN mkdir -p dist \
    && npm run build


FROM php:8.3-fpm-alpine AS php_stage
WORKDIR /var/www/html

COPY . .

# hapus folder src kalau sudah tak perlu (opsional)
RUN rm -rf src

# copy hasil build tailwind
COPY --from=tailwind /app/dist ./dist


FROM nginx:alpine AS web

WORKDIR /var/www/html

# Copy seluruh project hasil stage PHP
COPY --from=php_stage /var/www/html /var/www/html

# Replace default nginx config
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
