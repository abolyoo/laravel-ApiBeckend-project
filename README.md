<<<<<<< HEAD
<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

# 📦 Claim Service API

سرویس «مدیریت ادعاها» با استفاده از فریمورک Laravel طراحی شده و قابلیت‌هایی نظیر احراز هویت با JWT، صف‌های پس‌زمینه، ارتباط با سایر سرویس‌ها و واکنش‌ها را فراهم می‌کند.

---

## 🚀 راه‌اندازی سریع

```bash
git clone https://github.com/abolyoo/laravel-ApiBeckend-project.git
cd claim-service
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan queue:work

## 🔐 احراز هویت با توکن

Authorization: Bearer YOUR_TOKEN_HERE

## 🧵 صف‌ها و Jobها

php artisan queue:work

## 📂 ساختار پروژه

app/
├── Http/
│   └── Controllers/
├── Models/
├── Jobs/
├── Services/
database/
routes/

## 📜 لایسنس

MIT License © 2025 - Claim Development BY ABOLYOO20
=======
