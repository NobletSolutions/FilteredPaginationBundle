<?php

namespace NS\FilteredPaginationBundle\Tests\Filters;

use DateTime;

class Payment
{
    private DateTime $date;
    private $amount;

    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @param double $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }
}
