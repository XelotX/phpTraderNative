<?php

namespace LupeCode\phpTraderNative\TALib\Core;

use LupeCode\phpTraderNative\TALib\Classes\CandleSetting;
use LupeCode\phpTraderNative\TALib\Classes\MyInteger;
use LupeCode\phpTraderNative\TALib\Enum\CandleSettingType;
use LupeCode\phpTraderNative\TALib\Enum\Compatibility;
use LupeCode\phpTraderNative\TALib\Enum\RangeType;
use LupeCode\phpTraderNative\TALib\Enum\ReturnCode;
use LupeCode\phpTraderNative\TALib\Enum\UnstablePeriodFunctionID;

class CycleIndicators extends Core
{

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function htDcPeriod(int $startIdx, int $endIdx, array $inReal, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        $a              = 0.0962;
        $b              = 0.5769;
        $detrender_Odd  = $this->double(3);
        $detrender_Even = $this->double(3);
        $Q1_Odd         = $this->double(3);
        $Q1_Even        = $this->double(3);
        $jI_Odd         = $this->double(3);
        $jI_Even        = $this->double(3);
        $jQ_Odd         = $this->double(3);
        $jQ_Even        = $this->double(3);
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        $rad2Deg       = 180.0 / (4.0 * atan(1));
        $lookbackTotal = 32 + ($this->unstablePeriod[UnstablePeriodFunctionID::HtDcPeriod]);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outBegIdx->value = $startIdx;
        $trailingWMAIdx   = $startIdx - $lookbackTotal;
        $today            = $trailingWMAIdx;
        $tempReal         = $inReal[$today++];
        $periodWMASub     = $tempReal;
        $periodWMASum     = $tempReal;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 2.0;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 3.0;
        $trailingWMAValue = 0.0;
        $i                = 9;
        do {
            $tempReal = $inReal[$today++];
            {
                $periodWMASub     += $tempReal;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $tempReal * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
        } while (--$i != 0);
        $hilbertIdx = 0;
        {
            $detrender_Odd[0]          = 0.0;
            $detrender_Odd[1]          = 0.0;
            $detrender_Odd[2]          = 0.0;
            $detrender_Even[0]         = 0.0;
            $detrender_Even[1]         = 0.0;
            $detrender_Even[2]         = 0.0;
            $detrender                 = 0.0;
            $prev_detrender_Odd        = 0.0;
            $prev_detrender_Even       = 0.0;
            $prev_detrender_input_Odd  = 0.0;
            $prev_detrender_input_Even = 0.0;
        };
        {
            $Q1_Odd[0]          = 0.0;
            $Q1_Odd[1]          = 0.0;
            $Q1_Odd[2]          = 0.0;
            $Q1_Even[0]         = 0.0;
            $Q1_Even[1]         = 0.0;
            $Q1_Even[2]         = 0.0;
            $Q1                 = 0.0;
            $prev_Q1_Odd        = 0.0;
            $prev_Q1_Even       = 0.0;
            $prev_Q1_input_Odd  = 0.0;
            $prev_Q1_input_Even = 0.0;
        };
        {
            $jI_Odd[0]          = 0.0;
            $jI_Odd[1]          = 0.0;
            $jI_Odd[2]          = 0.0;
            $jI_Even[0]         = 0.0;
            $jI_Even[1]         = 0.0;
            $jI_Even[2]         = 0.0;
            $jI                 = 0.0;
            $prev_jI_Odd        = 0.0;
            $prev_jI_Even       = 0.0;
            $prev_jI_input_Odd  = 0.0;
            $prev_jI_input_Even = 0.0;
        };
        {
            $jQ_Odd[0]          = 0.0;
            $jQ_Odd[1]          = 0.0;
            $jQ_Odd[2]          = 0.0;
            $jQ_Even[0]         = 0.0;
            $jQ_Even[1]         = 0.0;
            $jQ_Even[2]         = 0.0;
            $jQ                 = 0.0;
            $prev_jQ_Odd        = 0.0;
            $prev_jQ_Even       = 0.0;
            $prev_jQ_input_Odd  = 0.0;
            $prev_jQ_input_Even = 0.0;
        };
        $period        = 0.0;
        $outIdx        = 0;
        $prevI2        = $prevQ2 = 0.0;
        $Re            = $Im = 0.0;
        $I1ForOddPrev3 = $I1ForEvenPrev3 = 0.0;
        $I1ForOddPrev2 = $I1ForEvenPrev2 = 0.0;
        $smoothPeriod  = 0.0;
        while ($today <= $endIdx) {
            $adjustedPrevPeriod = (0.075 * $period) + 0.54;
            $todayValue         = $inReal[$today];
            {
                $periodWMASub     += $todayValue;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $todayValue * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
            if (($today % 2) == 0) {
                {
                    $hilbertTempReal             = $a * $smoothedValue;
                    $detrender                   = -$detrender_Even[$hilbertIdx];
                    $detrender_Even[$hilbertIdx] = $hilbertTempReal;
                    $detrender                   += $hilbertTempReal;
                    $detrender                   -= $prev_detrender_Even;
                    $prev_detrender_Even         = $b * $prev_detrender_input_Even;
                    $detrender                   += $prev_detrender_Even;
                    $prev_detrender_input_Even   = $smoothedValue;
                    $detrender                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $detrender;
                    $Q1                   = -$Q1_Even[$hilbertIdx];
                    $Q1_Even[$hilbertIdx] = $hilbertTempReal;
                    $Q1                   += $hilbertTempReal;
                    $Q1                   -= $prev_Q1_Even;
                    $prev_Q1_Even         = $b * $prev_Q1_input_Even;
                    $Q1                   += $prev_Q1_Even;
                    $prev_Q1_input_Even   = $detrender;
                    $Q1                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $I1ForEvenPrev3;
                    $jI                   = -$jI_Even[$hilbertIdx];
                    $jI_Even[$hilbertIdx] = $hilbertTempReal;
                    $jI                   += $hilbertTempReal;
                    $jI                   -= $prev_jI_Even;
                    $prev_jI_Even         = $b * $prev_jI_input_Even;
                    $jI                   += $prev_jI_Even;
                    $prev_jI_input_Even   = $I1ForEvenPrev3;
                    $jI                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $Q1;
                    $jQ                   = -$jQ_Even[$hilbertIdx];
                    $jQ_Even[$hilbertIdx] = $hilbertTempReal;
                    $jQ                   += $hilbertTempReal;
                    $jQ                   -= $prev_jQ_Even;
                    $prev_jQ_Even         = $b * $prev_jQ_input_Even;
                    $jQ                   += $prev_jQ_Even;
                    $prev_jQ_input_Even   = $Q1;
                    $jQ                   *= $adjustedPrevPeriod;
                };
                if (++$hilbertIdx == 3) {
                    $hilbertIdx = 0;
                }
                $Q2            = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2            = (0.2 * ($I1ForEvenPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForOddPrev3 = $I1ForOddPrev2;
                $I1ForOddPrev2 = $detrender;
            } else {
                {
                    $hilbertTempReal            = $a * $smoothedValue;
                    $detrender                  = -$detrender_Odd[$hilbertIdx];
                    $detrender_Odd[$hilbertIdx] = $hilbertTempReal;
                    $detrender                  += $hilbertTempReal;
                    $detrender                  -= $prev_detrender_Odd;
                    $prev_detrender_Odd         = $b * $prev_detrender_input_Odd;
                    $detrender                  += $prev_detrender_Odd;
                    $prev_detrender_input_Odd   = $smoothedValue;
                    $detrender                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $detrender;
                    $Q1                  = -$Q1_Odd[$hilbertIdx];
                    $Q1_Odd[$hilbertIdx] = $hilbertTempReal;
                    $Q1                  += $hilbertTempReal;
                    $Q1                  -= $prev_Q1_Odd;
                    $prev_Q1_Odd         = $b * $prev_Q1_input_Odd;
                    $Q1                  += $prev_Q1_Odd;
                    $prev_Q1_input_Odd   = $detrender;
                    $Q1                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $I1ForOddPrev3;
                    $jI                  = -$jI_Odd[$hilbertIdx];
                    $jI_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jI                  += $hilbertTempReal;
                    $jI                  -= $prev_jI_Odd;
                    $prev_jI_Odd         = $b * $prev_jI_input_Odd;
                    $jI                  += $prev_jI_Odd;
                    $prev_jI_input_Odd   = $I1ForOddPrev3;
                    $jI                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $Q1;
                    $jQ                  = -$jQ_Odd[$hilbertIdx];
                    $jQ_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jQ                  += $hilbertTempReal;
                    $jQ                  -= $prev_jQ_Odd;
                    $prev_jQ_Odd         = $b * $prev_jQ_input_Odd;
                    $jQ                  += $prev_jQ_Odd;
                    $prev_jQ_input_Odd   = $Q1;
                    $jQ                  *= $adjustedPrevPeriod;
                };
                $Q2             = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2             = (0.2 * ($I1ForOddPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForEvenPrev3 = $I1ForEvenPrev2;
                $I1ForEvenPrev2 = $detrender;
            }
            $Re       = (0.2 * (($I2 * $prevI2) + ($Q2 * $prevQ2))) + (0.8 * $Re);
            $Im       = (0.2 * (($I2 * $prevQ2) - ($Q2 * $prevI2))) + (0.8 * $Im);
            $prevQ2   = $Q2;
            $prevI2   = $I2;
            $tempReal = $period;
            if (($Im != 0.0) && ($Re != 0.0)) {
                $period = 360.0 / (atan($Im / $Re) * $rad2Deg);
            }
            $tempReal2 = 1.5 * $tempReal;
            if ($period > $tempReal2) {
                $period = $tempReal2;
            }
            $tempReal2 = 0.67 * $tempReal;
            if ($period < $tempReal2) {
                $period = $tempReal2;
            }
            if ($period < 6) {
                $period = 6;
            } elseif ($period > 50) {
                $period = 50;
            }
            $period       = (0.2 * $period) + (0.8 * $tempReal);
            $smoothPeriod = (0.33 * $period) + (0.67 * $smoothPeriod);
            if ($today >= $startIdx) {
                $outReal[$outIdx++] = $smoothPeriod;
            }
            $today++;
        }
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outReal
     *
     * @return int
     */
    public function htDcPhase(int $startIdx, int $endIdx, array $inReal, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outReal): int
    {
        $a                       = 0.0962;
        $b                       = 0.5769;
        $detrender_Odd           = $this->double(3);
        $detrender_Even          = $this->double(3);
        $Q1_Odd                  = $this->double(3);
        $Q1_Even                 = $this->double(3);
        $jI_Odd                  = $this->double(3);
        $jI_Even                 = $this->double(3);
        $jQ_Odd                  = $this->double(3);
        $jQ_Even                 = $this->double(3);
        $smoothPrice_Idx         = 0;
        $maxIdx_smoothPricePrice = (50 - 1);
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        {
            $smoothPrice = $this->double($maxIdx_smoothPricePrice + 1);
        };
        $tempReal          = atan(1);
        $rad2Deg           = 45.0 / $tempReal;
        $constDeg2RadBy360 = $tempReal * 8.0;
        $lookbackTotal     = 63 + ($this->unstablePeriod[UnstablePeriodFunctionID::HtDcPhase]);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outBegIdx->value = $startIdx;
        $trailingWMAIdx   = $startIdx - $lookbackTotal;
        $today            = $trailingWMAIdx;
        $tempReal         = $inReal[$today++];
        $periodWMASub     = $tempReal;
        $periodWMASum     = $tempReal;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 2.0;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 3.0;
        $trailingWMAValue = 0.0;
        $i                = 34;
        do {
            $tempReal = $inReal[$today++];
            {
                $periodWMASub     += $tempReal;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $tempReal * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
        } while (--$i != 0);
        $hilbertIdx = 0;
        {
            $detrender_Odd[0]          = 0.0;
            $detrender_Odd[1]          = 0.0;
            $detrender_Odd[2]          = 0.0;
            $detrender_Even[0]         = 0.0;
            $detrender_Even[1]         = 0.0;
            $detrender_Even[2]         = 0.0;
            $detrender                 = 0.0;
            $prev_detrender_Odd        = 0.0;
            $prev_detrender_Even       = 0.0;
            $prev_detrender_input_Odd  = 0.0;
            $prev_detrender_input_Even = 0.0;
        };
        {
            $Q1_Odd[0]          = 0.0;
            $Q1_Odd[1]          = 0.0;
            $Q1_Odd[2]          = 0.0;
            $Q1_Even[0]         = 0.0;
            $Q1_Even[1]         = 0.0;
            $Q1_Even[2]         = 0.0;
            $Q1                 = 0.0;
            $prev_Q1_Odd        = 0.0;
            $prev_Q1_Even       = 0.0;
            $prev_Q1_input_Odd  = 0.0;
            $prev_Q1_input_Even = 0.0;
        };
        {
            $jI_Odd[0]          = 0.0;
            $jI_Odd[1]          = 0.0;
            $jI_Odd[2]          = 0.0;
            $jI_Even[0]         = 0.0;
            $jI_Even[1]         = 0.0;
            $jI_Even[2]         = 0.0;
            $jI                 = 0.0;
            $prev_jI_Odd        = 0.0;
            $prev_jI_Even       = 0.0;
            $prev_jI_input_Odd  = 0.0;
            $prev_jI_input_Even = 0.0;
        };
        {
            $jQ_Odd[0]          = 0.0;
            $jQ_Odd[1]          = 0.0;
            $jQ_Odd[2]          = 0.0;
            $jQ_Even[0]         = 0.0;
            $jQ_Even[1]         = 0.0;
            $jQ_Even[2]         = 0.0;
            $jQ                 = 0.0;
            $prev_jQ_Odd        = 0.0;
            $prev_jQ_Even       = 0.0;
            $prev_jQ_input_Odd  = 0.0;
            $prev_jQ_input_Even = 0.0;
        };
        $period        = 0.0;
        $outIdx        = 0;
        $prevI2        = $prevQ2 = 0.0;
        $Re            = $Im = 0.0;
        $I1ForOddPrev3 = $I1ForEvenPrev3 = 0.0;
        $I1ForOddPrev2 = $I1ForEvenPrev2 = 0.0;
        $smoothPeriod  = 0.0;
        for ($i = 0; $i < 50; $i++) {
            $smoothPrice[$i] = 0.0;
        }
        $DCPhase = 0.0;
        while ($today <= $endIdx) {
            $adjustedPrevPeriod = (0.075 * $period) + 0.54;
            $todayValue         = $inReal[$today];
            {
                $periodWMASub     += $todayValue;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $todayValue * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
            $smoothPrice[$smoothPrice_Idx] = $smoothedValue;
            if (($today % 2) == 0) {
                {
                    $hilbertTempReal             = $a * $smoothedValue;
                    $detrender                   = -$detrender_Even[$hilbertIdx];
                    $detrender_Even[$hilbertIdx] = $hilbertTempReal;
                    $detrender                   += $hilbertTempReal;
                    $detrender                   -= $prev_detrender_Even;
                    $prev_detrender_Even         = $b * $prev_detrender_input_Even;
                    $detrender                   += $prev_detrender_Even;
                    $prev_detrender_input_Even   = $smoothedValue;
                    $detrender                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $detrender;
                    $Q1                   = -$Q1_Even[$hilbertIdx];
                    $Q1_Even[$hilbertIdx] = $hilbertTempReal;
                    $Q1                   += $hilbertTempReal;
                    $Q1                   -= $prev_Q1_Even;
                    $prev_Q1_Even         = $b * $prev_Q1_input_Even;
                    $Q1                   += $prev_Q1_Even;
                    $prev_Q1_input_Even   = $detrender;
                    $Q1                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $I1ForEvenPrev3;
                    $jI                   = -$jI_Even[$hilbertIdx];
                    $jI_Even[$hilbertIdx] = $hilbertTempReal;
                    $jI                   += $hilbertTempReal;
                    $jI                   -= $prev_jI_Even;
                    $prev_jI_Even         = $b * $prev_jI_input_Even;
                    $jI                   += $prev_jI_Even;
                    $prev_jI_input_Even   = $I1ForEvenPrev3;
                    $jI                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $Q1;
                    $jQ                   = -$jQ_Even[$hilbertIdx];
                    $jQ_Even[$hilbertIdx] = $hilbertTempReal;
                    $jQ                   += $hilbertTempReal;
                    $jQ                   -= $prev_jQ_Even;
                    $prev_jQ_Even         = $b * $prev_jQ_input_Even;
                    $jQ                   += $prev_jQ_Even;
                    $prev_jQ_input_Even   = $Q1;
                    $jQ                   *= $adjustedPrevPeriod;
                };
                if (++$hilbertIdx == 3) {
                    $hilbertIdx = 0;
                }
                $Q2            = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2            = (0.2 * ($I1ForEvenPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForOddPrev3 = $I1ForOddPrev2;
                $I1ForOddPrev2 = $detrender;
            } else {
                {
                    $hilbertTempReal            = $a * $smoothedValue;
                    $detrender                  = -$detrender_Odd[$hilbertIdx];
                    $detrender_Odd[$hilbertIdx] = $hilbertTempReal;
                    $detrender                  += $hilbertTempReal;
                    $detrender                  -= $prev_detrender_Odd;
                    $prev_detrender_Odd         = $b * $prev_detrender_input_Odd;
                    $detrender                  += $prev_detrender_Odd;
                    $prev_detrender_input_Odd   = $smoothedValue;
                    $detrender                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $detrender;
                    $Q1                  = -$Q1_Odd[$hilbertIdx];
                    $Q1_Odd[$hilbertIdx] = $hilbertTempReal;
                    $Q1                  += $hilbertTempReal;
                    $Q1                  -= $prev_Q1_Odd;
                    $prev_Q1_Odd         = $b * $prev_Q1_input_Odd;
                    $Q1                  += $prev_Q1_Odd;
                    $prev_Q1_input_Odd   = $detrender;
                    $Q1                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $I1ForOddPrev3;
                    $jI                  = -$jI_Odd[$hilbertIdx];
                    $jI_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jI                  += $hilbertTempReal;
                    $jI                  -= $prev_jI_Odd;
                    $prev_jI_Odd         = $b * $prev_jI_input_Odd;
                    $jI                  += $prev_jI_Odd;
                    $prev_jI_input_Odd   = $I1ForOddPrev3;
                    $jI                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $Q1;
                    $jQ                  = -$jQ_Odd[$hilbertIdx];
                    $jQ_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jQ                  += $hilbertTempReal;
                    $jQ                  -= $prev_jQ_Odd;
                    $prev_jQ_Odd         = $b * $prev_jQ_input_Odd;
                    $jQ                  += $prev_jQ_Odd;
                    $prev_jQ_input_Odd   = $Q1;
                    $jQ                  *= $adjustedPrevPeriod;
                };
                $Q2             = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2             = (0.2 * ($I1ForOddPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForEvenPrev3 = $I1ForEvenPrev2;
                $I1ForEvenPrev2 = $detrender;
            }
            $Re       = (0.2 * (($I2 * $prevI2) + ($Q2 * $prevQ2))) + (0.8 * $Re);
            $Im       = (0.2 * (($I2 * $prevQ2) - ($Q2 * $prevI2))) + (0.8 * $Im);
            $prevQ2   = $Q2;
            $prevI2   = $I2;
            $tempReal = $period;
            if (($Im != 0.0) && ($Re != 0.0)) {
                $period = 360.0 / (atan($Im / $Re) * $rad2Deg);
            }
            $tempReal2 = 1.5 * $tempReal;
            if ($period > $tempReal2) {
                $period = $tempReal2;
            }
            $tempReal2 = 0.67 * $tempReal;
            if ($period < $tempReal2) {
                $period = $tempReal2;
            }
            if ($period < 6) {
                $period = 6;
            } elseif ($period > 50) {
                $period = 50;
            }
            $period           = (0.2 * $period) + (0.8 * $tempReal);
            $smoothPeriod     = (0.33 * $period) + (0.67 * $smoothPeriod);
            $DCPeriod         = $smoothPeriod + 0.5;
            $DCPeriodInt      = (int)$DCPeriod;
            $realPart         = 0.0;
            $imagPart         = 0.0;
            $idxothPricePrice = $smoothPrice_Idx;
            for ($i = 0; $i < $DCPeriodInt; $i++) {
                $tempReal  = ((double)$i * $constDeg2RadBy360) / (double)$DCPeriodInt;
                $tempReal2 = $smoothPrice[$idxothPricePrice];
                $realPart  += sin($tempReal) * $tempReal2;
                $imagPart  += cos($tempReal) * $tempReal2;
                if ($idxothPricePrice == 0) {
                    $idxothPricePrice = 50 - 1;
                } else {
                    $idxothPricePrice--;
                }
            }
            $tempReal = abs($imagPart);
            if ($tempReal > 0.0) {
                $DCPhase = atan($realPart / $imagPart) * $rad2Deg;
            } elseif ($tempReal <= 0.01) {
                if ($realPart < 0.0) {
                    $DCPhase -= 90.0;
                } elseif ($realPart > 0.0) {
                    $DCPhase += 90.0;
                }
            }
            $DCPhase += 90.0;
            $DCPhase += 360.0 / $smoothPeriod;
            if ($imagPart < 0.0) {
                $DCPhase += 180.0;
            }
            if ($DCPhase > 315.0) {
                $DCPhase -= 360.0;
            }
            if ($today >= $startIdx) {
                $outReal[$outIdx++] = $DCPhase;
            }
            {
                $smoothPrice_Idx++;
                if ($smoothPrice_Idx > $maxIdx_smoothPricePrice) {
                    $smoothPrice_Idx = 0;
                }
            };
            $today++;
        }
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outInPhase
     * @param array     $outQuadrature
     *
     * @return int
     */
    public function htPhasor(int $startIdx, int $endIdx, array $inReal, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outInPhase, array &$outQuadrature): int
    {
        $a              = 0.0962;
        $b              = 0.5769;
        $detrender_Odd  = $this->double(3);
        $detrender_Even = $this->double(3);
        $Q1_Odd         = $this->double(3);
        $Q1_Even        = $this->double(3);
        $jI_Odd         = $this->double(3);
        $jI_Even        = $this->double(3);
        $jQ_Odd         = $this->double(3);
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        $rad2Deg       = 180.0 / (4.0 * atan(1));
        $lookbackTotal = 32 + ($this->unstablePeriod[UnstablePeriodFunctionID::HtPhasor]);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outBegIdx->value = $startIdx;
        $trailingWMAIdx   = $startIdx - $lookbackTotal;
        $today            = $trailingWMAIdx;
        $tempReal         = $inReal[$today++];
        $periodWMASub     = $tempReal;
        $periodWMASum     = $tempReal;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 2.0;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 3.0;
        $trailingWMAValue = 0.0;
        $i                = 9;
        do {
            $tempReal = $inReal[$today++];
            {
                $periodWMASub     += $tempReal;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $tempReal * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
        } while (--$i != 0);
        $hilbertIdx = 0;
        {
            $detrender_Odd[0]          = 0.0;
            $detrender_Odd[1]          = 0.0;
            $detrender_Odd[2]          = 0.0;
            $detrender_Even[0]         = 0.0;
            $detrender_Even[1]         = 0.0;
            $detrender_Even[2]         = 0.0;
            $detrender                 = 0.0;
            $prev_detrender_Odd        = 0.0;
            $prev_detrender_Even       = 0.0;
            $prev_detrender_input_Odd  = 0.0;
            $prev_detrender_input_Even = 0.0;
        };
        {
            $Q1_Odd[0]          = 0.0;
            $Q1_Odd[1]          = 0.0;
            $Q1_Odd[2]          = 0.0;
            $Q1_Even[0]         = 0.0;
            $Q1_Even[1]         = 0.0;
            $Q1_Even[2]         = 0.0;
            $Q1                 = 0.0;
            $prev_Q1_Odd        = 0.0;
            $prev_Q1_Even       = 0.0;
            $prev_Q1_input_Odd  = 0.0;
            $prev_Q1_input_Even = 0.0;
        };
        {
            $jI_Odd[0]          = 0.0;
            $jI_Odd[1]          = 0.0;
            $jI_Odd[2]          = 0.0;
            $jI_Even[0]         = 0.0;
            $jI_Even[1]         = 0.0;
            $jI_Even[2]         = 0.0;
            $jI                 = 0.0;
            $prev_jI_Odd        = 0.0;
            $prev_jI_Even       = 0.0;
            $prev_jI_input_Odd  = 0.0;
            $prev_jI_input_Even = 0.0;
        };
        {
            $jQ_Odd[0]          = 0.0;
            $jQ_Odd[1]          = 0.0;
            $jQ_Odd[2]          = 0.0;
            $jQ_Even[0]         = 0.0;
            $jQ_Even[1]         = 0.0;
            $jQ_Even[2]         = 0.0;
            $jQ                 = 0.0;
            $prev_jQ_Odd        = 0.0;
            $prev_jQ_Even       = 0.0;
            $prev_jQ_input_Odd  = 0.0;
            $prev_jQ_input_Even = 0.0;
        };
        $period        = 0.0;
        $outIdx        = 0;
        $prevI2        = $prevQ2 = 0.0;
        $Re            = $Im = 0.0;
        $I1ForOddPrev3 = $I1ForEvenPrev3 = 0.0;
        $I1ForOddPrev2 = $I1ForEvenPrev2 = 0.0;
        while ($today <= $endIdx) {
            $adjustedPrevPeriod = (0.075 * $period) + 0.54;
            $todayValue         = $inReal[$today];
            {
                $periodWMASub     += $todayValue;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $todayValue * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
            if (($today % 2) == 0) {
                {
                    $hilbertTempReal             = $a * $smoothedValue;
                    $detrender                   = -$detrender_Even[$hilbertIdx];
                    $detrender_Even[$hilbertIdx] = $hilbertTempReal;
                    $detrender                   += $hilbertTempReal;
                    $detrender                   -= $prev_detrender_Even;
                    $prev_detrender_Even         = $b * $prev_detrender_input_Even;
                    $detrender                   += $prev_detrender_Even;
                    $prev_detrender_input_Even   = $smoothedValue;
                    $detrender                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $detrender;
                    $Q1                   = -$Q1_Even[$hilbertIdx];
                    $Q1_Even[$hilbertIdx] = $hilbertTempReal;
                    $Q1                   += $hilbertTempReal;
                    $Q1                   -= $prev_Q1_Even;
                    $prev_Q1_Even         = $b * $prev_Q1_input_Even;
                    $Q1                   += $prev_Q1_Even;
                    $prev_Q1_input_Even   = $detrender;
                    $Q1                   *= $adjustedPrevPeriod;
                };
                if ($today >= $startIdx) {
                    $outQuadrature[$outIdx] = $Q1;
                    $outInPhase[$outIdx++]  = $I1ForEvenPrev3;
                }
                {
                    $hilbertTempReal      = $a * $I1ForEvenPrev3;
                    $jI                   = -$jI_Even[$hilbertIdx];
                    $jI_Even[$hilbertIdx] = $hilbertTempReal;
                    $jI                   += $hilbertTempReal;
                    $jI                   -= $prev_jI_Even;
                    $prev_jI_Even         = $b * $prev_jI_input_Even;
                    $jI                   += $prev_jI_Even;
                    $prev_jI_input_Even   = $I1ForEvenPrev3;
                    $jI                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $Q1;
                    $jQ                   = -$jQ_Even[$hilbertIdx];
                    $jQ_Even[$hilbertIdx] = $hilbertTempReal;
                    $jQ                   += $hilbertTempReal;
                    $jQ                   -= $prev_jQ_Even;
                    $prev_jQ_Even         = $b * $prev_jQ_input_Even;
                    $jQ                   += $prev_jQ_Even;
                    $prev_jQ_input_Even   = $Q1;
                    $jQ                   *= $adjustedPrevPeriod;
                };
                if (++$hilbertIdx == 3) {
                    $hilbertIdx = 0;
                }
                $Q2            = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2            = (0.2 * ($I1ForEvenPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForOddPrev3 = $I1ForOddPrev2;
                $I1ForOddPrev2 = $detrender;
            } else {
                {
                    $hilbertTempReal            = $a * $smoothedValue;
                    $detrender                  = -$detrender_Odd[$hilbertIdx];
                    $detrender_Odd[$hilbertIdx] = $hilbertTempReal;
                    $detrender                  += $hilbertTempReal;
                    $detrender                  -= $prev_detrender_Odd;
                    $prev_detrender_Odd         = $b * $prev_detrender_input_Odd;
                    $detrender                  += $prev_detrender_Odd;
                    $prev_detrender_input_Odd   = $smoothedValue;
                    $detrender                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $detrender;
                    $Q1                  = -$Q1_Odd[$hilbertIdx];
                    $Q1_Odd[$hilbertIdx] = $hilbertTempReal;
                    $Q1                  += $hilbertTempReal;
                    $Q1                  -= $prev_Q1_Odd;
                    $prev_Q1_Odd         = $b * $prev_Q1_input_Odd;
                    $Q1                  += $prev_Q1_Odd;
                    $prev_Q1_input_Odd   = $detrender;
                    $Q1                  *= $adjustedPrevPeriod;
                };
                if ($today >= $startIdx) {
                    $outQuadrature[$outIdx] = $Q1;
                    $outInPhase[$outIdx++]  = $I1ForOddPrev3;
                }
                {
                    $hilbertTempReal     = $a * $I1ForOddPrev3;
                    $jI                  = -$jI_Odd[$hilbertIdx];
                    $jI_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jI                  += $hilbertTempReal;
                    $jI                  -= $prev_jI_Odd;
                    $prev_jI_Odd         = $b * $prev_jI_input_Odd;
                    $jI                  += $prev_jI_Odd;
                    $prev_jI_input_Odd   = $I1ForOddPrev3;
                    $jI                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $Q1;
                    $jQ                  = -$jQ_Odd[$hilbertIdx];
                    $jQ_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jQ                  += $hilbertTempReal;
                    $jQ                  -= $prev_jQ_Odd;
                    $prev_jQ_Odd         = $b * $prev_jQ_input_Odd;
                    $jQ                  += $prev_jQ_Odd;
                    $prev_jQ_input_Odd   = $Q1;
                    $jQ                  *= $adjustedPrevPeriod;
                };
                $Q2             = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2             = (0.2 * ($I1ForOddPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForEvenPrev3 = $I1ForEvenPrev2;
                $I1ForEvenPrev2 = $detrender;
            }
            $Re       = (0.2 * (($I2 * $prevI2) + ($Q2 * $prevQ2))) + (0.8 * $Re);
            $Im       = (0.2 * (($I2 * $prevQ2) - ($Q2 * $prevI2))) + (0.8 * $Im);
            $prevQ2   = $Q2;
            $prevI2   = $I2;
            $tempReal = $period;
            if (($Im != 0.0) && ($Re != 0.0)) {
                $period = 360.0 / (atan($Im / $Re) * $rad2Deg);
            }
            $tempReal2 = 1.5 * $tempReal;
            if ($period > $tempReal2) {
                $period = $tempReal2;
            }
            $tempReal2 = 0.67 * $tempReal;
            if ($period < $tempReal2) {
                $period = $tempReal2;
            }
            if ($period < 6) {
                $period = 6;
            } elseif ($period > 50) {
                $period = 50;
            }
            $period = (0.2 * $period) + (0.8 * $tempReal);
            $today++;
        }
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outSine
     * @param array     $outLeadSine
     *
     * @return int
     */
    public function htSine(int $startIdx, int $endIdx, array $inReal, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outSine, array &$outLeadSine): int
    {
        $a                       = 0.0962;
        $b                       = 0.5769;
        $detrender_Odd           = $this->double(3);
        $detrender_Even          = $this->double(3);
        $Q1_Odd                  = $this->double(3);
        $Q1_Even                 = $this->double(3);
        $jI_Odd                  = $this->double(3);
        $jI_Even                 = $this->double(3);
        $jQ_Odd                  = $this->double(3);
        $jQ_Even                 = $this->double(3);
        $smoothPrice_Idx         = 0;
        $maxIdx_smoothPricePrice = (50 - 1);
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        {
            $smoothPrice = $this->double($maxIdx_smoothPricePrice + 1);
        };
        $tempReal          = atan(1);
        $rad2Deg           = 45.0 / $tempReal;
        $deg2Rad           = 1.0 / $rad2Deg;
        $constDeg2RadBy360 = $tempReal * 8.0;
        $lookbackTotal     = 63 + ($this->unstablePeriod[UnstablePeriodFunctionID::HtSine]);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outBegIdx->value = $startIdx;
        $trailingWMAIdx   = $startIdx - $lookbackTotal;
        $today            = $trailingWMAIdx;
        $tempReal         = $inReal[$today++];
        $periodWMASub     = $tempReal;
        $periodWMASum     = $tempReal;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 2.0;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 3.0;
        $trailingWMAValue = 0.0;
        $i                = 34;
        do {
            $tempReal = $inReal[$today++];
            {
                $periodWMASub     += $tempReal;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $tempReal * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
        } while (--$i != 0);
        $hilbertIdx = 0;
        {
            $detrender_Odd[0]          = 0.0;
            $detrender_Odd[1]          = 0.0;
            $detrender_Odd[2]          = 0.0;
            $detrender_Even[0]         = 0.0;
            $detrender_Even[1]         = 0.0;
            $detrender_Even[2]         = 0.0;
            $detrender                 = 0.0;
            $prev_detrender_Odd        = 0.0;
            $prev_detrender_Even       = 0.0;
            $prev_detrender_input_Odd  = 0.0;
            $prev_detrender_input_Even = 0.0;
        };
        {
            $Q1_Odd[0]          = 0.0;
            $Q1_Odd[1]          = 0.0;
            $Q1_Odd[2]          = 0.0;
            $Q1_Even[0]         = 0.0;
            $Q1_Even[1]         = 0.0;
            $Q1_Even[2]         = 0.0;
            $Q1                 = 0.0;
            $prev_Q1_Odd        = 0.0;
            $prev_Q1_Even       = 0.0;
            $prev_Q1_input_Odd  = 0.0;
            $prev_Q1_input_Even = 0.0;
        };
        {
            $jI_Odd[0]          = 0.0;
            $jI_Odd[1]          = 0.0;
            $jI_Odd[2]          = 0.0;
            $jI_Even[0]         = 0.0;
            $jI_Even[1]         = 0.0;
            $jI_Even[2]         = 0.0;
            $jI                 = 0.0;
            $prev_jI_Odd        = 0.0;
            $prev_jI_Even       = 0.0;
            $prev_jI_input_Odd  = 0.0;
            $prev_jI_input_Even = 0.0;
        };
        {
            $jQ_Odd[0]          = 0.0;
            $jQ_Odd[1]          = 0.0;
            $jQ_Odd[2]          = 0.0;
            $jQ_Even[0]         = 0.0;
            $jQ_Even[1]         = 0.0;
            $jQ_Even[2]         = 0.0;
            $jQ                 = 0.0;
            $prev_jQ_Odd        = 0.0;
            $prev_jQ_Even       = 0.0;
            $prev_jQ_input_Odd  = 0.0;
            $prev_jQ_input_Even = 0.0;
        };
        $period        = 0.0;
        $outIdx        = 0;
        $prevI2        = $prevQ2 = 0.0;
        $Re            = $Im = 0.0;
        $I1ForOddPrev3 = $I1ForEvenPrev3 = 0.0;
        $I1ForOddPrev2 = $I1ForEvenPrev2 = 0.0;
        $smoothPeriod  = 0.0;
        for ($i = 0; $i < 50; $i++) {
            $smoothPrice[$i] = 0.0;
        }
        $DCPhase = 0.0;
        while ($today <= $endIdx) {
            $adjustedPrevPeriod = (0.075 * $period) + 0.54;
            $todayValue         = $inReal[$today];
            {
                $periodWMASub     += $todayValue;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $todayValue * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
            $smoothPrice[$smoothPrice_Idx] = $smoothedValue;
            if (($today % 2) == 0) {
                {
                    $hilbertTempReal             = $a * $smoothedValue;
                    $detrender                   = -$detrender_Even[$hilbertIdx];
                    $detrender_Even[$hilbertIdx] = $hilbertTempReal;
                    $detrender                   += $hilbertTempReal;
                    $detrender                   -= $prev_detrender_Even;
                    $prev_detrender_Even         = $b * $prev_detrender_input_Even;
                    $detrender                   += $prev_detrender_Even;
                    $prev_detrender_input_Even   = $smoothedValue;
                    $detrender                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $detrender;
                    $Q1                   = -$Q1_Even[$hilbertIdx];
                    $Q1_Even[$hilbertIdx] = $hilbertTempReal;
                    $Q1                   += $hilbertTempReal;
                    $Q1                   -= $prev_Q1_Even;
                    $prev_Q1_Even         = $b * $prev_Q1_input_Even;
                    $Q1                   += $prev_Q1_Even;
                    $prev_Q1_input_Even   = $detrender;
                    $Q1                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $I1ForEvenPrev3;
                    $jI                   = -$jI_Even[$hilbertIdx];
                    $jI_Even[$hilbertIdx] = $hilbertTempReal;
                    $jI                   += $hilbertTempReal;
                    $jI                   -= $prev_jI_Even;
                    $prev_jI_Even         = $b * $prev_jI_input_Even;
                    $jI                   += $prev_jI_Even;
                    $prev_jI_input_Even   = $I1ForEvenPrev3;
                    $jI                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $Q1;
                    $jQ                   = -$jQ_Even[$hilbertIdx];
                    $jQ_Even[$hilbertIdx] = $hilbertTempReal;
                    $jQ                   += $hilbertTempReal;
                    $jQ                   -= $prev_jQ_Even;
                    $prev_jQ_Even         = $b * $prev_jQ_input_Even;
                    $jQ                   += $prev_jQ_Even;
                    $prev_jQ_input_Even   = $Q1;
                    $jQ                   *= $adjustedPrevPeriod;
                };
                if (++$hilbertIdx == 3) {
                    $hilbertIdx = 0;
                }
                $Q2            = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2            = (0.2 * ($I1ForEvenPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForOddPrev3 = $I1ForOddPrev2;
                $I1ForOddPrev2 = $detrender;
            } else {
                {
                    $hilbertTempReal            = $a * $smoothedValue;
                    $detrender                  = -$detrender_Odd[$hilbertIdx];
                    $detrender_Odd[$hilbertIdx] = $hilbertTempReal;
                    $detrender                  += $hilbertTempReal;
                    $detrender                  -= $prev_detrender_Odd;
                    $prev_detrender_Odd         = $b * $prev_detrender_input_Odd;
                    $detrender                  += $prev_detrender_Odd;
                    $prev_detrender_input_Odd   = $smoothedValue;
                    $detrender                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $detrender;
                    $Q1                  = -$Q1_Odd[$hilbertIdx];
                    $Q1_Odd[$hilbertIdx] = $hilbertTempReal;
                    $Q1                  += $hilbertTempReal;
                    $Q1                  -= $prev_Q1_Odd;
                    $prev_Q1_Odd         = $b * $prev_Q1_input_Odd;
                    $Q1                  += $prev_Q1_Odd;
                    $prev_Q1_input_Odd   = $detrender;
                    $Q1                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $I1ForOddPrev3;
                    $jI                  = -$jI_Odd[$hilbertIdx];
                    $jI_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jI                  += $hilbertTempReal;
                    $jI                  -= $prev_jI_Odd;
                    $prev_jI_Odd         = $b * $prev_jI_input_Odd;
                    $jI                  += $prev_jI_Odd;
                    $prev_jI_input_Odd   = $I1ForOddPrev3;
                    $jI                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $Q1;
                    $jQ                  = -$jQ_Odd[$hilbertIdx];
                    $jQ_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jQ                  += $hilbertTempReal;
                    $jQ                  -= $prev_jQ_Odd;
                    $prev_jQ_Odd         = $b * $prev_jQ_input_Odd;
                    $jQ                  += $prev_jQ_Odd;
                    $prev_jQ_input_Odd   = $Q1;
                    $jQ                  *= $adjustedPrevPeriod;
                };
                $Q2             = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2             = (0.2 * ($I1ForOddPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForEvenPrev3 = $I1ForEvenPrev2;
                $I1ForEvenPrev2 = $detrender;
            }
            $Re       = (0.2 * (($I2 * $prevI2) + ($Q2 * $prevQ2))) + (0.8 * $Re);
            $Im       = (0.2 * (($I2 * $prevQ2) - ($Q2 * $prevI2))) + (0.8 * $Im);
            $prevQ2   = $Q2;
            $prevI2   = $I2;
            $tempReal = $period;
            if (($Im != 0.0) && ($Re != 0.0)) {
                $period = 360.0 / (atan($Im / $Re) * $rad2Deg);
            }
            $tempReal2 = 1.5 * $tempReal;
            if ($period > $tempReal2) {
                $period = $tempReal2;
            }
            $tempReal2 = 0.67 * $tempReal;
            if ($period < $tempReal2) {
                $period = $tempReal2;
            }
            if ($period < 6) {
                $period = 6;
            } elseif ($period > 50) {
                $period = 50;
            }
            $period           = (0.2 * $period) + (0.8 * $tempReal);
            $smoothPeriod     = (0.33 * $period) + (0.67 * $smoothPeriod);
            $DCPeriod         = $smoothPeriod + 0.5;
            $DCPeriodInt      = (int)$DCPeriod;
            $realPart         = 0.0;
            $imagPart         = 0.0;
            $idxothPricePrice = $smoothPrice_Idx;
            for ($i = 0; $i < $DCPeriodInt; $i++) {
                $tempReal  = ((double)$i * $constDeg2RadBy360) / (double)$DCPeriodInt;
                $tempReal2 = $smoothPrice[$idxothPricePrice];
                $realPart  += sin($tempReal) * $tempReal2;
                $imagPart  += cos($tempReal) * $tempReal2;
                if ($idxothPricePrice == 0) {
                    $idxothPricePrice = 50 - 1;
                } else {
                    $idxothPricePrice--;
                }
            }
            $tempReal = abs($imagPart);
            if ($tempReal > 0.0) {
                $DCPhase = atan($realPart / $imagPart) * $rad2Deg;
            } elseif ($tempReal <= 0.01) {
                if ($realPart < 0.0) {
                    $DCPhase -= 90.0;
                } elseif ($realPart > 0.0) {
                    $DCPhase += 90.0;
                }
            }
            $DCPhase += 90.0;
            $DCPhase += 360.0 / $smoothPeriod;
            if ($imagPart < 0.0) {
                $DCPhase += 180.0;
            }
            if ($DCPhase > 315.0) {
                $DCPhase -= 360.0;
            }
            if ($today >= $startIdx) {
                $outSine[$outIdx]       = sin($DCPhase * $deg2Rad);
                $outLeadSine[$outIdx++] = sin(($DCPhase + 45) * $deg2Rad);
            }
            {
                $smoothPrice_Idx++;
                if ($smoothPrice_Idx > $maxIdx_smoothPricePrice) {
                    $smoothPrice_Idx = 0;
                }
            };
            $today++;
        }
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

    /**
     * @param int       $startIdx
     * @param int       $endIdx
     * @param array     $inReal
     * @param MyInteger $outBegIdx
     * @param MyInteger $outNBElement
     * @param array     $outInteger
     *
     * @return int
     */
    public function htTrendMode(int $startIdx, int $endIdx, array $inReal, MyInteger &$outBegIdx, MyInteger &$outNBElement, array &$outInteger): int
    {
        $a                       = 0.0962;
        $b                       = 0.5769;
        $detrender_Odd           = $this->double(3);
        $detrender_Even          = $this->double(3);
        $Q1_Odd                  = $this->double(3);
        $Q1_Even                 = $this->double(3);
        $jI_Odd                  = $this->double(3);
        $jI_Even                 = $this->double(3);
        $jQ_Odd                  = $this->double(3);
        $jQ_Even                 = $this->double(3);
        $smoothPrice_Idx         = 0;
        $maxIdx_smoothPricePrice = (50 - 1);
        if ($startIdx < 0) {
            return ReturnCode::OutOfRangeStartIndex;
        }
        if (($endIdx < 0) || ($endIdx < $startIdx)) {
            return ReturnCode::OutOfRangeEndIndex;
        }
        {
            $smoothPrice = $this->double($maxIdx_smoothPricePrice + 1);
        };
        $iTrend1           = $iTrend2 = $iTrend3 = 0.0;
        $daysInTrend       = 0;
        $prevDCPhase       = $DCPhase = 0.0;
        $prevSine          = $sine = 0.0;
        $prevLeadSine      = $leadSine = 0.0;
        $tempReal          = atan(1);
        $rad2Deg           = 45.0 / $tempReal;
        $deg2Rad           = 1.0 / $rad2Deg;
        $constDeg2RadBy360 = $tempReal * 8.0;
        $lookbackTotal     = 63 + ($this->unstablePeriod[UnstablePeriodFunctionID::HtTrendMode]);
        if ($startIdx < $lookbackTotal) {
            $startIdx = $lookbackTotal;
        }
        if ($startIdx > $endIdx) {
            $outBegIdx->value    = 0;
            $outNBElement->value = 0;

            return ReturnCode::Success;
        }
        $outBegIdx->value = $startIdx;
        $trailingWMAIdx   = $startIdx - $lookbackTotal;
        $today            = $trailingWMAIdx;
        $tempReal         = $inReal[$today++];
        $periodWMASub     = $tempReal;
        $periodWMASum     = $tempReal;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 2.0;
        $tempReal         = $inReal[$today++];
        $periodWMASub     += $tempReal;
        $periodWMASum     += $tempReal * 3.0;
        $trailingWMAValue = 0.0;
        $i                = 34;
        do {
            $tempReal = $inReal[$today++];
            {
                $periodWMASub     += $tempReal;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $tempReal * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
        } while (--$i != 0);
        $hilbertIdx = 0;
        {
            $detrender_Odd[0]          = 0.0;
            $detrender_Odd[1]          = 0.0;
            $detrender_Odd[2]          = 0.0;
            $detrender_Even[0]         = 0.0;
            $detrender_Even[1]         = 0.0;
            $detrender_Even[2]         = 0.0;
            $detrender                 = 0.0;
            $prev_detrender_Odd        = 0.0;
            $prev_detrender_Even       = 0.0;
            $prev_detrender_input_Odd  = 0.0;
            $prev_detrender_input_Even = 0.0;
        };
        {
            $Q1_Odd[0]          = 0.0;
            $Q1_Odd[1]          = 0.0;
            $Q1_Odd[2]          = 0.0;
            $Q1_Even[0]         = 0.0;
            $Q1_Even[1]         = 0.0;
            $Q1_Even[2]         = 0.0;
            $Q1                 = 0.0;
            $prev_Q1_Odd        = 0.0;
            $prev_Q1_Even       = 0.0;
            $prev_Q1_input_Odd  = 0.0;
            $prev_Q1_input_Even = 0.0;
        };
        {
            $jI_Odd[0]          = 0.0;
            $jI_Odd[1]          = 0.0;
            $jI_Odd[2]          = 0.0;
            $jI_Even[0]         = 0.0;
            $jI_Even[1]         = 0.0;
            $jI_Even[2]         = 0.0;
            $jI                 = 0.0;
            $prev_jI_Odd        = 0.0;
            $prev_jI_Even       = 0.0;
            $prev_jI_input_Odd  = 0.0;
            $prev_jI_input_Even = 0.0;
        };
        {
            $jQ_Odd[0]          = 0.0;
            $jQ_Odd[1]          = 0.0;
            $jQ_Odd[2]          = 0.0;
            $jQ_Even[0]         = 0.0;
            $jQ_Even[1]         = 0.0;
            $jQ_Even[2]         = 0.0;
            $jQ                 = 0.0;
            $prev_jQ_Odd        = 0.0;
            $prev_jQ_Even       = 0.0;
            $prev_jQ_input_Odd  = 0.0;
            $prev_jQ_input_Even = 0.0;
        };
        $period        = 0.0;
        $outIdx        = 0;
        $prevI2        = $prevQ2 = 0.0;
        $Re            = $Im = 0.0;
        $I1ForOddPrev3 = $I1ForEvenPrev3 = 0.0;
        $I1ForOddPrev2 = $I1ForEvenPrev2 = 0.0;
        $smoothPeriod  = 0.0;
        for ($i = 0; $i < 50; $i++) {
            $smoothPrice[$i] = 0.0;
        }
        $DCPhase = 0.0;
        while ($today <= $endIdx) {
            $adjustedPrevPeriod = (0.075 * $period) + 0.54;
            $todayValue         = $inReal[$today];
            {
                $periodWMASub     += $todayValue;
                $periodWMASub     -= $trailingWMAValue;
                $periodWMASum     += $todayValue * 4.0;
                $trailingWMAValue = $inReal[$trailingWMAIdx++];
                $smoothedValue    = $periodWMASum * 0.1;
                $periodWMASum     -= $periodWMASub;
            };
            $smoothPrice[$smoothPrice_Idx] = $smoothedValue;
            if (($today % 2) == 0) {
                {
                    $hilbertTempReal             = $a * $smoothedValue;
                    $detrender                   = -$detrender_Even[$hilbertIdx];
                    $detrender_Even[$hilbertIdx] = $hilbertTempReal;
                    $detrender                   += $hilbertTempReal;
                    $detrender                   -= $prev_detrender_Even;
                    $prev_detrender_Even         = $b * $prev_detrender_input_Even;
                    $detrender                   += $prev_detrender_Even;
                    $prev_detrender_input_Even   = $smoothedValue;
                    $detrender                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $detrender;
                    $Q1                   = -$Q1_Even[$hilbertIdx];
                    $Q1_Even[$hilbertIdx] = $hilbertTempReal;
                    $Q1                   += $hilbertTempReal;
                    $Q1                   -= $prev_Q1_Even;
                    $prev_Q1_Even         = $b * $prev_Q1_input_Even;
                    $Q1                   += $prev_Q1_Even;
                    $prev_Q1_input_Even   = $detrender;
                    $Q1                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $I1ForEvenPrev3;
                    $jI                   = -$jI_Even[$hilbertIdx];
                    $jI_Even[$hilbertIdx] = $hilbertTempReal;
                    $jI                   += $hilbertTempReal;
                    $jI                   -= $prev_jI_Even;
                    $prev_jI_Even         = $b * $prev_jI_input_Even;
                    $jI                   += $prev_jI_Even;
                    $prev_jI_input_Even   = $I1ForEvenPrev3;
                    $jI                   *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal      = $a * $Q1;
                    $jQ                   = -$jQ_Even[$hilbertIdx];
                    $jQ_Even[$hilbertIdx] = $hilbertTempReal;
                    $jQ                   += $hilbertTempReal;
                    $jQ                   -= $prev_jQ_Even;
                    $prev_jQ_Even         = $b * $prev_jQ_input_Even;
                    $jQ                   += $prev_jQ_Even;
                    $prev_jQ_input_Even   = $Q1;
                    $jQ                   *= $adjustedPrevPeriod;
                };
                if (++$hilbertIdx == 3) {
                    $hilbertIdx = 0;
                }
                $Q2            = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2            = (0.2 * ($I1ForEvenPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForOddPrev3 = $I1ForOddPrev2;
                $I1ForOddPrev2 = $detrender;
            } else {
                {
                    $hilbertTempReal            = $a * $smoothedValue;
                    $detrender                  = -$detrender_Odd[$hilbertIdx];
                    $detrender_Odd[$hilbertIdx] = $hilbertTempReal;
                    $detrender                  += $hilbertTempReal;
                    $detrender                  -= $prev_detrender_Odd;
                    $prev_detrender_Odd         = $b * $prev_detrender_input_Odd;
                    $detrender                  += $prev_detrender_Odd;
                    $prev_detrender_input_Odd   = $smoothedValue;
                    $detrender                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $detrender;
                    $Q1                  = -$Q1_Odd[$hilbertIdx];
                    $Q1_Odd[$hilbertIdx] = $hilbertTempReal;
                    $Q1                  += $hilbertTempReal;
                    $Q1                  -= $prev_Q1_Odd;
                    $prev_Q1_Odd         = $b * $prev_Q1_input_Odd;
                    $Q1                  += $prev_Q1_Odd;
                    $prev_Q1_input_Odd   = $detrender;
                    $Q1                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $I1ForOddPrev3;
                    $jI                  = -$jI_Odd[$hilbertIdx];
                    $jI_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jI                  += $hilbertTempReal;
                    $jI                  -= $prev_jI_Odd;
                    $prev_jI_Odd         = $b * $prev_jI_input_Odd;
                    $jI                  += $prev_jI_Odd;
                    $prev_jI_input_Odd   = $I1ForOddPrev3;
                    $jI                  *= $adjustedPrevPeriod;
                };
                {
                    $hilbertTempReal     = $a * $Q1;
                    $jQ                  = -$jQ_Odd[$hilbertIdx];
                    $jQ_Odd[$hilbertIdx] = $hilbertTempReal;
                    $jQ                  += $hilbertTempReal;
                    $jQ                  -= $prev_jQ_Odd;
                    $prev_jQ_Odd         = $b * $prev_jQ_input_Odd;
                    $jQ                  += $prev_jQ_Odd;
                    $prev_jQ_input_Odd   = $Q1;
                    $jQ                  *= $adjustedPrevPeriod;
                };
                $Q2             = (0.2 * ($Q1 + $jI)) + (0.8 * $prevQ2);
                $I2             = (0.2 * ($I1ForOddPrev3 - $jQ)) + (0.8 * $prevI2);
                $I1ForEvenPrev3 = $I1ForEvenPrev2;
                $I1ForEvenPrev2 = $detrender;
            }
            $Re       = (0.2 * (($I2 * $prevI2) + ($Q2 * $prevQ2))) + (0.8 * $Re);
            $Im       = (0.2 * (($I2 * $prevQ2) - ($Q2 * $prevI2))) + (0.8 * $Im);
            $prevQ2   = $Q2;
            $prevI2   = $I2;
            $tempReal = $period;
            if (($Im != 0.0) && ($Re != 0.0)) {
                $period = 360.0 / (atan($Im / $Re) * $rad2Deg);
            }
            $tempReal2 = 1.5 * $tempReal;
            if ($period > $tempReal2) {
                $period = $tempReal2;
            }
            $tempReal2 = 0.67 * $tempReal;
            if ($period < $tempReal2) {
                $period = $tempReal2;
            }
            if ($period < 6) {
                $period = 6;
            } elseif ($period > 50) {
                $period = 50;
            }
            $period           = (0.2 * $period) + (0.8 * $tempReal);
            $smoothPeriod     = (0.33 * $period) + (0.67 * $smoothPeriod);
            $prevDCPhase      = $DCPhase;
            $DCPeriod         = $smoothPeriod + 0.5;
            $DCPeriodInt      = (int)$DCPeriod;
            $realPart         = 0.0;
            $imagPart         = 0.0;
            $idxothPricePrice = $smoothPrice_Idx;
            for ($i = 0; $i < $DCPeriodInt; $i++) {
                $tempReal  = ((double)$i * $constDeg2RadBy360) / (double)$DCPeriodInt;
                $tempReal2 = $smoothPrice[$idxothPricePrice];
                $realPart  += sin($tempReal) * $tempReal2;
                $imagPart  += cos($tempReal) * $tempReal2;
                if ($idxothPricePrice == 0) {
                    $idxothPricePrice = 50 - 1;
                } else {
                    $idxothPricePrice--;
                }
            }
            $tempReal = abs($imagPart);
            if ($tempReal > 0.0) {
                $DCPhase = atan($realPart / $imagPart) * $rad2Deg;
            } elseif ($tempReal <= 0.01) {
                if ($realPart < 0.0) {
                    $DCPhase -= 90.0;
                } elseif ($realPart > 0.0) {
                    $DCPhase += 90.0;
                }
            }
            $DCPhase += 90.0;
            $DCPhase += 360.0 / $smoothPeriod;
            if ($imagPart < 0.0) {
                $DCPhase += 180.0;
            }
            if ($DCPhase > 315.0) {
                $DCPhase -= 360.0;
            }
            $prevSine         = $sine;
            $prevLeadSine     = $leadSine;
            $sine             = sin($DCPhase * $deg2Rad);
            $leadSine         = sin(($DCPhase + 45) * $deg2Rad);
            $DCPeriod         = $smoothPeriod + 0.5;
            $DCPeriodInt      = (int)$DCPeriod;
            $idxothPricePrice = $today;
            $tempReal         = 0.0;
            for ($i = 0; $i < $DCPeriodInt; $i++) {
                $tempReal += $inReal[$idxothPricePrice--];
            }
            if ($DCPeriodInt > 0) {
                $tempReal = $tempReal / (double)$DCPeriodInt;
            }
            $trendline = (4.0 * $tempReal + 3.0 * $iTrend1 + 2.0 * $iTrend2 + $iTrend3) / 10.0;
            $iTrend3   = $iTrend2;
            $iTrend2   = $iTrend1;
            $iTrend1   = $tempReal;
            $trend     = 1;
            if ((($sine > $leadSine) && ($prevSine <= $prevLeadSine)) ||
                (($sine < $leadSine) && ($prevSine >= $prevLeadSine))) {
                $daysInTrend = 0;
                $trend       = 0;
            }
            $daysInTrend++;
            if ($daysInTrend < (0.5 * $smoothPeriod)) {
                $trend = 0;
            }
            $tempReal = $DCPhase - $prevDCPhase;
            if (($smoothPeriod != 0.0) &&
                (($tempReal > (0.67 * 360.0 / $smoothPeriod)) && ($tempReal < (1.5 * 360.0 / $smoothPeriod)))) {
                $trend = 0;
            }
            $tempReal = $smoothPrice[$smoothPrice_Idx];
            if (($trendline != 0.0) && (abs(($tempReal - $trendline) / $trendline) >= 0.015)) {
                $trend = 1;
            }
            if ($today >= $startIdx) {
                $outInteger[$outIdx++] = $trend;
            }
            {
                $smoothPrice_Idx++;
                if ($smoothPrice_Idx > $maxIdx_smoothPricePrice) {
                    $smoothPrice_Idx = 0;
                }
            };
            $today++;
        }
        $outNBElement->value = $outIdx;

        return ReturnCode::Success;
    }

}
