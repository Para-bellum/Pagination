<?php

namespace Pagination;

/*
* Класс для генерации постраничной навигации
*/
class Paginator
{
    /**
     * Ссылок навигации на страницу, помимо текущей (активной) ссылки
     * и ссылок-стрелок
     * 
     * @var integer
     */
    protected $max = 6;
    
    /**
     * Ключ для строки запроса, в котором указывается номер страницы
     * 
     * @var string
     */
    protected $index = 'page';
    
    /**
     * Текущая страница
     * 
     * @var integer
     */
    protected $current;
    
    /**
     * Общее количество записей
     * 
     * @var integer
     */
    protected $total; 
    
    /**
     * Записей на страницу
     * 
     * @var integer
     */
    protected $limit;
    
    /**
     * Массив из элементов текущей строки запроса
     * 
     * @var array
     */
    protected $query;
    
    /**
     * Подготовка к работе с навигацией
     * 
     * @param integer $total
     * @param integer $limit
     * 
     * @return void
     */
    public function __construct($total, $limit)
    {
        $this->total = $total;
        
        $this->limit = $limit;
        
        # Вызываем метод установки количества страниц
        $this->setAmount();
        
        # Вызываем метод установки текущей страницы
        $this->setCurrent();
    }
    
    /**
     * Создание и возврат ссылок навигации
     * 
     * @return string
     */
    public function generate()
    {
        # Если страниц не более одной
        if (!$this->hasPages()) {
            return;
        }
        
        $items = [];
        
        # Получаем ограничения для цикла
        $limits = $this->limits();

        for ($page = $limits[0]; $page <= $limits[1]; $page++) {
            # Формируем статус ссылки
            $status = $page == $this->current ? 'active' : null;
                
            # Заносим ссылку
            $items[] = $this->html($page, null, null, $status);
        }
        
        # Если текущая страница не первая
        if ($this->current > 1) {
            # Добавление ссылок в начало
            array_unshift(
                $items,
                $this->html(1, '&laquo;&laquo;', 'Первая'),
                $this->html($this->current - 1, '&laquo;', 'Предыдущая')
            );
        }
        
        # Если текущая страница не последняя
        if ($this->current < $this->amount) {
            # Добавление ссылок в конец
            array_push(
                $items,
                $this->html($this->current + 1, '&raquo;', 'Следующая'),
                $this->html($this->amount, '&raquo;&raquo;', 'Последняя')
            );
        }
        
        # Возвращаем ссылки
        return '<ul class="pagination">'. implode('', $items) .'</ul>';
    }
    
    /**
     * С какой позиции начинать выборку
     * 
     * @return integer
     */
    public function skip()
    {
        return $this->current * $this->limit - $this->limit;
    }
    
    /**
     * Ограничение выборки
     * 
     * @return integer
     */
    public function take()
    {
        return $this->limit;
    }
    
    /**
     * Для генерации HTML-кода ссылки
     * 
     * @param integer $page
     * @param mixed $text
     * @param string $title
     * @param string $class
     * 
     * @return string
     */
    protected function html($page, $text=null, $title=null, $status=null)
    {
        # Если текст ссылки не указан
        if (is_null($text)) {
            # Указываем, что текст - цифра страницы
            $text = $page;
        }
        
        # Формируем строку запроса
        $query = $this->createQueryString([
            $this->index => $page
        ]);
        
        # Формируем статус ссылки
        if ($status) {
            $status = ' class="'. $status .'"';
        }
            
        # Формируем HTML код ссылки и возвращаем
        return '<li'. $status .'><a href="?'. $query .'" title="'. $title .'">'. $text .'</a></li>';
    }
    
    /**
     * Для получения диапазона выводимых ссылок
     * 
     * @return array
     */
    protected function limits()
    {
        # Вычисляем ссылки слева (чтобы активная ссылка была посередине)
        $left = $this->current - round($this->max / 2, 0, PHP_ROUND_HALF_DOWN);

        # Первая страница в списке
        $start = $left > 0 ? $left : 1;               
        
        # Последняя страница в списке
        $end = $start + $this->max;

        # Если получается превышение общего количества
        if ($end > $this->amount) {
            # Конец - общее количество страниц
            $end = $this->amount;
            
            # Начало - $this->max  с конца
            $start = $this->amount - $this->max;
            
            $start = $start > 0 ? $start : 1;
        }
        
        # Возвращаем
        return [$start, $end];
    }

    /**
     * Для определения текущей страницы
     * 
     * @return void
     */
    protected function setCurrent()
    {
        # Получаем номер страницы
        $this->current = isset($_GET[$this->index]) ? (int) $_GET[$this->index] : 1;
        
        if ($this->current > 0) {
            if ($this->current > $this->amount) {
                # При превышении - сброс на крайнюю с конца
                $this->current = $this->amount;
            }
        } else {
            # Устанавливаем страницу на первую
            $this->current = 1;
        }
    }
    
    /**
     * Для формирования строки запроса
     * 
     * @return string
     */
    protected function createQueryString(array $parameters=[])
    {
        # Если текущая строка запроса не разобрана
        if (is_null($this->query)) {
            # Получаем параметры текущего запроса
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            
            # Разбираем строку запроса
            parse_str($query, $this->query);
        }
        
        # Формируем запрос
        return http_build_query(
            array_merge($this->query, $parameters)
        );
    }
    
    /**
     * Для проверки, есть ли больше одной страницы
     * 
     * @return boolean
     */
    public function hasPages()
    {
        return $this->amount > 1;
    }
    
    /**
     * Установка общего числа страниц
     * 
     * @return void
     */
    protected function setAmount()
    {
        $this->amount = ceil($this->total / $this->limit);
    }
}
