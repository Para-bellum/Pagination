<?php

namespace Parabellum\Pagination;

/*
 * Класс для генерации постраничной навигации
 */
class Paginator
{
    /**
     * Активных ссылок навигации на страницу.
     * Т.е. ссылок, помимо текущей (активной) ссылки и ссылок-стрелок.
     * 
     * @var integer
     */
    protected $max = 6;
    
    /**
     * Ключ для запроса, в который пишется номер страницы
     * 
     * @var string
     */
    protected $index = 'page';
    
    /**
     * Текущая страница
     * 
     * @var integer
     */
    private $current;
    
    /**
     * Общее количество записей
     * 
     * @var integer
     */
    private $total; 
    
    /**
     * Записей на страницу
     * 
     * @var integer
     */
    private $limit;

    /**
     * Исходные данные запроса
     * 
     * @var array
     */
    private $source;
    
    /**
     * Запуск необходимых данных для навигации
     * 
     * @param integer $total - общее количество записей
     * @param integer $limit - количество записей на страницу
     * 
     * @return void
     */
    public function __construct($total, $limit)
    {
        $this->total = $total;
        
        $this->limit = $limit;
        
        $this->source = $_GET;
        
        # Установка количества страниц
        $this->setAmount();
        
        # Установка текущей страницы
        $this->setCurrent();
    }
    
    /**
     * Создание и вывод навигации
     * 
     * @return string
     */
    public function generate()
    {
        # Нет страниц для вывода
        if ($this->isEmpty()) {
            return;
        }
        
        $items = [];
        
        # Получение ограничения для цикла
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
            # Добавление ссылок в начало
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
     * Генерация HTML-кода ссылки
     *
     * @param integer $page
     * @param string $text
     * @param string $title
     * @param string $class
     * 
     * @return string
     */
    protected function html($page, $text=null, $title=null, $class=null)
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
            
        # Формируем HTML код ссылки и возвращаем
        return '<li class="page-item '. $class .'"><a href="?'. $query .'" title="'. $title .'" class="page-link">'. $text .'</a></li>';
    }
    
    /**
     * Для получения, откуда стартовать вывод ссылок
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
        
        # Если впереди есть как минимум $this->max страниц
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
     * Для установки текущей страницы
     * 
     * @return void
     */
    protected function setCurrent()
    {
        # Получаем номер страницы
        $this->current = isset($this->source[$this->index]) ? $this->source[$this->index] : 1;

        if ($this->current > 0) {
            if ($this->current > $this->amount) {
                # При превышении - сброс на крайнюю
                $this->current = $this->amount;
            }
        } else {
            # Устанавливаем страницу на первую
            $this->current = 1;
        }
    }
    
    /**
     * Построение строки запроса
     *
     * @param array $parameters
     * 
     * @return string
     */
    protected function createQueryString(array $parameters=[])
    {
        # Формируем запрос
        return http_build_query(
            array_merge($this->source, $parameters)
        );
    }
    
    /**
     * Проверка, нет ли страниц для вывода
     * 
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->amount < 2;
    }
    
    /**
     * Установка общего числа страниц
     * 
     * @return void
     */
    protected function setAmount()
    {
        $this->amount = ceil($this->total / $this->limit) ?: 1;
    }
}
