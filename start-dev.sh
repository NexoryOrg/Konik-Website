if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi

PORT=${PORT:-8000}

LOG_DIR="logs"
mkdir -p "$LOG_DIR"

ACCESS_LOG="$LOG_DIR/access.log"
ERROR_LOG="$LOG_DIR/error.log"

echo "🚀 Starte PHP Server"
echo "🌐 URL: http://localhost:$PORT"
echo "📦 ENV: $APP_ENV"
echo "📄 Access-Log: $ACCESS_LOG"
echo "❗ Error-Log:  $ERROR_LOG"

php -d display_errors=$APP_DEBUG \
    -d log_errors=On \
    -S 0.0.0.0:$PORT \
    -t public \
    > >(tee -a "$ACCESS_LOG") \
    2> >(tee -a "$ERROR_LOG" >&2)