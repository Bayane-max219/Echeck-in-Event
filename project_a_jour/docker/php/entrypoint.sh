#!/bin/sh
set -e

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Echeck-in Event — Symfony API Starting..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Attendre MySQL
echo "⏳ Waiting for database..."
until php -r "
try {
    \$dsn = 'mysql:host=db;port=3306;dbname=echeck_in';
    new PDO(\$dsn, 'echeck_user', 'echeck_password');
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
"; do
    echo "   Database not ready, retrying in 3s..."
    sleep 3
done
echo "✅ Database connected!"

# Générer les clés JWT si absentes
if [ ! -f config/jwt/private.pem ]; then
    echo "🔑 Generating JWT keypair..."
    php bin/console lexik:jwt:generate-keypair --overwrite --no-interaction
    echo "✅ JWT keys generated"
fi

# Vider le cache Symfony
echo "🧹 Clearing Symfony cache..."
php bin/console cache:clear --env=prod --no-debug --no-interaction

# Migrations
echo "📦 Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "🚀 Echeck-in Event API is ready!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

exec "$@"
