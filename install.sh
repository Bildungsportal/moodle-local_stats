script_dir=$(dirname "$0")

# install node_modules for chart-app
cd "$script_dir/chart-app" || exit
npm install --production

