# **School Fees Management System (SFMS)**

A web-based application designed to manage school fees, student records, payments, and reporting for educational institutions.

## **Table of Contents**

* [Features](#bookmark=id.yvy9zwkq7taz)  
* [Technologies Used](#bookmark=id.idzgu42aucoy)  
* [Prerequisites](#bookmark=id.hnutjsnuvsef)  
* [Installation and Setup](#bookmark=id.y62inx1z5vo1)  
  * [1\. Clone Repository](#bookmark=id.ybk36ey3qfxf)  
  * [2\. Install Dependencies](#bookmark=id.esc7u2dn2fl8)  
  * [3\. Configure Environment](#bookmark=id.hj0tiatrijso)  
  * [4\. Database Setup](#bookmark=id.7upnyo9ngp92)  
  * [5\. Configure Mail Settings](#bookmark=id.2lwqs84x6mm0)  
  * [6\. Web Server Configuration](#bookmark=id.rxfmgl52qvtf)  
* [Running the Application](#bookmark=id.kevw89xpl3zv)  
* [Admin Login Credentials](#bookmark=id.fssnr8gxl4e1)  
* [Database Backup](#bookmark=id.ujw5q1dh6q2m)  
* [Scripts](#bookmark=id.lgiehwkr2q1n)  
* [Project Structure](#bookmark=id.pfnrrs82yd0u)  

## **Features**

* **Admin Dashboard:** Overview of key metrics and system functions.  
* **User Management:** Manage admin and other user accounts.  
* **Student Management:** Add, edit, and view student records.  
* **Fee Management:**  
  * Fee Categories: Define different types of fees.  
  * Fee Structures: Create and manage fee structures for different classes or programs.  
  * Invoice Generation: Automatically or manually generate fee invoices for students.  
  * Payment Tracking: Record and manage student payments (online and offline).  
  * Discount Management: Apply and manage discounts.  
* **Reporting:**  
  * Fee Collection Reports  
  * Outstanding Dues Reports  
  * Student Ledger  
  * Audit Logs  
  * And more.  
* **Student/Parent Portal:**  
  * View fee statements and invoices.  
  * Check payment history.  
  * Make payments (if payment gateway integrated).  
  * View profile.  
* **System Settings:** Configure application-wide settings.  
* **Email Notifications:** Automated reminders and notifications (e.g., for pending fees).  
* **Audit Logging:** Track important actions within the system.

## **Technologies Used**

* **Backend:** PHP (\>= 7.4)  
* **Routing:** nikic/fast-route  
* **Email:** phpmailer/phpmailer  
* **Environment Variables:** vlucas/phpdotenv  
* **Dependency Management:** Composer  
* **Database:** SQL-based (e.g., MySQL, MariaDB \- a .sql backup is provided)  
* **Frontend:** HTML, CSS, JavaScript (specific libraries/frameworks not explicitly defined, likely vanilla or with minimal libraries)

## **Prerequisites**

Before you begin, ensure you have the following installed:

* PHP version 7.4 or higher.  
* A web server (e.g., Apache, Nginx).  
* A database server (e.g., MySQL, MariaDB, PostgreSQL).  
* Composer (PHP dependency manager).  
* Git (for cloning the repository).

## **Installation and Setup**

Follow these steps to get your SFMS instance up and running:

### **1\. Clone Repository**

git clone \<your-repository-url\> sfms\_project  
cd sfms\_project

### **2\. Install Dependencies**

Install the required PHP packages using Composer:

composer install

This will download all the necessary libraries defined in composer.json into the vendor/ directory.

### **3\. Configure Environment**

The project uses .env files for environment-specific configurations. You might need to create a .env file at the root of the project if one isn't already present, and populate it based on .env.example (if provided) or the application's needs. Key configurations usually include database credentials and application URLs.

* Refer to app/config/database.php for database connection details.  
* Refer to app/config/mail.php for mail server details.

Ensure your .env file (or the direct configuration files if not using .env extensively for these) has the correct settings.

Example .env file:

APP\_NAME="School Fees Management System"  
APP\_ENV=local  
APP\_KEY=YourSecretKey  
APP\_DEBUG=true  
APP\_URL=http://localhost

DB\_CONNECTION=mysql  
DB\_HOST=127.0.0.1  
DB\_PORT=3306  
DB\_DATABASE=sfms\_db  
DB\_USERNAME=your\_db\_user  
DB\_PASSWORD=your\_db\_password  
DB\_CHARSET=utf8mb4  
DB\_COLLATION=utf8mb4\_unicode\_ci

MAIL\_DRIVER=smtp  
MAIL\_HOST=your\_smtp\_host  
MAIL\_PORT=587  
MAIL\_USERNAME=your\_smtp\_username  
MAIL\_PASSWORD=your\_smtp\_password  
MAIL\_ENCRYPTION=tls \# or 'ssl'  
MAIL\_FROM\_ADDRESS=noreply@yourschool.com  
MAIL\_FROM\_NAME="SFMS Notifications"

### **4\. Database Setup**

A database backup with sample data is provided to help you get started quickly.

* Create a Database:  
  Create an empty database in your database server (e.g., MySQL, MariaDB). Let's say you name it sfms\_db.  
* Update Database Configuration:  
  Ensure your database connection details (host, database name, username, password) are correctly set up. This is typically managed in app/config/database.php or through environment variables loaded by phpdotenv.  
  Example for app/config/database.php (modify as per your actual setup):  
  // app/config/database.php  
  return \[  
      'driver'    \=\> getenv('DB\_CONNECTION') ?: 'mysql', // or your db driver  
      'host'      \=\> getenv('DB\_HOST') ?: '127.0.0.1',  
      'database'  \=\> getenv('DB\_DATABASE') ?: 'sfms\_db',  
      'username'  \=\> getenv('DB\_USERNAME') ?: 'root',  
      'password'  \=\> getenv('DB\_PASSWORD') ?: '',  
      'charset'   \=\> 'utf8mb4',  
      'collation' \=\> 'utf8mb4\_unicode\_ci',  
      'prefix'    \=\> '',  
  \];

* Restore Database Backup:  
  The project includes a database backup file located at Database Backup/sfms\_db.sql. This file contains the database schema and some sample data.  
  To restore the database, use a command appropriate for your database system.  
  For **MySQL/MariaDB**:  
  mysql \-u your\_db\_user \-p your\_database\_name \< "Database Backup/sfms\_db.sql"

  (Replace your\_db\_user and your\_database\_name with your actual database username and name. You will be prompted for the password.)  
  For **PostgreSQL**:  
  psql \-U your\_db\_user \-d your\_database\_name \-f "Database Backup/sfms\_db.sql"

  (Replace your\_db\_user and your\_database\_name accordingly.)  
  Consult your database client's documentation for specific instructions if needed.

### **5\. Configure Mail Settings**

Update your mail server configuration in app/config/mail.php or via environment variables. This is crucial for email notifications and reminders. See the .env example in step 3 for the relevant variables.

Example for app/config/mail.php:

// app/config/mail.php  
return \[  
    'driver'    \=\> getenv('MAIL\_DRIVER') ?: 'smtp',  
    'host'      \=\> getenv('MAIL\_HOST') ?: 'smtp.mailtrap.io',  
    'port'      \=\> getenv('MAIL\_PORT') ?: 2525,  
    'username'  \=\> getenv('MAIL\_USERNAME') ?: null,  
    'password'  \=\> getenv('MAIL\_PASSWORD') ?: null,  
    'encryption' \=\> getenv('MAIL\_ENCRYPTION') ?: 'tls', // or 'ssl'  
    'from'      \=\> \[  
        'address' \=\> getenv('MAIL\_FROM\_ADDRESS') ?: 'hello@example.com',  
        'name'    \=\> getenv('MAIL\_FROM\_NAME') ?: 'SFMS Admin',  
    \],  
\];

### **6\. Web Server Configuration**

Configure your web server (Apache or Nginx) to serve the application from the public/ directory. This directory contains the index.php entry point for all requests.

For Apache:  
Ensure mod\_rewrite is enabled. You might need a .htaccess file in the public/ directory (if not already present) with rules similar to this:  
\<IfModule mod\_rewrite.c\>  
    RewriteEngine On  
    RewriteCond %{REQUEST\_FILENAME} \!-f  
    RewriteCond %{REQUEST\_FILENAME} \!-d  
    RewriteRule ^ index.php \[L\]  
\</IfModule\>

For Nginx:  
A typical Nginx configuration might look like this:  
server {  
    listen 80;  
    server\_name yourdomain.com; \# or localhost  
    root /path/to/your/sfms\_project/public;

    index index.php index.html index.htm;

    location / {  
        try\_files $uri $uri/ /index.php?$query\_string;  
    }

    location \~ \\.php$ {  
        try\_files $uri \=404;  
        fastcgi\_split\_path\_info ^(.+\\.php)(/.+)$;  
        fastcgi\_pass unix:/var/run/php/php7.4-fpm.sock; \# Adjust to your PHP-FPM version/socket  
        fastcgi\_index index.php;  
        fastcgi\_param SCRIPT\_FILENAME $document\_root$fastcgi\_script\_name;  
        include fastcgi\_params;  
    }

    location \~ /\\.ht {  
        deny all;  
    }  
}

Adjust paths and PHP-FPM settings as per your server setup.

## **Running the Application**

Once the setup is complete, you should be able to access the application by navigating to the URL you configured for your web server (e.g., http://localhost/sfms\_project/public/ or http://yourdomain.com/).

The main entry point is public/index.php.

## **Admin Login Credentials**

To access the admin panel, use the following default credentials:

* **Login URL:** Typically /login or /admin/login (Please verify the exact path from the application's routes if needed).  
* **Username:** testuser  
* **Password:** pwd123

It is highly recommended to change these default credentials immediately after your first login for security reasons.

## **Database Backup**

A full database backup, including schema and sample data, is provided in the Database Backup/ directory:

* **File:** Database Backup/sfms\_db.sql

This backup can be used to quickly restore the database to a working state with sample data, which is useful for development, testing, or initial setup. Refer to the [Database Setup](#bookmark=id.5hlafwye1bre) section for instructions on how to restore it.

## **Scripts**

The scripts/ directory contains utility scripts.

* scripts/send\_reminders.php: This script is likely used for sending automated fee payment reminders. It would typically be configured to run periodically via a cron job or scheduled task.  
  Example cron job setup (runs daily at 8 AM):  
  0 8 \* \* \* /usr/bin/php /path/to/your/sfms\_project/scripts/send\_reminders.php

  (Adjust the path to PHP and the script location accordingly.)

## **Project Structure**

A brief overview of the main directories:

* app/: Contains the core application logic, including modules, controllers, views, models (if any), services, helpers, and configuration.  
  * Core/: Base classes, database connection, core services.  
  * Modules/: Application modules like Auth, Dashboard, FeeManagement, StudentManagement, etc. Each module typically contains its own Controllers and Views.  
  * Views/: Contains layout files and view templates.  
  * config/: Application configuration files (database, mail, etc.).  
* public/: The web server's document root. Contains the main index.php entry point and static assets (CSS, JS, images \- if any are placed here).  
* vendor/: Composer dependencies.  
* scripts/: Command-line scripts for tasks like sending reminders.  
* Database Backup/: Contains the SQL database backup file.  
* composer.json: Defines project dependencies and metadata.

