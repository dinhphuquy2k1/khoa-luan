echo "Production building version..."

echo "pulling from origin..."
git fetch --all && git reset --hard origin/main && git pull

echo "Database migrating..."
php artisan migrate

echo "production bundle asset building..."
npm run prod

echo "optimizing..."
php artisan optimize:clear
