docker exec -it kkk-php sh -c '
echo "===== Composer Validate ====="
composer validate --strict --no-check-publish && echo "===== Composer Validate =====" || echo "Composer validation failed."

echo "===== Composer Audit ====="
composer audit && echo "===== Composer Audit =====" || echo "Composer audit failed."

echo "===== Composer Outdated ====="
composer outdated && echo "===== Composer Outdated =====" || echo "Composer outdated check failed."
'