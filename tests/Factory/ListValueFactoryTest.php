<?php
namespace Povs\ListerBundle\Factory;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\Service\ListValue;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListValueFactoryTest extends TestCase
{
    public function testCreateListValue(): void
    {
        $listMapperMock = $this->createMock(ListMapper::class);
        $filterMapperMock = $this->createMock(FilterMapper::class);
        $listValueFactory = new ListValueFactory();
        $listValue = $listValueFactory->createListValue($listMapperMock, $filterMapperMock);
        $this->assertInstanceOf(ListValue::class, $listValue);
    }
}