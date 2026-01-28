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

if ! php -m | grep -q 'pdo_mysql'; then
  echo "💡 PDO MySQL Treiber fehlt, wird installiert..."
  sudo apt update && sudo apt install -y php8.2-mysql
fi

MYSQL_DATA_DIR="/var/lib/mysql"
if [ ! -d "$MYSQL_DATA_DIR/mysql" ]; then
  echo "⚡ Initialisiere MySQL Datenbank..."
  sudo mysqld --initialize-insecure --user=mysql --datadir="$MYSQL_DATA_DIR"
  sudo service mysql start

  sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
  sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';"
  sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'%';"
  sudo mysql -e "FLUSH PRIVILEGES;"
else
  sudo service mysql start
fi

php -d display_errors=$APP_DEBUG \
    -d log_errors=On \
    -S 0.0.0.0:$PORT \
    -t public \
    > >(tee -a "$ACCESS_LOG") \
    2> >(tee -a "$ERROR_LOG" >&2)
