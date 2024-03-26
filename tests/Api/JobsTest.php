<?php

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient\Api;

use PHPUnit\Framework\MockObject\MockObject;

class JobsTest extends ApiTestCase
{
    public function testShow()
    {
        $expected = [];
        $jobId = '46bf13150a86fece079ca979cb8ef57c78773faa';

        /** @var Jobs&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/jobs/' . $jobId))
            ->willReturn($expected);

        $this->assertSame($expected, $api->show($jobId));
    }

    protected function getApiClass()
    {
        return Jobs::class;
    }
}
