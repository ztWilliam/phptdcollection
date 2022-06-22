<?php
namespace WztzTech\Iot\PhpTd\Test\Collection;

use PHPUnit\Framework\TestCase;

use WztzTech\Iot\PhpTd\Collection\BaseCollectionStore;
use WztzTech\Iot\PhpTd\Collection\Demo\StoreDemo;
use WztzTech\Iot\PhpTd\Enum\TdUpdateMode;

class BaseCollectionStoreTest extends TestCase {

    public function testRegister() {
        $baseStore = BaseCollectionStore::createStore('base', 'Base for test', 360, TdUpdateMode::UPDATE_PART, []);

        $demoStore = StoreDemo::createStore('demo', 'Demo for test', 720, TdUpdateMode::DISABLE, []);

        $this->assertEquals('base', $baseStore->getName());
        $this->assertEquals('demo', $demoStore->getName());

    }

}