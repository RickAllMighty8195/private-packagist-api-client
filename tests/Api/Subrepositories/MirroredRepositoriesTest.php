<?php

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient\Api\Subrepositories;

use PHPUnit\Framework\MockObject\MockObject;
use PrivatePackagist\ApiClient\Api\ApiTestCase;

class MirroredRepositoriesTest extends ApiTestCase
{
    public function testAll()
    {
        $subrepositoryName = 'subrepository';
        $expected = [
            $this->getProjectMirroredRepositoryDefinition(),
        ];

        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/'))
            ->willReturn($expected);

        $this->assertSame($expected, $api->all($subrepositoryName));
    }

    public function testShow()
    {
        $subrepositoryName = 'subrepository';
        $expected = $this->getProjectMirroredRepositoryDefinition();

        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/1/'))
            ->willReturn($expected);

        $this->assertSame($expected, $api->show($subrepositoryName, 1));
    }

    public function testAdd()
    {
        $subrepositoryName = 'subrepository';
        $expected = $this->getProjectMirroredRepositoryDefinition();
        $data = [
            'id' => $expected['mirroredRepository']['id'],
            'mirroringBehavior' => $expected['mirroringBehavior'],
        ];

        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/'), $this->equalTo([$data]))
            ->willReturn([$expected]);

        $this->assertSame([$expected], $api->add($subrepositoryName, [$data]));
    }

    public function testEdit()
    {
        $subrepositoryName = 'subrepository';
        $expected = $this->getProjectMirroredRepositoryDefinition();
        $mirroredRepositoryId = $expected['mirroredRepository']['id'];
        $data = [
            'mirroringBehavior' => $mirroringBehaviour = $expected['mirroringBehavior'],
        ];

        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/1/'), $this->equalTo($data))
            ->willReturn($expected);

        $this->assertSame($expected, $api->edit($subrepositoryName, $mirroredRepositoryId, $mirroringBehaviour));
    }
    public function testRemove()
    {
        $subrepositoryName = 'subrepository';
        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/1/'))
            ->willReturn([]);

        $this->assertSame([], $api->remove($subrepositoryName, 1));
    }

    public function testListPackages()
    {
        $subrepositoryName = 'subrepository';
        $expected = [[
            'name' => 'acme/cool-lib',
            'origin' => 'public-mirror',
            'credentials' => null,
        ]];
        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/1/packages/'))
            ->willReturn($expected);

        $this->assertSame($expected, $api->listPackages($subrepositoryName, 1));
    }

    public function testAddPackages()
    {
        $subrepositoryName = 'subrepository';
        $expected = [[
            'id' => 'job-id',
            'status' => 'queued',
        ]];

        $packages = [
            'acme/cool-lib',
        ];

        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/1/packages/'), $this->equalTo($packages))
            ->willReturn($expected);

        $this->assertSame($expected, $api->addPackages($subrepositoryName, 1, $packages));
    }

    public function testRemovePackages()
    {
        $subrepositoryName = 'subrepository';
        /** @var MirroredRepositories&MockObject $api */
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('/subrepositories/subrepository/mirrored-repositories/1/packages/'))
            ->willReturn([]);

        $this->assertSame([], $api->removePackages($subrepositoryName, 1));
    }

    protected function getApiClass()
    {
        return MirroredRepositories::class;
    }

    private function getProjectMirroredRepositoryDefinition()
    {
        return [
            'mirroringBehavior' => 'add_on_use',
            'mirroredRepository' => [
                'id' => 1,
                'name' => 'Packagist.org',
                'url' => 'https://packagist.org',
                'mirroringBehavior' => 'add_on_use',
                'credentials' => null,
            ]
        ];
    }
}
