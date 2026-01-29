#!/bin/bash
set -e

echo "🔧 Prüfe Dev-Umgebung..."

# Prüfe zuerst ob alles schon installiert ist
if command -v php8.3 &> /dev/null && php8.3 -m 2>/dev/null | grep -q 'pdo_mysql'; then
  echo "✅ PHP 8.3 mit PDO MySQL ist bereits verfügbar"
  exit 0
fi

echo "📦 Installiere PHP 8.3 mit MySQL Support..."

# Installiere php8.3-cli und php8.3-mysql - ignoriere apt Fehler (z.B. GPG)
apt-get update 2>&1 | grep -v "GPG error" | grep -v "signatures couldn't be verified" || true
apt-get install -y php8.3-cli php8.3-mysql 2>&1 | tail -5

# Verifiziere dass pdo_mysql geladen ist
echo "✓ Verifiziere Installation..."
if php8.3 -m | grep -q 'pdo_mysql'; then
  echo "✅ PDO MySQL erfolgreich installiert"
else
  echo "❌ PDO MySQL konnte nicht installiert werden"
  exit 1
fi

echo "✅ Dev-Umgebung ist bereit!"
