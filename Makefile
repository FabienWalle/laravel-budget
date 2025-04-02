serve:
	concurrently "npm run dev" "php artisan serve"

base_import:
	php artisan import:transactions 25032025_2385443.csv toto@coutougno.fr

