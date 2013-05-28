<?php

class dates
{
    public function trim_date ( $key )
    {
        $key = explode ( 'T', $key );
        $key = $key[0];

        return $key;
    }

    public function getMonths ()
    {
        return $monthsList = array ( '01' => "January",
                                     '02' => "February",
                                     '03' => "March",
                                     '04' => "April",
                                     '05' => "May",
                                     '06' => "June",
                                     '07' => "July",
                                     '08' => "August",
                                     '09' => "September",
                                     '10' => "October",
                                     '11' => "November",
                                     '12' => "December"
        );
    }

    /**
     * getDateYears - builds an array of years for a given start year
     *
     * @param int  $start  - the start year
     * @param int  $before - the amount of years before start date
     * @param int  $after  - the amount of years after
     * @param bool $sort   - sort the years ASC or DESC
     *
     * @return array years!
     */
    public function getDateYears( $start = NULL, $before = NULL, $after = NULL, $sort = NULL )
    {
        if ( empty( $start ) || ( empty( $before ) && empty( $after ) ) ) {
            return false;
        }

        $years = array( $start );

        if ($before) {
            for ($i = 1; $i <= $before; $i++) {
                $years[] = $start - $i;
            }
        }

        if ($after) {
            for ($i = 1; $i <= $after; $i++) {
                $years[] = $start + $i;
            }
        }

        asort ( $years );

        if ($sort) {
            rsort ( $years );
        }

        return $years;
    }

    public function calculate_days ( $date )
    {
        $now = time(); // or your date as well
        $your_date = strtotime( $date );
        $datediff = $now - $your_date;

         return abs( floor( $datediff / ( 60 * 60 * 24 ) ) );
    }

    public function yearsMonthsBetween ( $date1, $date2 )
    {
        $d1 = new DateTime ( $date1 );
        $d2 = new DateTime ( $date2 );

        $diff = $d2->diff ( $d1 );

        // Return array years and months
        return array ( 'years' => $diff->y, 'months' => $diff->m );
    }

    public function livedYears ()
    {
        return $this->getDateYears ( date( 'Y' ), 100, 0, TRUE );
    }

    public function startYears ()
    {
        return $this->getDateYears ( date( 'Y' ), 10, 0, TRUE );
    }

    public function expiryYears ()
    {
        return $this->getDateYears ( date( 'Y' ), 0, 10, FALSE );
    }

}
