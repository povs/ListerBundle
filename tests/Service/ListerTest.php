<?php

namespace Povs\ListerBundle\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListerTest extends TestCase
{
    public function testBuildList(): void
    {
        $mock = $this->createMock(ListManager::class);
        $mock->expects($this->once())
            ->method('buildList')
            ->with('listClass', 'listType', ['param1' => 'val1', 'param2' => 'val2'])
            ->willReturnSelf();

        $lister = new Lister($mock);
        $lister->buildList('listClass', 'listType', ['param1' => 'val1', 'param2' => 'val2']);
    }

    public function testGenerateResponse(): void
    {
        $response = new Response();
        $mock = $this->createMock(ListManager::class);
        $mock->expects($this->once())
            ->method('getResponse')
            ->with(['param1' => 'val1', 'param2' => 'val2'])
            ->willReturn($response);

        $lister = new Lister($mock);
        $res = $lister->generateResponse(['param1' => 'val1', 'param2' => 'val2']);
        $this->assertEquals($response, $res);
    }

    public function testGenerateData(): void
    {
        $data = 'data';
        $mock = $this->createMock(ListManager::class);
        $mock->expects($this->once())
            ->method('getData')
            ->with(['param1' => 'val1', 'param2' => 'val2'])
            ->willReturn($data);

        $lister = new Lister($mock);
        $res = $lister->generateData(['param1' => 'val1', 'param2' => 'val2']);
        $this->assertEquals($data, $res);
    }
}
