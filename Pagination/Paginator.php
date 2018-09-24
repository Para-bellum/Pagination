<?php

namespace Parabellum\Pagination;

class Paginator
{
    /**
     * The number of links, in addition to the current and arrows.
     * 
     * @var integer
     */
    protected $max = 6;

    /**
     * The request key containing the page number.
     * 
     * @var string
     */
    protected $index = 'page';

    /**
     * The current page.
     * 
     * @var integer
     */
    private $current;
    
    /**
     * Total amount of records.
     * 
     * @var integer
     */
    private $total; 
    
    /**
     * Amount of records on page.
     * 
     * @var integer
     */
    private $limit;

    /**
     * The request data.
     * 
     * @var array
     */
    protected $source;
    
    /**
     * Create a paginator instance.
     * 
     * @param integer $total
     * @param integer $limit
     * @param array $source
     * 
     * @return void
     */
    public function __construct($total, $limit, array $source)
    {
        $this->total  = $total;
        $this->limit  = $limit;
        $this->source = $source;

        $this->setAmount();
        $this->setCurrent();
    }

    /**
     * Render the pagination code.
     * 
     * @return string|null
     */
    public function render()
    {
        if (!$this->isEmpty()) {
            return sprintf('<ul class="pagination">%s</ul>', join($this->getItems()));
        }
    }
    
    /**
     * Get the pagination items.
     * 
     * @return array
     */
    protected function getItems()
    {
        $range = $this->range();
        
        for ($page = $range[0]; $page <= $range[1]; $page++) {
            $class = $page == $this->current ? 'active' : '';

            $items[] = $this->item($page, $page, '', $class);
        }

        if ($this->current > 1) {
            array_unshift(
                $items,
                $this->item(1, '&laquo;&laquo;', _('First')),
                $this->item($this->current - 1, '&laquo;', _('Previous'))
            );
        }
        
        if ($this->current < $this->amount) {
            array_push(
                $items,
                $this->item($this->current + 1, '&raquo;', _('Next')),
                $this->item($this->amount, '&raquo;&raquo;', _('Last'))
            );
        }
        
        return $items;
    }
    
    /**
     * Get the offset.
     * 
     * @return integer
     */
    public function skip()
    {
        return $this->current * $this->limit - $this->limit;
    }
    
    /**
     * How many records to take.
     * 
     * @return integer
     */
    public function take()
    {
        return $this->limit;
    }
    
    /**
     * Generate the link HTML code.
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
            $title = sprintf('title="%s"', $title);
        }

        return sprintf(
            '<li class="page-item %s"><a href="?%s" %s class="page-link">%s</a></li>',
            $class,
            $this->createQueryString($page),
            $title,
            $text
        );
    }
    
    /**
     * Get the range of display links.
     * 
     * @return array
     */
    protected function range()
    {
        $begin = $this->current - ceil($this->max / 2);

        if ($begin < 1) {
            $begin = 1;
        }

        $end = $begin + $this->max;

        if ($end > $this->amount) {
            $end = $this->amount;

            if (($new = $end - $this->max) > 0) {
                $begin = $new;
            }
        }

        return [$begin, $end];
    }

    /**
     * Set the current page number.
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
     * Build the query string.
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
     * Check if not the links for dislpay.
     * 
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->amount < 2;
    }
    
    /**
     * Set the total amount of pages.
     * 
     * @return void
     */
    protected function setAmount()
    {
        $this->amount = ceil($this->total / $this->limit) ?: 1;
    }
}