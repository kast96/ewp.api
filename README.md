# API &middot; ![bitrix](https://img.shields.io/badge/bitrix-module-orange)

Модуль для работы API для CMS 1C-Битрикс


> Версия модуля **main** должна быть выше 21.400.0м

## Get Started

### Установка

Установка производится в папку `/local/modules`.

```sh
git clone git@github.com:kast96/ewp.api.git
```

Далее устанавливаем модуль через админку стандартным функционалом Битрикса.

В `.htaccess` заменяем старый файл-обработчик роутинга на новый:
```sh
#RewriteCond %{REQUEST_FILENAME} !/bitrix/urlrewrite.php$
#RewriteRule ^(.*)$ /bitrix/urlrewrite.php [L]

RewriteCond %{REQUEST_FILENAME} !/bitrix/routing_index.php$
RewriteRule ^(.*)$ /bitrix/routing_index.php [L]
```

### Проверка работы

При переходе по url `/api`, сайт должен отдать приветственное сообщение.

### Принцип работы

API работает на Битриксовых [Роутингах](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&CHAPTER_ID=013764&LESSON_PATH=3913.3516.5062.13764) и [Контроллерах](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&CHAPTER_ID=03750&LESSON_PATH=3913.3516.5062.3750).

При установке в конфиг `/bitrix/.settings.php` автоматически добавляется новое значение роутига, которое указывает название файла-обработчика для роутинга.
```sh
'routing' => array(
    'value' => array(
      'config' => array(
        0 => 'ewp_api.php',
      ),
    ),
    'readonly' => false,
  ),
```

Сам файл роутинга находится по пути `/bitrix/routes/ewp_api.php`. Файл автоматически создается при установке модуля.
Он, в свою очередь, подключает нужный файл внутри модуля, где описываются роуты.

### Роутинг

Роуты находятся внутри папки модуля `/routes`.

`/routes/routes.php` - точка входа для роутингов. Файл служит для управления логикой подключения нужного роутинг-конфигуратора.
Например, В зависимости от определенных условий может подключаться другая версия API.
> На данный момент существет только одна версия api - v1. По-этому, в файле пока нет логических условий, только подключение единственного роутинг-конфигуратора

`/routes/v1/routingConfigurator.php` - Роутинг-конфигуратор для API v1. Здесь указываются роутинги API.

### Контроллеры, фильтры событий и логика API

Контроллеры, фильтры событий и вспомогательные функции, классы, объекты и т.п., относящиеся к определенный версии API, находятся по пути `/lib/v1` относительно модуля.
Где v1 - раздел с названием версии API.

#### Контроллеры

Раздел с контроллерами: `/lib/v1/controllers`.

Файлы котроллеров должны иметь постфикс `Controller`. Например, `productsController`, `usersController`.

Контроллер лучше наследовать от объекта `\Ewp\Api\V1\Controllers\Controller`. Который, в свою очередь наследуется от `\Bitrix\Main\Engine\Controller`. В нем находятся общие методы для улучшения разработки.

События контроллера, те что указываются в роутинге, в роутинг-кофигураторе, должны иметь постфикс `Action`
```sh
class ProductsController extends BaseController
{
    public function listAction() :? array
    {
        ...
    }
}
```

Вызов этого метода в роутинге:

```sh
$routes->any(API_PATH.'/products', [ProductsController::class, 'list'])->methods(['GET', 'OPTIONS']);
```

#### Фильтры событий

Раздел с фильтрами событий: `/lib/v1/actionfilter`.

Фильтры - это обработчики, которые выполняются до или после Action. С их помощью можно отклонить выполнение действия, либо повлиять на результат действия.

Фильтр должен быть наследован от объекта `\Bitrix\Main\Engine\ActionFilter\Base`.