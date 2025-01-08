<?php

namespace App\Traits;

trait EnglishToArabic
{
    function convertToArabicNumbers(?string $number = "")
    {
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', 'minutes', 'hours', 'days', 'months', 'years', 'minute', 'hour', 'day', 'month', 'year'];
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '،', 'دقائق', 'ساعات', 'ايام', 'اشهر', 'سنوات', 'دقيقة', 'ساعة', 'يوم', 'شهر', 'سنة'];
        return str_replace($englishNumbers, $arabicNumbers, $number);
    }
}
