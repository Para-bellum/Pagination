## Постраничная навигация

Данная постраничная навигация совместима с Bootstrap, поэтому Вам не нужно беспокоиться о стилях для блока навигации.
Если же Вы не используете Bootstrap -- Вам достаточно будет прописать стили в Вашем CSS-файле для следующих классов:
```css
.pagination{
    /* Стиль для общего блока с постраничной навигацией */
}
.pagination li{
    /* Стиль для тегов LI (нужно задать float) */
}
.pagination .active{
    /* Стиль для тега LI с текущей ссылкой */
}
.pagination a{
    /* Стиль для ссылок */
}
```

###### Пример использования
```php
use Pagination\Paginator;

# Получение из базы количества записей
# ...
$count = 100;

$paginator = new Paginator($count, 10);
```
Для ограничения выборки из базы данных используйте следующие методы:
- $paginator->skip()
- $paginator->take()

Например:
```php
$stmt = $db->prepare('SELECT * FROM users LIMIT ?, ?');

$result = $stmt->execute([
    $paginator->skip(),
    $paginator->take()
]);

```php
# Вывод страниц
$paginator->generate();
```
