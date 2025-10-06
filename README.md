# 🧾 Inventory Management System  
**Client:** Ministry of Land  

---

## 🧠 Project Overview  
The **Inventory Management System** is a web-based platform developed for the **Ministry of Land** to efficiently manage, monitor, and track their inventory resources.  
It provides features for item registration, stock tracking, asset categorization, and reporting — ensuring transparency and better control over resources.

---

## 🏗️ Tech Stack  

| Layer | Technology |
|:------|:------------|
| **Backend** | Laravel (PHP Framework) |
| **Frontend** | Blade Templates, Bootstrap |
| **Database** | MySQL |
| **Server** | PHP Artisan Development Server |
| **Dependency Manager** | Composer |
| **Version Control** | Git & GitHub |

---

## 🚀 Installation Guide  

Follow the steps below to set up the project on your local environment:

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/your-username/inventory-management-system.git
cd inventory-management-system
```

### 2️⃣ Copy Environment File

Duplicate the example environment file and rename it:
```bash
cp .env.example .env
```

### 3️⃣ Create Database

Create a new MySQL database named:
```bash
land_inv_db
```

### 4️⃣ Install Dependencies

Install all PHP dependencies using Composer:
```bash
composer install
```
### 5️⃣ Generate Application Key
```bash
php artisan key:generate
```
### 6️⃣ Run Migrations
```bash
php artisan migrate
```
### 7️⃣ Start the Development Server

Run the following command to start the application:
```bash
php artisan serve
```

The application will be available at:
👉 http://127.0.0.1:8000

### 📄 License

This project is developed for the Ministry of Land and is not open for public redistribution without prior permission.
