# Company Module - Localhost Setup & Testing Guide

## 📋 Prerequisites

Before running the company module on localhost, ensure you have:

1. **PHP 5.6+** - Command: `php --version`
2. **MySQL Server** - Running and accessible
3. **Apache/PHP Server** - Either XAMPP, WAMP, or built-in PHP server

---

## 🚀 Step 1: Start MySQL Server

### Option A: XAMPP
```bash
# Windows
- Open XAMPP Control Panel
- Click "Start" next to MySQL
- Verify "MySQL" shows as running
```

### Option B: WAMP
```bash
# Windows
- Click the WAMP icon in system tray
- Hover over "MySQL" and verify it's green
```

### Option C: Command Line (if MySQL is installed)
```bash
# Windows PowerShell
net start MySQL

# Or if using a specific MySQL service
net start "MySQL80"
```

---

## 🚀 Step 2: Verify Database & Tables

### Check if skillbridge_db exists:
```bash
mysql -u root -p (press enter for empty password)
SHOW DATABASES;
```

If `skillbridge_db` doesn't exist, create it:
```sql
CREATE DATABASE skillbridge_db;
USE skillbridge_db;
```

---

## 🚀 Step 3: Create User Table (if missing)

The `User` table must exist for the company module to work. Run this SQL in MySQL:

```sql
CREATE TABLE IF NOT EXISTS User (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    Email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'company', 'organization', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create company table for test user
CREATE TABLE IF NOT EXISTS company (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255),
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🚀 Step 4: Create a Company Test User

Insert a test company user into the database:

```sql
INSERT INTO User (username, Email, password, role) 
VALUES ('Tech Corp', 'hr@techcorp.com', SHA1('password123'), 'company');

-- Verify insert
SELECT * FROM User WHERE role='company';
```

**Demo Company Credentials:**
- Email: `hr@techcorp.com`
- Password: `password123`

---

## 🚀 Step 5: Start PHP Server

### Option A: Built-in PHP Server (Simplest)
```bash
cd c:\Users\Shaini Ishuwara\Desktop\project\Skill_Bridge_Group_Project
php -S localhost:8000
```
Then navigate to: **http://localhost:8000**

### Option B: XAMPP
1. Put the project in `C:\xampp\htdocs\Skill_Bridge_Group_Project`
2. Start Apache from XAMPP Control Panel
3. Navigate to: **http://localhost/Skill_Bridge_Group_Project**

### Option C: WAMP
1. Put the project in `C:\wamp\www\Skill_Bridge_Group_Project`
2. Click WAMP icon and ensure Apache is green
3. Navigate to: **http://localhost/Skill_Bridge_Group_Project**

---

## ✅ Step 6: Verify Company Module

### Run the Verification Script:
```bash
php Config/verify_company.php
```

Expected output:
```
✅ PASSED: Connected to skillbridge_db
✅ PASSED: Table 'companies' exists
✅ PASSED: Table 'internships' exists
✅ PASSED: Table 'applications' exists
✅ PASSED: Table 'interviews' exists
```

---

## 🔑 Step 7: Test Company Login

1. Navigate to: `http://localhost:8000/Auth/login.php`
2. Click "Create Account"
3. Select **"Company"** from the role dropdown
4. Fill in company details:
   - Email: `hr@company.com`
   - Password: `testpass123`
   - Company Name: `My Test Company`
   - Industry: `Technology`
   - Contact: `John Doe`
   - Phone: `+1234567890`
   - Location: `New York`
5. Click "Register"
6. Verify email (check for test/dev mode)
7. Login with your company credentials

---

## 📊 Step 8: Test Company Dashboard

After login, you should be redirected to:
```
/Functions/Dashboards/Company/dashboard.php
```

### Verify You Can See:

✅ **Dashboard Stats**
- Active Internships count
- Total Applications count
- Shortlisted count
- Interviews Scheduled count

✅ **Navigation Sidebar**
- Dashboard link (active)
- Company Profile
- Internships
- Applications
- Interviews
- Logout button

✅ **Welcome Section**
- "Welcome back, [Company Name]"
- Company info card showing name and location

---

## 🧪 Step 9: Test CRUD Operations

### A. Create Internship
1. Click "Internships" in sidebar
2. Fill in form:
   - Title: "Full Stack Developer Intern"
   - Description: "Build web applications"
   - Location: "Remote"
   - Duration: "3 months"
   - Type: "Full-time"
   - Deadline: (future date)
3. Click "Publish Internship"
4. ✅ Verify internship appears in table below

### B. View Applications
1. Click "Applications" in sidebar
2. ✅ Should see demo applications (if demo data seeded)
3. Filter by status using dropdown
4. ✅ Verify filtering works

### C. Update Application Status
1. From applications list, select a status from dropdown
2. Click "Update"
3. ✅ Status should change and page refreshes

### D. Schedule Interview
1. Click "Interviews" in sidebar
2. Fill in form:
   - Applicant Name: "John Student"
   - Position: "Full Stack Developer"
   - Date/Time: (future date/time)
   - Type: "Video Call"
3. Click "Save Interview"
4. ✅ Interview should appear in list below

### E. Update Company Profile
1. Click "Company Profile" in sidebar
2. Edit any field (e.g., description)
3. Click "Save Company Profile"
4. ✅ Changes should persist on reload

---

## 🔍 Step 10: Troubleshooting

### Issue: "Connection failed: No connection could be made"
**Solution:** Start MySQL server (see Step 1)

### Issue: "Table doesn't exist"
**Solution:** Company schema auto-creates on first load. Refresh the page or run:
```php
php Config/verify_company.php
```

### Issue: White blank page
**Solution:** Check PHP error log:
```bash
php -r "ini_set('display_errors', 1); require 'index.php';"
```

### Issue: "Sessions not working" / Redirects to login
**Solution:** 
1. Verify cookies are enabled
2. Check that `$_SESSION['user']` is being set
3. Try clearing browser cookies
4. Verify BASE_URL in `Session/Session.php` is correct: `/Skill_Bridge_Group_Project`

### Issue: "Database tables not found"
**Solution:** Run this in MySQL:
```sql
USE skillbridge_db;
SHOW TABLES;
```
If company tables missing, the schema will auto-create on company dashboard first access.

---

## 📝 Database Schema Reference

### companies table
```sql
id, user_id, company_name, industry_sector, contact_person, 
contact_number, website, location, company_size, description, 
logo, created_at, updated_at
```

### internships table
```sql
id, company_id, title, description, location, duration, 
type, status, deadline, created_at, updated_at
```

### applications table
```sql
id, company_id, applicant_name, email, position, 
status, applied_date
```

### interviews table
```sql
id, company_id, applicant_name, position, interview_date, 
interview_type, status, notes, created_at
```

---

## ✨ Demo Features

The company module includes automatic demo data that seeds on first company dashboard load:

### Demo Internships:
1. Full Stack Developer Intern - Remote, 6 months
2. Data Science Intern - Hybrid, 3 months  
3. Product Design Intern - On-site, 4 months

### Demo Applications:
1. Aisha Perera - Full Stack Developer (New)
2. Nimal Jayasinghe - Data Science (Shortlisted)
3. Tharindu Silva - Product Design (Interview)

### Demo Interviews:
1. Nimal Jayasinghe - Portfolio Review - 2026-08-25
2. Tharindu Silva - Design Challenge - 2026-08-27

---

## 🎯 Quick Start Command

```bash
# All-in-one setup (run from project root):
cd c:\Users\Shaini Ishuwara\Desktop\project\Skill_Bridge_Group_Project
php -S localhost:8000

# Then in browser:
http://localhost:8000/Auth/login.php
```

---

## ✅ Checklist for Localhost Setup

- [ ] MySQL server is running
- [ ] `skillbridge_db` database exists
- [ ] `User` table exists with test company user
- [ ] PHP server is running (localhost:8000 or Apache)
- [ ] Can access login page without errors
- [ ] Can create company account via registration
- [ ] Can login as company user
- [ ] Company dashboard loads with stats
- [ ] Sidebar navigation works
- [ ] Can create, read, update internships
- [ ] Can view and update applications
- [ ] Can schedule interviews

---

## 🆘 Support

If you encounter issues:

1. Check PHP error logs
2. Verify MySQL is running: `mysql -u root`
3. Check database connection: `php Config/verify_company.php`
4. Review Session.php for BASE_URL configuration
5. Clear browser cache and cookies
6. Check PHP version supports required features

---

**Last Updated:** 2026-08-18  
**PHP Version Required:** 5.6+  
**MySQL Required:** Yes, running on localhost  
**Default Port:** 8000 (built-in server) or 80 (Apache)
