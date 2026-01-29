if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

PORT=${PORT:-8000}

LOG_DIR="logs"
mkdir -p "$LOG_DIR"
ACCESS_LOG="$LOG_DIR/access.log"
ERROR_LOG="$LOG_DIR/error.log"

echo "🚀 Starte PHP Server auf Port $PORT ..."
echo "📄 Access-Log: $ACCESS_LOG"
echo "❗ Error-Log:  $ERROR_LOG"
echo "🌐 ENV: $APP_ENV"

# IMMER: init-dev.sh ausführen um Pakete zu prüfen/installieren
echo "🔧 Stelle Umgebung sicher..."
sudo bash init-dev.sh

# Nutze PHP 8.3 mit pdo_mysql
export PHP_BIN=php8.3
echo "✅ Verwende PHP 8.3 mit pdo_mysql"

MYSQL_DATA_DIR="/var/lib/mysql"
if [ ! -d "$MYSQL_DATA_DIR/mysql" ]; then
  echo "⚡ Initialisiere MySQL Datenbank..."
  sudo mysqld --initialize-insecure --user=mysql --datadir="$MYSQL_DATA_DIR" 2>/dev/null || true
  sudo service mysql start 2>/dev/null || true

  sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true
  sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';" 2>/dev/null || true
  sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'%';" 2>/dev/null || true
  sudo mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true
else
  sudo service mysql start 2>/dev/null || true
fi

${PHP_BIN:-php} -d display_errors=$APP_DEBUG \
    -d log_errors=On \
    -S 0.0.0.0:$PORT \
    -t public 2>&1 | tee -a "$ACCESS_LOG" "$ERROR_LOG"
