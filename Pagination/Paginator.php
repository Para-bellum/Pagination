<?php

namespace Parabellum\Pagination;

class Paginator
{
    /**
     * The number of links, in addition to the current.
     * 
     * @var integer
     */
    protected $sides = 6;

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
     * The pages amount.
     * 
     * @var integer
     */
    private $pages;
    
    /**
     * Total amount of records.
     * 
     * @var integer
     */
    private $total; 
    
    /**
     * Amount of records per page.
     * 
     * @var integer
     */
    private $take;

    /**
     * The base part of query string.
     * 
     * @var array
     */
    protected $query;
    
    /**
     * Create a paginator instance.
     * 
     * @param integer $total
     * @param integer $take
     * @param array $source
     * 
     * @return void
     */
    public function __construct($total, $take, array & $source)
    {
        $this->total  = $total;
        $this->take   = $take;
        $this->source = & $source;

        $this->setPages();
        $this->setCurrent();
        $this->setQuery();
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
            $class = $page == $this->current ? 'active' : null;

            $items[] = $this->item($page, $page, null, $class);
        }

        if ($this->current > 1) {
            array_unshift(
                $items,
                $this->item(1, '&laquo;&laquo;', _('First')),
                $this->item($this->current - 1, '&laquo;', _('Previous'))
            );
        }
        
        if ($this->current < $this->pages) {
            array_push(
                $items,
                $this->item($this->current + 1, '&raquo;', _('Next')),
                $this->item($this->pages, '&raquo;&raquo;', _('Last'))
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
        return $this->current * $this->take - $this->take;
    }
    
    /**
     * How many records to take.
     * 
     * @return integer
     */
    public function take()
    {
        return $this->take;
    }
    
    /**
     * Generate the item HTML.
     *
     * @param integer $page
     * @param string $text
     * @param string $title
     * @param string $class
     * 
     * @return string
     */
    protected function item($page, $text, $title = null, $class = null)
    {
        if (null !== $title) {
            $title = sprintf('title="%s"', $title);
        }
        
        $format = '<li class="page-item %s"><a href="?%s%d" %s class="page-link">%s</a></li>';
        
        return sprintf($format, $class, $this->query, $page, $title, $text);
    }
    
    /**
     * Get the range of display links.
     * 
     * @return array
     */
    protected function range()
    {
        $begin = $this->current - ceil($this->sides / 2);

        if ($begin < 1) {
            $begin = 1;
        }

        $end = $begin + $this->sides;

        if ($end > $this->pages) {
            $end = $this->pages;

            if (($new = $end - $this->sides) > 0) {
                $begin = $new;
            }
        }

        return [$begin, $end];
    }
    
    /**
     * Set the total amount of pages.
     * 
     * @return void
     */
    protected function setPages()
    {
        $this->pages = ceil($this->total / $this->take) ?: 1;
    }

    /**
     * Set the current page number.
     * 
     * @return void
     */
    protected function setCurrent()
    {
        $page = isset($this->source[$this->index]) ? (int) $this->source[$this->index] : 1;

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $this->pages) {
            $page = $this->pages;
        }
        
        $this->current = $page;
    }
    
    /**
     * Set the base part of query string.
     * 
     * @return void
     */
    protected function setQuery()
    {
        $source = $this->source;
        
        unset($source[$this->index]);
        
        $source[$this->index] = '';
        
        $this->query = http_build_query($source);
    }
    
    /**
     * Check if not the links for dislpay.
     * 
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->pages < 2;
    }
}