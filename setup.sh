#!/usr/bin/env bash
set -euo pipefail

echo "=== Braille Bridge Setup ==="

# 1. Storage permissions
echo "[1/6] Setting storage permissions..."
chmod 750 storage storage/uploads storage/processed storage/jobs storage/temp
chown -R www-data:www-data storage/ 2>/dev/null || true

# 2. Python virtual environment
echo "[2/6] Setting up Python environment..."
cd python-service
python3 -m venv .venv
source .venv/bin/activate
pip install -q --upgrade pip
pip install -q -r requirements.txt
deactivate
cd ..

# 3. Check system dependencies
echo "[3/6] Checking system dependencies..."
for cmd in lou_translate file2brl tesseract mysql; do
  if command -v "$cmd" &>/dev/null; then
    echo "  ✓ $cmd found"
  else
    echo "  ✗ $cmd NOT found — install liblouis-utils / tesseract-ocr"
  fi
done

# 4. Generate secret if not set
echo "[4/6] Checking environment..."
if [ ! -f .env ]; then
  SECRET=$(openssl rand -hex 32)
  echo "PYTHON_SECRET=${SECRET}" > .env
  echo "UPLOAD_PATH=$(pwd)/storage/uploads" >> .env
  echo "PROCESSED_PATH=$(pwd)/storage/processed" >> .env
  echo "PHP_CALLBACK_URL=http://127.0.0.1/api/internal/job-update" >> .env
  echo "  ✓ .env created with generated secret"
fi

# 5. Initialize MySQL DB
echo "[5/6] Checking database connection..."
if ! mysql -u root -e "CREATE DATABASE IF NOT EXISTS braille_bridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
  echo "  ⚠ MySQL connection failed. Run manually:"
  echo "    mysql -u root -p -e \"CREATE DATABASE braille_bridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
else
  echo "  ✓ Database created"
  php -r "
    define('APP_ROOT', '$(pwd)');
    define('STORAGE_PATH', APP_ROOT . '/storage');
    define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
    define('PROCESSED_PATH', STORAGE_PATH . '/processed');
    define('JOBS_PATH', STORAGE_PATH . '/jobs');
    define('TEMP_PATH', STORAGE_PATH . '/temp');
    define('MAX_FILE_SIZE', 52428800);
    define('RATE_LIMIT_UPLOADS', 10);
    define('RATE_LIMIT_WINDOW', 3600);
    define('ALLOWED_MIME_TYPES', []);
    define('ALLOWED_EXTENSIONS', []);
    define('SESSION_LIFETIME', 7200);
    define('CSRF_TOKEN_LENGTH', 32);
    define('PYTHON_SERVICE_URL', '');
    define('PYTHON_SERVICE_SECRET', '');
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'braille_bridge');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    require 'src/Database.php';
    Database::get();
    echo '  ✓ Tables created' . PHP_EOL;
  "
fi

# 6. Systemd service for Python
echo "[6/6] Creating systemd service..."
cat > /tmp/braille-python.service << 'EOF'
[Unit]
Description=Braille Bridge Python Microservice
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=WORKDIR
EnvironmentFile=WORKDIR/.env
ExecStart=WORKDIR/python-service/.venv/bin/uvicorn main:app --host 127.0.0.1 --port 8001 --workers 2
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

sed -i "s|WORKDIR|$(pwd)|g" /tmp/braille-python.service
cp /tmp/braille-python.service /etc/systemd/system/braille-python.service 2>/dev/null || \
  echo "  (Copy /tmp/braille-python.service to /etc/systemd/system/ manually)"

echo ""
echo "=== Setup Complete ==="
echo "Start Python service: systemctl enable --now braille-python"
echo "Apache document root: $(pwd)/public"
echo "Python service runs on: http://127.0.0.1:8001"
