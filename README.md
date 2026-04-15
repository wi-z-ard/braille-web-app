# Braille Bridge

Production-grade web application for converting textbooks (PDF, DOCX, TXT) to Braille.

## Tech Stack

- **Backend**: Pure PHP 8.4+ (no frameworks)
- **Microservice**: Python FastAPI
- **Database**: MySQL 8.0+
- **CLI Tools**: Liblouis, Tesseract OCR
- **Frontend**: TailwindCSS, Vanilla JS

## Quick Setup

### 1. Install System Dependencies

```bash
# Debian/Ubuntu
apt install mysql-server php-fpm php-mysql php-mbstring php-curl \
            liblouis-bin tesseract-ocr tesseract-ocr-ara \
            python3 python3-venv python3-pip poppler-utils

# Start MySQL
systemctl start mysql
```

### 2. Create Database

```bash
mysql -u root -p < schema.sql
```

Or manually:
```sql
CREATE DATABASE braille_bridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configure Environment

```bash
cp .env.example .env
# Edit .env with your MySQL credentials and generate a secret:
openssl rand -hex 32
```

### 4. Run Setup Script

```bash
bash setup.sh
```

### 5. Start Python Service

```bash
# Copy systemd service
sudo cp /tmp/braille-python.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now braille-python

# Check status
systemctl status braille-python
```

### 6. Configure Apache

```apache
<VirtualHost *:80>
    ServerName braille.local
    DocumentRoot /var/www/html/brf-helper/public

    <Directory /var/www/html/brf-helper/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/braille_error.log
    CustomLog ${APACHE_LOG_DIR}/braille_access.log combined
</VirtualHost>
```

Enable rewrite module:
```bash
a2enmod rewrite
systemctl restart apache2
```

## Architecture

```
Browser → Apache/PHP (port 80)
              ↓ Bearer token auth
         Python FastAPI (127.0.0.1:8001)
              ↓ subprocess
         Liblouis + Tesseract
              ↓ HTTP callback
         PHP job update endpoint
```

## Security Features

- CSRF protection on all forms
- Rate limiting per IP
- MIME + extension validation
- Malicious content detection
- Argon2id password hashing
- Internal API secret authentication
- Path traversal prevention
- Storage directory access blocked
- All security headers set

## File Structure

```
brf-helper/
├── public/           # Apache document root
│   ├── index.php     # Router
│   └── pages/        # UI pages
├── src/              # PHP business logic
│   ├── Security.php
│   ├── Auth.php
│   ├── Database.php
│   └── api/          # API endpoints
├── python-service/   # FastAPI microservice
│   └── app/processors/
├── storage/          # Uploads & processed files
└── config/           # Configuration
```

## Usage

1. Register account at `/register`
2. Upload document at `/upload`
3. Track processing at `/job/{id}`
4. Download BRF or Unicode Braille

## Outputs

- **BRF**: Standard Braille format for embossers
- **Unicode Braille**: Preview in browser (U+2800 block)

## Supported Languages

- English (UEB Grade 2)
- Arabic (Grade 1)
- Auto-detection

## Troubleshooting

**Python service won't start:**
```bash
journalctl -u braille-python -f
```

**Database connection failed:**
Check credentials in `.env` and `config/config.php`

**Upload fails:**
```bash
chmod 750 storage/uploads storage/processed
chown -R www-data:www-data storage/
```

**OCR not working:**
```bash
tesseract --list-langs
# Should show: ara, eng
```

## Production Checklist

- [ ] Change `PYTHON_SERVICE_SECRET` in `.env`
- [ ] Set strong MySQL password
- [ ] Enable HTTPS (Let's Encrypt)
- [ ] Set `expose_php = Off` in php.ini
- [ ] Configure firewall (block port 8001 externally)
- [ ] Set up log rotation
- [ ] Configure backup for MySQL + storage/
- [ ] Review rate limits in `config/config.php`

## License

Built for accessibility. Use responsibly.
