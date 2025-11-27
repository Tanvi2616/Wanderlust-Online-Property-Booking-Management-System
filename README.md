## 🏡 Wanderlust – Online Property Booking & Management System

Wanderlust is a web-based platform designed to connect property owners and customers directly, eliminating the need for third-party agents.
It provides a smooth interface for customers to browse properties, check availability, book stays, make secure payments, and submit reviews, while property owners can manage their listings, bookings, and payments in one place.

## 🚀 Features
⭐ Customer Features

- Browse all available properties

- Filter & explore property categories

- View property details, images, and reviews

- Book stays & make secure payments

-  Download PDF receipts

- Submit reviews & ratings

- Manage bookings through a personal dashboard

## 🏠 Owner Features

- Register & log in as a property owner

- Add new property listings with images

- Edit or delete existing properties

- View customer bookings for each property

- Track payments and revenue

- Monitor customer reviews

## 🖥️ System Features

- Role-based authentication (Customer/Owner)

- Responsive UI using Bootstrap

- Real-time booking status updates

- Automated receipt generation (FPDF)

- MySQL-based relational database

- Secure and user-friendly interface

## 🧱 Tech Stack
- Frontend : HTML5 ,CSS3 , Bootstrap 5 ,JavaScript, Font Awesome Icons

- Backend : PHP, Apache Server (XAMPP), FPDF Library (PDF generation)

- Database : MySQL, phpMyAdmin

- Tools: Visual Studio Code, XAMPP Local Server, Google Chrome / Any Browser

## 🏗️ System Architecture
Customer → Frontend → Backend → Database → Owner


- Frontend handles UI & form validation

- Backend (PHP) manages logic, sessions & database operations

- Database stores users, properties, bookings, payments & reviews

## 📂 Project Modules
1. Authentication Module

Login / Signup for Customers & Owners

Role-based redirection

2. Property Management

Add, edit, update, delete properties (Owner)

Display property listings (Customer)

3. Booking Management

Real-time availability

Booking status (Active, Completed, Cancelled)

Checkout-based auto-updated status

4. Payment System

Payment entry storage

Downloadable PDF receipt

5. Review System

Post & display property reviews

Average rating calculation

## 🗄️ Database Structure
- Table Name	Description
- users	Stores customer & owner login credentials
- properties	Property details linked to owners
- bookings	Booking data linked to customers & properties
- payments	Payment entries for each booking
- reviews	Customer reviews & ratings


## 🧪 How to Run the Project
1. Install XAMPP

Download XAMPP and start:

Apache

MySQL

2. Extract the Project

Place all files inside:

xampp/htdocs/Wanderlust/

3. Import Database

Open phpMyAdmin

Create database: wanderlust

Import wanderlust.sql file

4. Run the Project

Open browser and go to:

http://localhost/Wanderlust/

## 🔮 Future Enhancements

- Online Chat Support

- AI-based property recommendations

- Location-based suggestions

- Mobile App integration

- OTP-based authentication

- Admin dashboard

- SMS/Email alerts


## 📜 License

This project is for academic and learning purposes. You may modify or extend it as needed.
