# 💊 Drug Tracker API

A Laravel REST API that integrates with the [RxNorm API](https://lhncbc.nlm.nih.gov/RxNav/APIs/RxNormAPIs.html) to help users:

- 🔍 Search for drug information (public)
- 👤 Register & log in securely
- 💾 Add/remove drugs to/from their personal medication list
- 📋 Retrieve their tracked medications

---

## 📦 Features

- Laravel 10 + Sanctum for API token auth
- Integrates with National Library of Medicine's RxNorm API
- Rate-limited & cached public drug search
- SQLite/MySQL support for dev/testing
- Fully tested: registration, login, RxNorm validation, medication CRUD

---

## ⚙️ Getting Started

### 🔧 Setup

```bash
git clone https://github.com/YOUR_USERNAME/drug-tracker.git
cd drug-tracker

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
