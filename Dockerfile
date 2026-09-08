# استخدم نسخة Alpine
FROM php:8.4-fpm-alpine

# تثبيت متطلبات النظام وامتداد الـ zip
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# إعداد مجلد العمل
WORKDIR /var/www
COPY . .

# تثبيت المكتبات
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# إعداد الصلاحيات
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

RUN php artisan storage:link \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# تشغيل السيرفر
EXPOSE 8000
# storage:link وضبط الصلاحيات هون كمان (مش بس فوق وقت الـ build) - لو Render
# بيربط persistent disk بمجلد storage وقت التشغيل الفعلي (بعد ما الصورة
# بنيت)، بيصير المسار يلي كان اتعمل له symlink/صلاحيات وقت الـ build يختفي
# أو يترجع لصلاحيات افتراضية غير مناسبة، وهيك كل ملف تحت /storage كان يرجع
# 403 حتى لو مساره صحيح 100%. كل خطوة هون بـ "|| true" عشان لو فشلت لأي سبب
# (مثلاً --force نفسها عندها مشاكل معروفة بأنظمة معينة) ما توقف تشغيل
# السيرفر بالكامل - أهم شي الموقع يضل شغال حتى لو التخزين لسا فيه مشكلة
CMD php artisan storage:link --force || true; \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true; \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true; \
    php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000