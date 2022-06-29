<?php
namespace WztzTech\Iot\PhpTd\Collection\Meta\Analyzer;

class StoreCounterData {
    public String $storeName;
    public String $countingTime;
    public int $pointCount;
    public int $collectorCount;
    public int $dataCount;
    public int $dataSize;

    public function __construct($storeName, $countingTime, $pointCount, $collectorCount, $dataCount, $dataSize)  
    {
        $this->storeName = $storeName;
        $this->countingTime = $countingTime;
        $this->pointCount = $pointCount;
        $this->collectorCount = $collectorCount;
        $this->dataCount = $dataCount;
        $this->dataSize = $dataSize;
    }

}