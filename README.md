# Minimal Commerce

Full-stack e-commerce application with RESTful API backend and Vue.js frontend. Features include authentication, product management, shopping cart, wishlist, and order management.

## Tech Stack

### Backend
- Laravel 12, PHP 8.2+
- PostgreSQL
- Laravel Sanctum (Authentication)
- Swagger/OpenAPI (L5-Swagger)
- PHPUnit

### Frontend
- Vue.js 3
- Vite
- Pinia (State Management)
- Vue Router
- Tailwind CSS
- Axios
- Vue Sonner (Toast Notifications)

## Features

### Public
- User registration and login
- View products and categories
- Product search and filtering

### User
- Shopping cart management
- Product wishlist
- Order placement and checkout
- Order history
- Payment and order cancellation

### Admin
- Product management (CRUD)
- Category management (CRUD)
- Order management
- Order statistics
- Order status updates

## Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- PostgreSQL
- Node.js 20+ and npm

### Backend Setup

1. Clone repository
```bash
git clone <repository-url>
cd minimal-commerce/backend
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Edit `.env` file and configure database settings:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations
```bash
php artisan migrate
```

6. Generate Swagger documentation
```bash
php artisan l5-swagger:generate
```

7. Start server
```bash
php artisan serve
```

API will be available at `http://localhost:8000/api`

### Frontend Setup

1. Navigate to frontend directory
```bash
cd ../frontend
```

2. Install dependencies
```bash
npm install
```

3. Create `.env` file and configure API base URL:
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

4. Start development server
```bash
npm run dev
```

Frontend will be available at `http://localhost:5173`

5. Build for production
```bash
npm run build
```

### Docker Setup

#### Backend

1. Build image
```bash
cd backend
docker build -t ecommerce-api .
```

2. Run container
```bash
docker run -d \
  -p 2465:2465 \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=your_db_host \
  -e DB_PORT=5432 \
  -e DB_DATABASE=ecommerce_db \
  -e DB_USERNAME=your_username \
  -e DB_PASSWORD=your_password \
  ecommerce-api
```

#### Frontend

1. Build image
```bash
cd frontend
docker build -t ecommerce-frontend --build-arg VITE_API_BASE_URL=http://your-api-url/api .
```

2. Run container
```bash
docker run -d -p 2464:2464 ecommerce-frontend
```

Frontend will be available at `http://localhost:2464`

Or use docker-compose if available.

## API Documentation

After the server is running, API documentation can be accessed at:
```
http://localhost:8000/api/documentation
```

### Using Authorization (Bearer Token)

1. **Get the Token**:
   - Expand the `POST /api/login` endpoint.
   - Click **"Try it out"**.
   - Enter your email and password in the Request Body.
   - Click **"Execute"**.
   - Copy the `token` string from the Response body (e.g., `1|abc123xym...`).

2. **Authorize**:
   - Click the **"Authorize"** button (lock icon) at the top or near an endpoint.
   - In the **Value** box for `bearerAuth (http, Bearer)`, paste **only** the token you copied.
     - *Do not add "Bearer " prefix.*
   - Click **"Authorize"**, then **"Close"**.

All endpoints requiring authentication (marked with a lock icon) will now use this token automatically.

### Testing Endpoints

1. Click on an endpoint name (e.g., `GET /api/products`) to expand it.
2. Click the **"Try it out"** button on the right.
3. If the endpoint requires parameters (like `id` or request body), enter them in the respective fields.
4. Click the blue **"Execute"** button.
5. Scroll down to see the **Response Body** (JSON data) and **Response Headers**.

### Compiling Documentation

If you make changes to the API endpoints or annotations, you need to regenerate the Swagger documentation:

```bash
php artisan l5-swagger:generate
```

*Note: In production, auto-generation is disabled by default. Ensure you run this command during your build process.*

## Testing

Run the test suite:
```bash
php artisan test
```

Make sure the testing database configuration is correct in `.env` or `phpunit.xml`.

## Main Endpoints

- `POST /api/register` - Register new user
- `POST /api/login` - Login and get token
- `GET /api/products` - List products
- `GET /api/categories` - List categories
- `GET /api/cart` - Shopping cart (requires auth)
- `POST /api/orders` - Create order (requires auth)
- `GET /api/admin/orders` - List all orders (admin only)

See complete documentation in Swagger UI for details on all endpoints.

## Project Structure

```
minimal-commerce/
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/Api/  
│   │   └── Models/                 
│   ├── database/
│   │   ├── migrations/         
│   │   └── factories/              
│   ├── routes/
│   │   └── api.php               
│   ├── tests/                      
│   └── storage/
│       └── api-docs/               
└── frontend/
    ├── src/
    │   ├── components/             
    │   ├── pages/                  
    │   ├── stores/                
    │   ├── routes/               
    │   └── lib/                  
    └── public/                     
```

