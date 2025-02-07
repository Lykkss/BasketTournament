# Symfony Project

This is a Symfony project that includes user authentication, admin creation, and role-based redirection.

## Requirements

- PHP 8.1 or higher
- Composer
- Symfony CLI
- Node.js and npm (for frontend assets)

## Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/your-repo/symfony-project.git
    cd symfony-project
    ```

2. Install PHP dependencies:

    ```sh
    composer install
    ```

3. Install JavaScript dependencies:

    ```sh
    npm install
    ```

4. Create and configure the [.env](http://_vscodecontentref_/0) file:

    ```sh
    cp .env .env.local
    ```

    Update the `.env.local` file with your database credentials and other configuration settings.

5. Run database migrations:

    ```sh
    php bin/console doctrine:migrations:migrate
    ```

6. Build frontend assets:

    ```sh
    npm run dev
    ```

## Usage

### Running the Server

Start the Symfony server:

```sh
symfony server:start

Collecting workspace information

Sure, here is a basic `README.md` for your project:

```markdown
# Symfony Project

This is a Symfony project that includes user authentication, admin creation, and role-based redirection.

## Requirements

- PHP 8.1 or higher
- Composer
- Symfony CLI
- Node.js and npm (for frontend assets)

## Installation

1. Clone the repository:

    ```sh
    git clone git@github.com:Lykkss/BasketTournament.git
    cd symfony-project
    ```

2. Install PHP dependencies:

    ```sh
    composer install
    ```

3. Install JavaScript dependencies:

    ```sh
    npm install
    ```

4. Create and configure the 

.env

 file:

    ```sh
    cp .env .env.local
    ```

    Update the `.env.local` file with your database credentials and other configuration settings.

5. Run database migrations:

    ```sh
    php bin/console doctrine:migrations:migrate
    ```

6. Build frontend assets:

    ```sh
    npm run dev
    ```

## Usage

### Running the Server

Start the Symfony server:

```sh
symfony server:start
```

### Creating an Admin User

To create an admin user, run the following command:

php bin/console doctrine:fixtures:load

```sh
php bin/console doctrine:fixtures:load
```

This will create an admin user with the email `admin@example.com` and password `admin123`.

### Accessing the Application

- Visit `/login` to log in as a user.
- Visit `/admin-login` to log in as an admin.
- Visit `/create-admin` to create a new admin user.

## Project Structure

- 

Controller

: Contains the controllers for handling HTTP requests.
- 

Entity

: Contains the entities representing the database tables.
- 

templates

: Contains the Twig templates for rendering HTML.
- 

public

: Contains the publicly accessible files, including frontend assets.

## Routes

- `/login`: User login page.
- `/logout`: User logout route.
- `/redirect`: Redirects users after login based on their roles.
- `/admin-login`: Admin login route.
- `/create-admin`: Route to create a new admin user.

## License

This project is licensed under the MIT License.
```

Feel free to customize this `README.md` file according to your project's specific details and requirements.
Feel free to customize this `README.md` file according to your project's specific details and requirements.