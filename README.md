<h2>Тестовое задание PHP Developer</h2>

Задание 1:

-------------------

      SELECT usr.id AS ID, CONCAT(usr.first_name, ' ', usr.last_name) AS Name, 
      bks.author AS Author, GROUP_CONCAT(bks.name SEPARATOR ', ') AS Books
      FROM users AS usr
      JOIN user_books AS ub
      ON usr.id = ub.user_id
      JOIN books AS bks
      ON bks.id = ub.book_id
      WHERE DATEDIFF(ub.return_date, ub.get_date) <= 14
      AND TIMESTAMPDIFF(YEAR, usr.birthday, NOW()) BETWEEN 7 AND 17
      GROUP BY usr.id, bks.author
      HAVING COUNT(bks.name) = 2;

------------

Задание 2:

------------
Установка
------------

Клонируем проект
~~~
git clone git@github.com:KurasonIvan/pp_test.git
~~~

Переходим в директорию проекта

~~~
cd pp_test/
~~~

Меняем права для директории runtime:

~~~
sudo chmod -R 777 runtime/
~~~

Ставим зависимости:

~~~
composer install
~~~

Поднимаем docker контейнер:

~~~
docker-compose up -d
~~~

Приложение доступно для использования. <br>
url: "localhost:8088" <br>
тип Authorization: Bearer<br>
token: oTtsLjwzGvaMkXy94UKSWxrec5fR7bg6

------------
Использование
------------
------------
* В некоторых регионах приложение может быть не доступно. Используйте vpn.
------------
Получение курсов валют(по отношению к доллару США):

~~~
url: http://localhost:8088/api/v1?method=rates&currency=RUB
метод: GET
Обязательные заголовки:
Content-Type: "application/json"
Authorization: "Bearer oTtsLjwzGvaMkXy94UKSWxrec5fR7bg6"
~~~

*если параметр currency не передаётся, то возвращается список всех доступных курсов.
в параметр currency можно передавать несколько валют в формате: currency=RUB,EUR,...

------------

Конвертация валют:

~~~
url: http://localhost:8088/api/v1?method=convert
метод: POST
Обязательные заголовки:
Content-Type: "application/json"
Authorization: "Bearer oTtsLjwzGvaMkXy94UKSWxrec5fR7bg6"
~~~

Пример тела запроса:

~~~
{
"currency_from": "USD",
"currency_to": "RUB",
"value": "100"
}
~~~
где currency_from - валюта, которую хотим обменять, <br>
currency_to - валюта, на которую хотим обменять, <br>
value - сумма, которую хотим обменять

------------
пример использования через curl:
------------

Получение курсов:

~~~
curl \
--location \
--request GET \
--header "Authorization: Bearer oTtsLjwzGvaMkXy94UKSWxrec5fR7bg6" \
--header "Content-Type: application/json" \
"http://localhost:8088/api/v1?method=rates"
~~~

Конвертация:

~~~
curl \
--location \
--request POST \
--header "Authorization: Bearer oTtsLjwzGvaMkXy94UKSWxrec5fR7bg6" \
--header "Content-Type: application/json" \
--data '{
"currency_from": "btc",
"currency_to": "rub",
"value": "0.1"
}' \
"http://localhost:8088/api/v1?method=convert"
~~~