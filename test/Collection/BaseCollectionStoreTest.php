<?php
namespace WztzTech\Iot\PhpTd\Test\Collection;

use PHPUnit\Framework\TestCase;

use WztzTech\Iot\PhpTd\Collection\BaseCollectionStore;
use WztzTech\Iot\PhpTd\Collection\Demo\StoreDemo;
use WztzTech\Iot\PhpTd\Enum\TdUpdateMode;

class BaseCollectionStoreTest extends TestCase {

    public function testRegister() {
        $baseStore = BaseCollectionStore::register('base', 360, TdUpdateMode::UPDATE_PART, [], 'Base for test');

        $demoStore = StoreDemo::register('demo', 720, TdUpdateMode::DISABLE, [], 'Demo for test');

        $this->assertEquals('baseWztzTech\Iot\PhpTd\Collection\BaseCollectionStore', $baseStore->getName());
        $this->assertEquals('demoWztzTech\Iot\PhpTd\Collection\Demo\StoreDemo', $demoStore->getName());

    }

}