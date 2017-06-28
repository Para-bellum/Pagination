<?php

namespace Parabellum\Pagination;

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
     * Полготовка навигации к запуску
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
        
        $this->source = $_GET;
        
        # Установка количества страниц
        $this->setAmount();
        
        # Установка текущей страницы
        $this->setCurrent();
    }
    
    /**
     * Создание и вывод навигации
     * 
     * @return string|null
     */
    public function generate()
    {
        # Нет страниц для вывода
        if ($this->isEmpty()) {
            return;
        }
        
        $items = [];
        
        # Получение диапазона номеров страниц
        $range = $this->range();

        for ($page = $range[0]; $page <= $range[1]; $page++) {
            $status = $page == $this->current ? 'active' : null;

            $items[] = $this->html($page, null, null, $status);
        }
        
        # Текущая страница не первая
        if ($this->current > 1) {
            array_unshift(
                $items,
                $this->html(1, '&laquo;&laquo;', 'Первая'),
                $this->html($this->current - 1, '&laquo;', 'Предыдущая')
            );
        }
        
        # Текущая страница не последняя
        if ($this->current < $this->amount) {
            array_push(
                $items,
                $this->html($this->current + 1, '&raquo;', 'Следующая'),
                $this->html($this->amount, '&raquo;&raquo;', 'Последняя')
            );
        }
        
        return '<ul class="pagination">'. implode($items) .'</ul>';
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
     * Сколько записей выбирать
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
    protected function html($page, $text = null, $title = null, $class = null)
    {
        if (!$text) {
            $text = $page;
        }
        
        # Формируем строку запроса
        $query = $this->createQueryString([
            $this->index => $page
        ]);

        if ($title) {
            $title = ' title="'. $title .'"';
        }
            
        # Формируем HTML код ссылки и возвращаем
        return '<li class="page-item '. $class .'"><a href="?'. $query .'"'. $title .' class="page-link">'. $text .'</a></li>';
    }
    
    /**
     * Для получения диапазона вывода ссылок
     * 
     * @return array
     */
    protected function range()
    {
        # Начальное положение (чтобы активная ссылка была посередине)
        $begin = $this->current - round($this->max / 2, 0, PHP_ROUND_HALF_DOWN);

        if ($begin < 1) {
            $begin = 1;
        }
        
        # Конечное положение
        $end = $begin + $this->max;
        
        # Конечное положение превышает допустимое
        if ($end > $this->amount) {
            $end = $this->amount;
            
            if (($new = $end - $this->max) > 0) {
                $begin = $new;
            }
        }
        
        return [$begin, $end];
    }

    /**
     * Установка текущей страницы
     * 
     * @return void
     */
    protected function setCurrent()
    {
        $this->current = isset($this->source[$this->index]) ? $this->source[$this->index] : 1;

        if ($this->current > 0) {
            if ($this->current > $this->amount) {
                $this->current = $this->amount;
            }
        } else {
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
    protected function createQueryString(array $parameters = [])
    {
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
