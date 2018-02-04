<?php

namespace LupeCode\phpTraderNative\TALib\Core;

use LupeCode\phpTraderNative\TALib\Classes\CandleSetting;
use LupeCode\phpTraderNative\TALib\Classes\MyInteger;
use LupeCode\phpTraderNative\TALib\Enum\CandleSettingType;
use LupeCode\phpTraderNative\TALib\Enum\Compatibility;
use LupeCode\phpTraderNative\TALib\Enum\RangeType;
use LupeCode\phpTraderNative\TALib\Enum\ReturnCode;
use LupeCode\phpTraderNative\TALib\Enum\UnstablePeriodFunctionID;

class StatisticFunctions extends Core
{

    /**
     * @param int      $startIdx
     * @param int      $endIdx
     * @param float[]  $inReal0
     * @param float[]  $inReal1
     * @param int      $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param float[]  $outReal
     *
     * @return int
     */
    public function beta(int $startIdx, int $endIdx, array $inReal0, array $inReal1, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 5;
        } elseif (((int)$optInTimePeriod < 1) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $nbInitialElementNeeded = $optInTimePeriod;
        if ($startIdx < $nbInitialElementNeeded) {
            $startIdx = $nbInitialElementNeeded;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $trailingIdx  = $startIdx - $nbInitialElementNeeded;
        $last_price_x = $trailing_last_price_x = $inReal0[$trailingIdx];
        $last_price_y = $trailing_last_price_y = $inReal1[$trailingIdx];
        $i            = ++$trailingIdx;
        $S_xx         = $S_xy = $S_x = $S_y = 0;
        while ($i < $startIdx) {
            $tmp_real = $inReal0[$i];
            if (!(((-0.00000001) < $last_price_x) && ($last_price_x < 0.00000001))) {
                $x = ($tmp_real - $last_price_x) / $last_price_x;
            } else {
                $x = 0.0;
            }
            $last_price_x = $tmp_real;
            $tmp_real     = $inReal1[$i++];
            if (!(((-0.00000001) < $last_price_y) && ($last_price_y < 0.00000001))) {
                $y = ($tmp_real - $last_price_y) / $last_price_y;
            } else {
                $y = 0.0;
            }
            $last_price_y = $tmp_real;
            $S_xx         += $x * $x;
            $S_xy         += $x * $y;
            $S_x          += $x;
            $S_y          += $y;
        }
        $outIdx = 0;
        $n      = (double)$optInTimePeriod;
        do {
            $tmp_real = $inReal0[$i];
            if (!(((-0.00000001) < $last_price_x) && ($last_price_x < 0.00000001))) {
                $x = ($tmp_real - $last_price_x) / $last_price_x;
            } else {
                $x = 0.0;
            }
            $last_price_x = $tmp_real;
            $tmp_real     = $inReal1[$i++];
            if (!(((-0.00000001) < $last_price_y) && ($last_price_y < 0.00000001))) {
                $y = ($tmp_real - $last_price_y) / $last_price_y;
            } else {
                $y = 0.0;
            }
            $last_price_y = $tmp_real;
            $S_xx         += $x * $x;
            $S_xy         += $x * $y;
            $S_x          += $x;
            $S_y          += $y;
            $tmp_real     = $inReal0[$trailingIdx];
            if (!(((-0.00000001) < $trailing_last_price_x) && ($trailing_last_price_x < 0.00000001))) {
                $x = ($tmp_real - $trailing_last_price_x) / $trailing_last_price_x;
            } else {
                $x = 0.0;
            }
            $trailing_last_price_x = $tmp_real;
            $tmp_real              = $inReal1[$trailingIdx++];
            if (!(((-0.00000001) < $trailing_last_price_y) && ($trailing_last_price_y < 0.00000001))) {
                $y = ($tmp_real - $trailing_last_price_y) / $trailing_last_price_y;
            } else {
                $y = 0.0;
            }
            $trailing_last_price_y = $tmp_real;
            $tmp_real              = ($n * $S_xx) - ($S_x * $S_x);
            if (!(((-0.00000001) < $tmp_real) && ($tmp_real < 0.00000001))) {
                $outReal[$outIdx++] = (($n * $S_xy) - ($S_x * $S_y)) / $tmp_real;
            } else {
                $outReal[$outIdx++] = 0.0;
            }
            $S_xx -= $x * $x;
            $S_xy -= $x * $y;
            $S_x  -= $x;
            $S_y  -= $y;
        } while ($i <= $endIdx);
        $outNBElement->value = $outIdx;
        $outBegIdx->value    = $startIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal0
     * @param array     $inReal1
     * @param int       $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function correl(int $startIdx, int $endIdx, array $inReal0, array $inReal1, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //double $sumXY, $sumX, $sumY, $sumX2, $sumY2, $x, $y, $trailingX, $trailingY;
        //double $tempReal;
        //int $lookbackTotal, $today, $trailingIdx, $outIdx;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 30;
        } elseif (((int)$optInTimePeriod < 1) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $lookbackTotal = $optInTimePeriod - 1;
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outBegIdx->value = $startIdx;
        $trailingIdx      = $startIdx - $lookbackTotal;
        $sumXY            = $sumX = $sumY = $sumX2 = $sumY2 = 0.0;
        for ($today = $trailingIdx; $today <= $startIdx; $today++) {
            $x     = $inReal0[$today];
            $sumX  += $x;
            $sumX2 += $x * $x;
            $y     = $inReal1[$today];
            $sumXY += $x * $y;
            $sumY  += $y;
            $sumY2 += $y * $y;
        }
        $trailingX = $inReal0[$trailingIdx];
        $trailingY = $inReal1[$trailingIdx++];
        $tempReal  = ($sumX2 - (($sumX * $sumX) / $optInTimePeriod)) * ($sumY2 - (($sumY * $sumY) / $optInTimePeriod));
        if (!($tempReal < 0.00000001)) {
            $outReal[0] = ($sumXY - (($sumX * $sumY) / $optInTimePeriod)) / sqrt($tempReal);
        } else {
            $outReal[0] = 0.0;
        }
        $outIdx = 1;
        while ($today <= $endIdx) {
            $sumX      -= $trailingX;
            $sumX2     -= $trailingX * $trailingX;
            $sumXY     -= $trailingX * $trailingY;
            $sumY      -= $trailingY;
            $sumY2     -= $trailingY * $trailingY;
            $x         = $inReal0[$today];
            $sumX      += $x;
            $sumX2     += $x * $x;
            $y         = $inReal1[$today++];
            $sumXY     += $x * $y;
            $sumY      += $y;
            $sumY2     += $y * $y;
            $trailingX = $inReal0[$trailingIdx];
            $trailingY = $inReal1[$trailingIdx++];
            $tempReal  = ($sumX2 - (($sumX * $sumX) / $optInTimePeriod)) * ($sumY2 - (($sumY * $sumY) / $optInTimePeriod));
            if (!($tempReal < 0.00000001)) {
                $outReal[$outIdx++] = ($sumXY - (($sumX * $sumY) / $optInTimePeriod)) / sqrt($tempReal);
            } else {
                $outReal[$outIdx++] = 0.0;
            }
        }
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function linearReg(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //int $outIdx;
        //int $today, $lookbackTotal;
        //double $SumX, $SumXY, $SumY, $SumXSqr, $Divisor;
        //double $m, $b;
        //int $i;
        //double $tempValue1;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 14;
        } elseif (((int)$optInTimePeriod < 2) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $lookbackTotal = (new Lookback())->linearRegLookback($optInTimePeriod);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outIdx  = 0;
        $today   = $startIdx;
        $SumX    = $optInTimePeriod * ($optInTimePeriod - 1) * 0.5;
        $SumXSqr = $optInTimePeriod * ($optInTimePeriod - 1) * (2 * $optInTimePeriod - 1) / 6;
        $Divisor = $SumX * $SumX - $optInTimePeriod * $SumXSqr;
        while ($today <= $endIdx) {
            $SumXY = 0;
            $SumY  = 0;
            for ($i = $optInTimePeriod; $i-- != 0;) {
                $SumY  += $tempValue1 = $inReal[$today - $i];
                $SumXY += (double)$i * $tempValue1;
            }
            $m                  = ($optInTimePeriod * $SumXY - $SumX * $SumY) / $Divisor;
            $b                  = ($SumY - $m * $SumX) / (double)$optInTimePeriod;
            $outReal[$outIdx++] = $b + $m * (double)($optInTimePeriod - 1);
            $today++;
        }
        $outBegIdx->value    = $startIdx;
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function linearRegAngle(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //int $outIdx;
        //int $today, $lookbackTotal;
        //double $SumX, $SumXY, $SumY, $SumXSqr, $Divisor;
        //double $m;
        //int $i;
        //double $tempValue1;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 14;
        } elseif (((int)$optInTimePeriod < 2) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $lookbackTotal = (new Lookback())->linearRegAngleLookback($optInTimePeriod);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outIdx  = 0;
        $today   = $startIdx;
        $SumX    = $optInTimePeriod * ($optInTimePeriod - 1) * 0.5;
        $SumXSqr = $optInTimePeriod * ($optInTimePeriod - 1) * (2 * $optInTimePeriod - 1) / 6;
        $Divisor = $SumX * $SumX - $optInTimePeriod * $SumXSqr;
        while ($today <= $endIdx) {
            $SumXY = 0;
            $SumY  = 0;
            for ($i = $optInTimePeriod; $i-- != 0;) {
                $SumY  += $tempValue1 = $inReal[$today - $i];
                $SumXY += (double)$i * $tempValue1;
            }
            $m                  = ($optInTimePeriod * $SumXY - $SumX * $SumY) / $Divisor;
            $outReal[$outIdx++] = atan($m) * (180.0 / 3.14159265358979323846);
            $today++;
        }
        $outBegIdx->value    = $startIdx;
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function linearRegIntercept(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //int $outIdx;
        //int $today, $lookbackTotal;
        //double $SumX, $SumXY, $SumY, $SumXSqr, $Divisor;
        //double $m;
        //int $i;
        //double $tempValue1;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 14;
        } elseif (((int)$optInTimePeriod < 2) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $lookbackTotal = (new Lookback())->linearRegInterceptLookback($optInTimePeriod);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outIdx  = 0;
        $today   = $startIdx;
        $SumX    = $optInTimePeriod * ($optInTimePeriod - 1) * 0.5;
        $SumXSqr = $optInTimePeriod * ($optInTimePeriod - 1) * (2 * $optInTimePeriod - 1) / 6;
        $Divisor = $SumX * $SumX - $optInTimePeriod * $SumXSqr;
        while ($today <= $endIdx) {
            $SumXY = 0;
            $SumY  = 0;
            for ($i = $optInTimePeriod; $i-- != 0;) {
                $SumY  += $tempValue1 = $inReal[$today - $i];
                $SumXY += (double)$i * $tempValue1;
            }
            $m                  = ($optInTimePeriod * $SumXY - $SumX * $SumY) / $Divisor;
            $outReal[$outIdx++] = ($SumY - $m * $SumX) / (double)$optInTimePeriod;
            $today++;
        }
        $outBegIdx->value    = $startIdx;
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function linearRegSlope(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //int $outIdx;
        //int $today, $lookbackTotal;
        //double $SumX, $SumXY, $SumY, $SumXSqr, $Divisor;
        //int $i;
        //double $tempValue1;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 14;
        } elseif (((int)$optInTimePeriod < 2) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $lookbackTotal = (new Lookback())->linearRegSlopeLookback($optInTimePeriod);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outIdx  = 0;
        $today   = $startIdx;
        $SumX    = $optInTimePeriod * ($optInTimePeriod - 1) * 0.5;
        $SumXSqr = $optInTimePeriod * ($optInTimePeriod - 1) * (2 * $optInTimePeriod - 1) / 6;
        $Divisor = $SumX * $SumX - $optInTimePeriod * $SumXSqr;
        while ($today <= $endIdx) {
            $SumXY = 0;
            $SumY  = 0;
            for ($i = $optInTimePeriod; $i-- != 0;) {
                $SumY  += $tempValue1 = $inReal[$today - $i];
                $SumXY += (double)$i * $tempValue1;
            }
            $outReal[$outIdx++] = ($optInTimePeriod * $SumXY - $SumX * $SumY) / $Divisor;
            $today++;
        }
        $outBegIdx->value    = $startIdx;
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param float     $optInNbDev
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function stdDev(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, float $optInNbDev, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //int $i;
        //ReturnCode $retCode;
        //double $tempReal;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 5;
        } elseif (((int)$optInTimePeriod < 2) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        if ($optInNbDev == (-4e+37)) {
            $optInNbDev = 1.000000e+0;
        } elseif (($optInNbDev < -3.000000e+37) || ($optInNbDev > 3.000000e+37)) {
            return ReturnCode::BadParam;
        }
        $retCode = $this->TA_INT_VAR(
            $startIdx, $endIdx,
            $inReal, $optInTimePeriod,
            $outBegIdx, $outNBElement, $outReal
        );
        if ($retCode != ReturnCode::Success) {
            return $retCode;
        }
        if ($optInNbDev != 1.0) {
            for ($i = 0; $i < (int)$outNBElement->value; $i++) {
                $tempReal = $outReal[$i];
                if (!($tempReal < 0.00000001)) {
                    $outReal[$i] = sqrt($tempReal) * $optInNbDev;
                } else {
                    $outReal[$i] = (double)0.0;
                }
            }
        } else {
            for ($i = 0; $i < (int)$outNBElement->value; $i++) {
                $tempReal = $outReal[$i];
                if (!($tempReal < 0.00000001)) {
                    $outReal[$i] = sqrt($tempReal);
                } else {
                    $outReal[$i] = (double)0.0;
                }
            }
        }

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function tsf(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        //int $outIdx;
        //int $today, $lookbackTotal;
        //double $SumX, $SumXY, $SumY, $SumXSqr, $Divisor;
        //double $m, $b;
        //int $i;
        //double $tempValue1;
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 14;
        } elseif (((int)$optInTimePeriod < 2) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        $lookbackTotal = (new Lookback())->tsfLookback($optInTimePeriod);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outIdx  = 0;
        $today   = $startIdx;
        $SumX    = $optInTimePeriod * ($optInTimePeriod - 1) * 0.5;
        $SumXSqr = $optInTimePeriod * ($optInTimePeriod - 1) * (2 * $optInTimePeriod - 1) / 6;
        $Divisor = $SumX * $SumX - $optInTimePeriod * $SumXSqr;
        while ($today <= $endIdx) {
            $SumXY = 0;
            $SumY  = 0;
            for ($i = $optInTimePeriod; $i-- != 0;) {
                $SumY  += $tempValue1 = $inReal[$today - $i];
                $SumXY += (double)$i * $tempValue1;
            }
            $m                  = ($optInTimePeriod * $SumXY - $SumX * $SumY) / $Divisor;
            $b                  = ($SumY - $m * $SumX) / (double)$optInTimePeriod;
            $outReal[$outIdx++] = $b + $m * (double)$optInTimePeriod;
            $today++;
        }
        $outBegIdx->value    = $startIdx;
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param int       $optInTimePeriod
     * @param float     $optInNbDev
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function variance(int $startIdx, int $endIdx, array $inReal, int $optInTimePeriod, float $optInNbDev, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        if ((int)$optInTimePeriod == (PHP_INT_MIN)) {
            $optInTimePeriod = 5;
        } elseif (((int)$optInTimePeriod < 1) || ((int)$optInTimePeriod > 100000)) {
            return ReturnCode::BadParam;
        }
        if ($optInNbDev == (-4e+37)) {
            $optInNbDev = 1.000000e+0;
        } elseif (($optInNbDev < -3.000000e+37) || ($optInNbDev > 3.000000e+37)) {
            return ReturnCode::BadParam;
        }

        return $this->TA_INT_VAR(
            $startIdx, $endIdx, $inReal,
            $optInTimePeriod,
            $outBegIdx, $outNBElement, $outReal
        );
    }
}