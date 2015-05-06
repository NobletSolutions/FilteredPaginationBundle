<?php

namespace NS\FilteredPaginationBundle\Tests\Filters;

/**
 * Description of Payment
 *
 * @author gnat
 */
class Payment
{
    private $date;
    private $amount;

    /**
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     *
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @param \DateTime $date
     * @return \NS\FilteredPaginationBundle\Tests\Filters\Payment
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     *
     * @param double $amount
     * @return \NS\FilteredPaginationBundle\Tests\Filters\Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
}