# Company Registration Form - Setup Guide

## ✓ What's Fixed

### 1. **CSS & JavaScript Paths**
   - ✓ Fixed incorrect asset paths in HTML
   - CSS now loads from: `company_register.css`
   - JS now loads from: `company_register.js`

### 2. **Backend Handler Created**
   - ✓ Created `process_company_register.php`
   - Includes validation for all form fields
   - Secure password hashing using PHP's `password_hash()`
   - Error handling and session management

### 3. **Error Display System**
   - ✓ Added error message display to form
   - ✓ Custom styled error container with visual feedback
   - Session-based error persistence

### 4. **Form Validation**
   - Client-side: Password matching
   - Server-side: All field validation
   - Email validation
   - Password strength (minimum 8 characters)

---

## 🚀 How to Test the UI (Immediately)

1. **Open the test file in your browser:**
   ```
   Assets/CSS/Student/company_register_test.html
   ```
   This HTML version loads the same CSS and shows the completed UI design.

---

## 🔧 How to Get Full Functionality (With PHP Backend)

Since your PHP version (5.3.8) doesn't support the built-in server, use one of these options:

### **Option 1: Use XAMPP/WAMP (Recommended)**

1. Download and install XAMPP or WAMP
2. Place the project in the `htdocs` folder (XAMPP) or `www` folder (WAMP)
3. Access via: `http://localhost/Skill_Bridge_Group_Project/Assets/CSS/Student/company_register.php`

### **Option 2: Use Apache/IIS**

If you have Apache or IIS installed:
1. Configure the project directory as a virtual host
2. Ensure PHP is enabled
3. Access via: `http://localhost/company_register.php`

### **Option 3: Use Newer PHP Version**

1. Install PHP 7.4+ (supports built-in server)
2. Run: `php -S localhost:8000`
3. Access via: `http://localhost:8000/Assets/CSS/Student/company_register.php`

---

## 📊 Database Setup Required

Before the form can save data, create this table:

```sql
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    business_email VARCHAR(255) NOT NULL UNIQUE,
    industry_sector VARCHAR(100) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    website VARCHAR(255),
    location VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 📋 Files Included

```
Assets/CSS/Student/
├── company_register.php              ✓ Main form (needs PHP server)
├── company_register.css              ✓ Styling (complete)
├── company_register.js               ✓ JavaScript validation (complete)
├── company_register_test.html        ✓ Static test version
└── process_company_register.php      ✓ Backend handler (needs database)
```

---

## ✅ Testing Checklist

- [ ] Visual design loads correctly (use test.html)
- [ ] Form fields are all present and styled properly
- [ ] Password toggle icon works
- [ ] Form validation works
- [ ] Set up PHP server (XAMPP/WAMP)
- [ ] Create database table
- [ ] Test form submission with backend

---

## 🐛 Troubleshooting

**Issue:** CSS/JS not loading
- **Solution:** Make sure you're accessing via HTTP/HTTPS, not file:// protocol

**Issue:** Form won't submit
- **Solution:** Check that the database table is created and PHP can connect to MySQL

**Issue:** White screen after submit
- **Solution:** Check PHP error logs for database connection issues

---

## 📞 Support

If you need help with:
1. Setting up a PHP server
2. Creating the database table
3. Deploying the form
4. Adding additional features

Let me know and I can help!
