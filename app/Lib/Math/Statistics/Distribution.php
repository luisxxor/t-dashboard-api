<?php

namespace App\Lib\Math\Statistics;

/**
 * Class Distribution
 * @package App\Math\Statistics
 * @version Feb 12, 2020, 15:24 UTC
 */
class Distribution
{
    protected $values;
    protected $total;
    protected $maxValue;
    protected $minValue;
    protected $range;
    protected $intervalWidth;
    protected $intervals = [];
    protected $arithmeticMean;
    protected $geometricMean;
    protected $harmonicMean;
    protected $median;
    protected $mode;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct( array $values )
    {
        $this->values = $values;
        $this->total = count( $values );
        $this->maxValue = max( $values );
        $this->minValue = min( $values );
        $this->range = $this->maxValue - $this->minValue;
        $this->intervalWidth = round( $this->range / ( 1 + 3.322 * log10( $this->total ) ), 2 );
    }

    public function frequency()
    {
        $𝐹ᵢ =  0;
        for ( $i = $this->minValue ; $i <= $this->maxValue ; $i = $i + $this->intervalWidth ) {

            $𝐿ᵢ     = round( $i, 2 );
            $𝐿₍ᵢ₊₁₎ = round( $i + $this->intervalWidth, 2 );
            $𝑋ᵢ     = round( ( $𝐿ᵢ + $𝐿₍ᵢ₊₁₎ ) / 2, 2 );
            $𝑓ᵢ     = 0;

            foreach ( $this->values as $value ) {
                if ( $value >= $𝐿ᵢ && $value < $𝐿₍ᵢ₊₁₎ ) {
                    $𝑓ᵢ++;
                }
            }

            $𝐹ᵢ = $𝐹ᵢ + $𝑓ᵢ;

            $this->intervals[] = [
                '𝐿ᵢ' => $𝐿ᵢ,
                '𝐿₍ᵢ₊₁₎' => $𝐿₍ᵢ₊₁₎,
                '𝑋ᵢ' => $𝑋ᵢ,
                '𝑓ᵢ' => $𝑓ᵢ,
                '𝐹ᵢ' => $𝐹ᵢ,
            ];
        }

        $this->arithmeticMean = $this->arithmeticMean();
        $this->geometricMean = $this->geometricMean();
        $this->harmonicMean = $this->harmonicMean();
        $this->median = $this->median();
        $this->mode = $this->mode();

        return $this;
    }

    public function toArray()
    {
        return get_object_vars( $this );
        // return [
        //     'values' => $this->values,
        //     'total' => $this->total,
        //     'maxValue' => $this->maxValue,
        //     'minValue' => $this->minValue,
        //     'range' => $this->range,
        //     'intervalWidth' => $this->intervalWidth,
        //     'intervals' => $this->intervals,
        // ];
    }

    protected function ∑𝑓ᵢ⋅𝑋ᵢ()
    {
        $∑𝑓ᵢ⋅𝑋ᵢ = 0.0;

        foreach ( $this->intervals as $interval ) {
            $∑𝑓ᵢ⋅𝑋ᵢ = $∑𝑓ᵢ⋅𝑋ᵢ + ( $interval[ '𝑓ᵢ' ] * $interval[ '𝑋ᵢ' ] );
        }

        return $∑𝑓ᵢ⋅𝑋ᵢ;
    }

    protected function ∏𝑓ᵢ⋅𝑋ᵢ()
    {
        $∏𝑓ᵢ⋅𝑋ᵢ = 0.0;

        foreach ( $this->intervals as $interval ) {
            $multiplication = $interval[ '𝑓ᵢ' ] * $interval[ '𝑋ᵢ' ];
            $∏𝑓ᵢ⋅𝑋ᵢ === 0.0
                ? $∏𝑓ᵢ⋅𝑋ᵢ = $multiplication
                : $∏𝑓ᵢ⋅𝑋ᵢ = $∏𝑓ᵢ⋅𝑋ᵢ * $multiplication ;
        }

        return $∏𝑓ᵢ⋅𝑋ᵢ;
    }

    protected function arithmeticMean()
    {
        $arithmeticMean = $this->∑𝑓ᵢ⋅𝑋ᵢ() / $this->total;

        return round( $arithmeticMean, 2 );
    }

    protected function geometricMean()
    {
        $geometricMean = pow( $this->∏𝑓ᵢ⋅𝑋ᵢ(), 1 / $this->total );

        return round( $geometricMean, 2 );
    }

    protected function harmonicMean()
    {
        $harmonicMean = $this->total / ( 1 / $this->∑𝑓ᵢ⋅𝑋ᵢ() );

        return round( $harmonicMean, 2 );
    }

    protected function median()
    {
        $previous𝑓ᵢ = 0;
        foreach ( $this->intervals as $interval ) {
            if ( $interval[ '𝐹ᵢ' ] >= ( $this->total / 2 ) ) {
                // medianal interval
                break;
            }
            $previous𝑓ᵢ = $interval[ '𝑓ᵢ' ];
        }

        $median =  $interval[ '𝐿ᵢ' ] + ( ( ( $this->total / 2 ) - $previous𝑓ᵢ ) / $interval[ '𝑓ᵢ' ] ) * $this->intervalWidth;

        return round( $median, 2 );
    }

    protected function mode()
    {
         $max = -1; // will hold max val
         $modalInterval = null; // will hold item with max val;

         foreach ( $this->intervals as $interval ) {
            if ( $interval[ '𝑓ᵢ' ] > $max ) {
                $max = $interval[ '𝑓ᵢ' ];
                $modalInterval = $interval;
            }
        }

        $modalIntervalKey = array_search( $modalInterval, $this->intervals );

        $previous𝑓ᵢ = array_key_exists( $modalIntervalKey - 1, $this->intervals ) ? $this->intervals[ $modalIntervalKey - 1 ][ '𝑓ᵢ' ] : 0;
        $next𝑓ᵢ = array_key_exists( $modalIntervalKey + 1, $this->intervals ) ? $this->intervals[ $modalIntervalKey + 1 ][ '𝑓ᵢ' ] : 0;

        $D1 = $modalInterval[ '𝑓ᵢ' ] - $previous𝑓ᵢ;
        $D2 = $modalInterval[ '𝑓ᵢ' ] - $next𝑓ᵢ;

        $mode = $modalInterval[ '𝐿ᵢ' ] + ( $D1 / ( $D1 + $D2 ) ) * $this->intervalWidth;

        return round( $mode, 2 );
    }
}

// Route::get( 'test', function () {

//     $numbers = [
//         150,
//         155,
//         155,
//         157,
//         165,
//         180,
//         210,
//         210,
//         210,
//         250,
//         280
//     ];

//     // $stats = MathPHP\Statistics\Descriptive::describe($numbers, true);

//     // // return [ $stats ];

//     // $p = 0.1;
//     // // Beta distribution
//     // $α      = 1; // shape parameter
//     // $β      = 1; // shape parameter
//     // $x      = 1;
//     // $beta   = new MathPHP\Probability\Distribution\Continuous\Beta($α, $β);
//     // $pdf    = $beta->pdf($x);
//     // $cdf    = $beta->cdf($x);
//     // $icdf   = $beta->inverse($p);
//     // $μ      = $beta->mean();
//     // $median = $beta->median();
//     // // $mode   = $beta->mode();
//     // $σ²     = $beta->variance();

//     // // return compact( 'α','β','x','beta','pdf','cdf','icdf','μ','median','mode','σ²' );

//     // $distribution = [
//     //     'frequency' => MathPHP\Statistics\Distribution::frequency($numbers),
//     //     'relativeFrequency' => MathPHP\Statistics\Distribution::relativeFrequency($numbers),
//     //     'cumulativeFrequency' => MathPHP\Statistics\Distribution::cumulativeFrequency($numbers),
//     //     'cumulativeRelativeFrequency' => MathPHP\Statistics\Distribution::cumulativeRelativeFrequency($numbers),
//     //     'stemAndLeafPlot' => MathPHP\Statistics\Distribution::stemAndLeafPlot($numbers),
//     // ];

//     // // return [ $distribution ];

//     $distribution = new App\Lib\Math\Statistics\Distribution( $numbers );

//     $frequencyDistribution = $distribution->frequency()->toArray();

//     return [
//         $frequencyDistribution,
//     ];
// } );