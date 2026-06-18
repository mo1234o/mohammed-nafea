# Mohammed Nafea Portfolio - Production Ready

## 🚀 Deployment Instructions

### 1. Upload Files
Upload the entire project folder to your hosting server:
- All PHP files
- `data/` directory (with JSON files)
- `uploads/` directory (empty initially)
- `vendor/` directory (PHP dependencies)
- `.htaccess` file

### 2. Set Permissions
```bash
# Set proper file permissions
chmod 755 ./
chmod 644 *.php
chmod 755 dashboard/
chmod 644 dashboard/*.php
chmod 755 data/
chmod 644 data/*.json
chmod 755 uploads/
chmod 755 vendor/
```

### 3. Configure Database (Optional)
The project uses JSON files for data storage, no database required.

### 4. Access Dashboard
- **Main Site**: `https://yourdomain.com/`
- **Admin Dashboard**: `https://yourdomain.com/dashboard/login.php`

### 5. Default Login Credentials
- **Email**: `mohammednafea700@gmail.com`
- **Password**: `Nafea#2026$Studio!`

## 📁 Project Structure

```
├── index.php              # Main portfolio page
├── .htaccess              # Apache configuration
├── composer.json          # PHP dependencies
├── mailer_config.php      # Email configuration
├── dashboard/             # Admin panel
│   ├── index.php          # Unified dashboard
│   ├── login.php          # Login page
│   ├── logout.php         # Logout handler
│   ├── admin_config.php   # Admin credentials
│   ├── projects.php       # Projects management
│   ├── theme.php          # Theme colors
│   └── sections.php       # Sections management
├── data/                  # JSON data files
│   ├── content.json       # Site content
│   ├── projects.json      # Projects data
│   ├── theme.json         # Theme colors
│   └── sections.json      # Sections settings
├── uploads/               # Project images
└── vendor/                # PHP dependencies
```

## ⚡ Features

### Frontend
- **Responsive Design**: Works on all devices
- **Dynamic Content**: All content loaded from JSON
- **Theme Customization**: Live color changes
- **Section Management**: Show/hide/reorder sections
- **Project Gallery**: With image uploads
- **Contact Form**: With email notifications

### Admin Dashboard
- **Unified Interface**: All controls in one place
- **Content Editor**: Edit all text content
- **Projects Manager**: Add/edit/delete projects
- **Theme Editor**: Live color customization
- **Sections Manager**: Drag & drop reordering
- **Image Upload**: Secure file handling

## 🔧 Configuration

### Email Setup
Edit `mailer_config.php`:
```php
<?php
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
    'from' => ['your-email@gmail.com' => 'Mohammed Nafea'],
    'to' => ['your-email@gmail.com']
];
```

### Change Admin Credentials
Edit `dashboard/admin_config.php`:
```php
<?php
return [
    'email' => 'your-email@example.com',
    'password_hash' => password_hash('your-new-password', PASSWORD_DEFAULT)
];
```

## 🛡️ Security Features

- **Session Protection**: Secure admin authentication
- **File Upload Security**: Image validation and sanitization
- **XSS Protection**: All output properly escaped
- **CSRF Protection**: Form tokens
- **Directory Protection**: Sensitive files blocked
- **HTTPS Ready**: SSL configuration included

## 📱 Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🔄 Live Updates

All changes made in the dashboard are reflected immediately on the frontend:
- Content changes: Instant
- Theme colors: Instant
- Projects: Instant
- Sections: Instant

## 📞 Support

For any issues or questions:
- Check file permissions
- Verify PHP version (7.4+)
- Ensure Apache with mod_rewrite
- Check error logs

---

**Ready for production deployment!** 🎉
