<?php

namespace Parabellum\Pagination;

class Paginator
{
    /**
     * Количество ссылок, помимо текущей и ссылок-стрелок
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
     * Подготовка навигации к запуску
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

        $this->setAmount();
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

        $range = $this->range();
        
        for ($page = $range[0]; $page <= $range[1]; $page++) {
            $class = $page == $this->current ? 'active' : '';

            $items[] = $this->item($page, $page, '', $class);
        }

        if ($this->current > 1) {
            # Стрелки на предыдущие страницы
            array_unshift(
                $items,
                $this->item(1, '&laquo;&laquo;', 'Первая'),
                $this->item($this->current - 1, '&laquo;', 'Предыдущая')
            );
        }
        
        if ($this->current < $this->amount) {
            # Стрелки на следующие страницы
            array_push(
                $items,
                $this->item($this->current + 1, '&raquo;', 'Следующая'),
                $this->item($this->amount, '&raquo;&raquo;', 'Последняя')
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
     * Создание HTML-кода ссылки
     *
     * @param integer $page
     * @param string $text
     * @param string $title
     * @param string $class
     * 
     * @return string
     */
    protected function item($page, $text, $title = '', $class = '')
    {
        if ($title) {
            $title = ' title="'. $title .'"';
        }

        $query = $this->createQueryString($page);

        return '<li class="page-item '. $class .'"><a href="?'. $query .'"'. $title .' class="page-link">'. $text .'</a></li>';
    }
    
    /**
     * Получения диапазона вывода ссылок
     * 
     * @return array
     */
    protected function range()
    {
        $begin = $this->current - ceil($this->max / 2);

        if ($begin < 1) {
            $begin = 1;
        }

        # Отсчёт от начала
        $end = $begin + $this->max;

        # Конечное положение превышает допустимое
        if ($end > $this->amount) {
            $end = $this->amount;

            # Отсчёт от конца
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
        $this->current = isset($this->source[$this->index]) ? (int) $this->source[$this->index] : 1;

        if ($this->current < 1) {
            $this->current = 1;
        } elseif ($this->current > $this->amount) {
            $this->current = $this->amount;
        }
    }
    
    /**
     * Построение строки запроса
     *
     * @param integer $page
     * 
     * @return string
     */
    protected function createQueryString($page)
    {
        return http_build_query(
            array_merge($this->source, [$this->index => $page])
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