## ჯოკერი ონლაინ
## Joker online 

Multiplayer online card game powered by Laravel and Bootstrap. Cheater admin and bots included.

<img src=joker.png />

## Usage:
1. Clone this repository
2. `cd joker && cp .env.example .env`
3. `composer install`
4. `npm install && npm run dev`
5. `php artisan storage:link`
6. `php artisan migrate`
7. `php artisan db:seed` [users](database/seeders/DatabaseSeeder.php) password is _password_
8. `php artisan queue:listen`
9. `php artisan websockets:serve`
10. Step 6, or register and play! Verification links are logged in storage/logs/mail.log

or use docker:
```
$ git clone https://github.com/ame1337/joker.git
$ cd joker
$ docker build --tag=joker .
$ docker run -it --rm -v $(pwd):/www -p 80:80 -p 443:443 -p 33060:3306 --name=joker joker
```
## Credits

- [ame1337](https://github.com/ame1337)

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
