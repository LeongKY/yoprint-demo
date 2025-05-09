
# Project Title

YoPrint Laravel Engineering Challenge

## Features

Users will be able to upload CSV files into our system. Once uploaded, we will process the file in the background. 
We will then notify the user when the process completes. We will also show the user a history of all the file uploads.

## Installation

Clone the repository:

```bash
git clone https://github.com/LeongKY/yoprint-demo.git
cd yoprint-demo
```

Install dependencies:

```bash
npm install
composer install
```

## Usage

Basic usage instructions:

```bash
npm run build
# and
php artisan migrate
# and
php artisan horizon
# and
php artisan reverb:start --port=6001
# and
php artisan serve
```

## Environment file setup

You should also set the variable in order for the code to work properly:

```bash
APP_TIMEZONE=Asia/Kuala_Lumpur
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
DB_CONNECTION=mysql
DB_DATABASE=yoprint_demo
DB_USERNAME="Your DB username"
DB_PASSWORD="Your DB password"
REDIS_CLIENT=predis
VITE_APP_NAME="${APP_NAME}"
VITE_APP_ENV=local
REVERB_HOST=127.0.0.1
REVERB_PORT=6001
REVERB_APP_ID="Generate a id"
REVERB_APP_KEY="Generate a key"
REVERB_APP_SECRET="Generate a secret"
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
REVERB_SCHEME=http
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you'd like to change.

## License

[MIT](LICENSE)
