# user_discount

## Описание, установка

Реализовал как понял. Понял так:

При получении скидки, создать реальную скидку с купоном, привязать ее к юзеру. Скидка активна 3 часа с момента создания. Если мы пытаемся создать ее заново,  то в течении 1 часа после получения скидки она не меняется, больше часа - удаляем старую и создаем новую.

Реализовал это в виде компонента. По сути достаточно установить этот компонент и функционал будет работать.
Компонент ставится традиционно: кидаем папку **ts** в **/local/components** или **/bitrix/components**

Далее создаем страницу и добавляем в нее компонент: ts_components - user_discount - user_discount.

или так:
```
<?$APPLICATION->IncludeComponent(
	"ts:user_discount",
	"",
Array()
);?>
```

Перед установкой, компонента рекомендуется выполнить код из файла **user_fields.php** в **Командная PHP-строка**.

Этот код создаст пользовательские поля у пользователей, куда будут записываться коды купонов. Это необязательно, функционал обозначенный в рамках задачи, будет работать и без этого. Однако это придает удобство если надо взять купон напрямую из пользователя не обращаясь к функционалу компонента.

Во фронтэндовой части эти пользовательские поля в рамках задачи никак не задействованы.


## Недостатки, недоработки

В компоненте происходит избыточная выборка и передача избыточного количества данных. Можно сказать, что это задел на будущее, если придется расширять функционал.

В данной реализации сделано так, что для каждого пользователя при запросе формируется отдельная скидка и купон к ней. И хотя во время очередных запросов когда скидка становится неактуальной она удаляется, при большом количестве пользователей может насоздаваться большое количество скидок и это плохо будет влиять на производительность.

Решением тут может быть такая схема: формируется 50 скидок (от 1% до 50%), и уже к ним привязываются или удаляются купоны пользователей. Однако тут стоит быть осторожным, т.к. если все купоны удалятся у скидки, она становится доступной для всех без купонов. Это надо как-то отслеживать или не допускать такого состояния. Сложность тут повышается и уже, я думаю, выходит за рамки тестового задания.

